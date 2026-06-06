<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','cancelled','ready','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','paid','ready','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'");
    }
};
