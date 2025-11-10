<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $t) {
            $t->boolean('child_discount_active')->default(false);
            $t->decimal('child_discount_percent', 5, 2)->nullable(); // 0–100
        });
        Schema::table('rooms', function (Blueprint $t) {
            $t->boolean('child_discount_active')->default(false);
            $t->decimal('child_discount_percent', 5, 2)->nullable(); // 0–100
        });
    }
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $t) {
            $t->dropColumn(['child_discount_active','child_discount_percent']);
        });
        Schema::table('rooms', function (Blueprint $t) {
            $t->dropColumn(['child_discount_active','child_discount_percent']);
        });
    }
};

