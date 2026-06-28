@extends('layouts.pos')

@section('title', 'Kelola Inventori')

@section('content')
<div class="pos-container" style="flex-direction:column; background-color:#f9fafb; height:100vh;">
    <div style="background:#fff; padding:16px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0; font-size:1.25rem; color:#111827;">Kelola Inventori</h2>
            <p style="margin:4px 0 0; color:#6b7280; font-size:0.875rem;">Stok produk</p>
        </div>
        <form method="GET" action="{{ route('pos.inventori') }}" style="display:flex; gap:12px; align-items:center;">
            <div style="position:relative; width:300px;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk..." style="width:100%; padding:10px 16px 10px 40px; border:1px solid #d1d5db; border-radius:8px; font-size:0.9rem; outline:none; transition:all 0.2s;">
                <svg style="width:20px; height:20px; position:absolute; left:12px; top:10px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" style="background:#C43626; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">Cari</button>
        </form>
    </div>

    <div style="flex:1; padding:24px; overflow-y:auto;">
        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.1); overflow:hidden;">
            <div style="background:#f3f4f6; padding:12px 24px; font-weight:700; color:#374151; font-size:0.9rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                INVENTORI
            </div>
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb; font-size:0.85rem; color:#111827;">
                        <th style="padding:16px 24px; font-weight:700;">NAMA PRODUK</th>
                        <th style="padding:16px 24px; font-weight:700;">JUMLAH STOK</th>
                        <th style="padding:16px 24px; font-weight:700;">PERINGATAN STOK</th>
                        <th style="padding:16px 24px; font-weight:700; text-align:right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr style="border-bottom:1px solid #f3f4f6; transition:background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                        <td style="padding:16px 24px;">
                            <div style="display:flex; align-items:center; gap:16px;">
                                <div style="width:48px; height:48px; background:#e5e7eb; border-radius:8px; overflow:hidden;">
                                    @if($product->img_url)
                                    <img src="{{ asset($product->img_url) }}" style="width:100%; height:100%; object-fit:cover;">
                                    @endif
                                </div>
                                <span style="font-weight:600; color:#111827;">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td style="padding:16px 24px; font-weight:600; font-size:1.1rem; color:#111827;" id="stock-val-{{ $product->product_id }}">
                            {{ $product->stock }}
                        </td>
                        <td style="padding:16px 24px;" id="warning-{{ $product->product_id }}">
                            @if($product->stock == 0)
                                <span style="color:#ef4444; font-weight:600;">Stok Habis</span>
                            @elseif($product->stock <= 10)
                                <span style="color:#f59e0b; font-weight:600;">Stok Sedikit</span>
                            @else
                                <span style="color:#10b981; font-weight:600;">Aman</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px; text-align:right;">
                            <button onclick="editStock({{ $product->product_id }}, '{{ addslashes($product->name) }}', {{ $product->stock }})" style="background:#fff; border:1px solid #d1d5db; padding:8px 16px; border-radius:6px; font-weight:600; color:#374151; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#9ca3af'" onmouseout="this.style.borderColor='#d1d5db'">
                                Sesuaikan
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @if($products->isEmpty())
                    <tr>
                        <td colspan="4" style="text-align:center; padding:40px; color:#6b7280;">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function editStock(id, name, currentStock) {
        Swal.fire({
            title: 'Sesuaikan Stok',
            text: name,
            input: 'number',
            inputValue: currentStock,
            inputAttributes: {
                min: 0,
                step: 1
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#C43626',
            showLoaderOnConfirm: true,
            preConfirm: (newStock) => {
                if (newStock === '' || newStock < 0) {
                    Swal.showValidationMessage('Masukkan jumlah stok yang valid (minimal 0)');
                    return false;
                }
                return fetch('{{ route("pos.updateInventori") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ product_id: id, stock: parseInt(newStock) })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal menyimpan: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Stok berhasil diperbarui!',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Update DOM directly
                let stockVal = result.value.new_stock;
                document.getElementById('stock-val-' + id).innerText = stockVal;
                
                let warningEl = document.getElementById('warning-' + id);
                if (stockVal == 0) {
                    warningEl.innerHTML = '<span style="color:#ef4444; font-weight:600;">Stok Habis</span>';
                } else if (stockVal <= 10) {
                    warningEl.innerHTML = '<span style="color:#f59e0b; font-weight:600;">Stok Sedikit</span>';
                } else {
                    warningEl.innerHTML = '<span style="color:#10b981; font-weight:600;">Aman</span>';
                }
            } else if (result.value && !result.value.success) {
                Swal.fire('Gagal', result.value.message, 'error');
            }
        });
    }
</script>
@endsection
