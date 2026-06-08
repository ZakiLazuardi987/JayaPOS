<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        
        // Midtrans mengirimkan payload dalam bentuk JSON
        $payload = $request->getContent();
        $notification = json_decode($payload);

        if (!$notification) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Verifikasi signature key
        $orderId = $notification->order_id;
        $statusCode = $notification->status_code;
        $grossAmount = $notification->gross_amount;
        $serverKey = config('services.midtrans.server_key');
        
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($signatureKey !== $notification->signature_key) {
            Log::warning('Midtrans Invalid Signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $realOrderId = $notification->custom_field1 ?? null; // Kita simpan order_id asli di sini

        if (!$realOrderId) {
            return response()->json(['message' => 'custom_field1 missing'], 400);
        }

        try {
            DB::transaction(function () use ($realOrderId, $transactionStatus, $notification) {
                $payment = Payment::where('order_id', $realOrderId)
                                  ->where('payment_gateway', 'midtrans')
                                  ->first();

                if (!$payment) return;

                $order = Order::find($realOrderId);

                // Update Response
                $payment->payment_response = array_merge((array)$payment->payment_response, (array)$notification);

                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if ($payment->status !== 'success') {
                        $payment->status = 'success';
                        $payment->paid_at = now();
                        
                        if ($order) {
                            $order->status = 'paid';
                            $order->save();
                            
                            // Set paid_at via query builder agar trigger poin berjalan
                            DB::table('orders')->where('order_id', $order->order_id)->update(['paid_at' => now()]);
                        }
                    }
                } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                    if ($payment->status !== 'failed' && $payment->status !== 'expired') {
                        $payment->status = $transactionStatus == 'expire' ? 'expired' : 'failed';
                        $payment->expired_at = now();
                        
                        if ($order) {
                            $order->status = 'cancelled';
                            $order->save();
                        }
                    }
                } else if ($transactionStatus == 'pending') {
                    $payment->status = 'pending';
                }

                $payment->save();

                // Bebaskan meja jika lunas atau batal
                if (in_array($payment->status, ['success', 'failed', 'expired']) && $order && $order->table_id) {
                    DB::table('tables')->where('table_id', $order->table_id)->update(['status' => 'available']);
                }
            });

            return response()->json(['message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}
