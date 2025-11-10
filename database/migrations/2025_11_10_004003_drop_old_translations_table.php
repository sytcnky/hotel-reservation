<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('translations');
    }

    public function down(): void
    {
        // İstersen boş bırak, geri alma ihtiyacın yok
        // veya eski şemayı buraya koyabilirsin.
    }
};

