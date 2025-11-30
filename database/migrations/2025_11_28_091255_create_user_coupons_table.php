<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_coupons', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coupon_id');

            $table->dateTime('assigned_at');
            $table->dateTime('expires_at')->nullable();

            $table->integer('used_count')->default(0);
            $table->dateTime('last_used_at')->nullable();

            $table->enum('source', ['manual', 'bulk', 'campaign', 'system'])->default('manual');

            $table->timestamps();

            // FK'ler
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_coupons');
    }
};
