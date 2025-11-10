<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('test_models');
    }

    public function down(): void
    {
        // Bilinçli boş. Geri alma gerekmiyor.
    }
};
