<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('table_id')->nullable()->after('outlet_id');
            $table->integer('pax')->nullable()->after('table_id');
            $table->unsignedBigInteger('waiter_id')->nullable()->after('pax');

            $table->foreign('table_id')->references('table_id')->on('tables')->onDelete('set null');
            $table->foreign('waiter_id')->references('staff_id')->on('staff')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['waiter_id']);
            $table->dropColumn(['table_id', 'pax', 'waiter_id']);
        });
    }
};
