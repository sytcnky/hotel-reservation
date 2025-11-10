<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transfer_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('transfer_vehicles', 'name')) {
                $table->jsonb('name')->after('id');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'description')) {
                $table->jsonb('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('transfer_vehicles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
                $table->index('is_active');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
                $table->index('sort_order');
            }

            if (! Schema::hasColumn('transfer_vehicles', 'capacity_total')) {
                $table->unsignedSmallInteger('capacity_total')->after('sort_order');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'capacity_adult_max')) {
                $table->unsignedSmallInteger('capacity_adult_max')->nullable()->after('capacity_total');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'capacity_child_max')) {
                $table->unsignedSmallInteger('capacity_child_max')->nullable()->after('capacity_adult_max');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'capacity_infant_max')) {
                $table->unsignedSmallInteger('capacity_infant_max')->nullable()->after('capacity_child_max');
            }

            if (! Schema::hasColumn('transfer_vehicles', 'infants_count_towards_total')) {
                $table->boolean('infants_count_towards_total')->default(true)->after('capacity_infant_max');
            }

            if (! Schema::hasColumn('transfer_vehicles', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('transfer_vehicles', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // Geri alma tanımlamıyoruz; üretimde kolon düşürmek istemeyiz.
    }
};
