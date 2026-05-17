<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_modifier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('modifier_id');
            $table->timestamps();

            $table->unique(['product_id', 'modifier_id']);
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('modifier_id')->references('modifier_id')->on('modifier')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_modifier');
    }
};
