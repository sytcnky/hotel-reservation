<?php

namespace Database\Seeders;

use App\Enums\PersonType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PersonType::cases() as $case) {
            [$min, $max] = $case->ageRange();

            DB::table('person_types')->updateOrInsert(
                ['code' => $case->value],
                [
                    'label' => $case->label(),
                    'min_age' => $min,
                    'max_age' => $max,
                ]
            );
        }
    }
}
