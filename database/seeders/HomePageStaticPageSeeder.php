<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticPage;

class HomePageStaticPageSeeder extends Seeder
{
    public function run(): void
    {
        StaticPage::updateOrCreate(
            ['key' => 'home_page'],
            [
                'is_active'  => true,
                'sort_order' => 0,
                'content' => [

                    // ======================
                    // HERO
                    // ======================
                    'hero' => [
                        'eyebrow' => [
                            'tr' => 'Hayalinizdeki',
                            'en' => 'Your dream',
                        ],
                        'title' => [
                            'tr' => 'Tatil Sizi Bekliyor',
                            'en' => 'Vacation Awaits You',
                        ],
                        'subtitle' => [
                            'tr' => 'Erken rezervasyon fırsatlarını kaçırmayın!',
                            'en' => 'Don’t miss early booking opportunities!',
                        ],
                    ],

                    // ======================
                    // POPÜLER OTELLER
                    // ======================
                    'popular_hotels' => [
                        'section_eyebrow' => [
                            'tr' => 'İçmeler',
                            'en' => 'Icmeler',
                        ],
                        'section_title' => [
                            'tr' => 'Popüler Oteller',
                            'en' => 'Popular Hotels',
                        ],
                        'hero_title' => [
                            'tr' => 'İçmeler’de Tatilin Kalbi',
                            'en' => 'The Heart of a Holiday in Icmeler',
                        ],
                        'description' => [
                            'tr' => 'Ege’nin incisi Marmaris’te denize sıfır, her şey dahil konseptli otelleri keşfedin.',
                            'en' => 'Discover beachfront, all-inclusive hotels in Marmaris, the jewel of the Aegean.',
                        ],
                        'button' => [
                            'text' => [
                                'tr' => 'İçmeler Otelleri',
                                'en' => 'Icmeler Hotels',
                            ],
                            'href' => [
                                'tr' => '/bolgeler/icmeler',
                                'en' => '/en/regions/icmeler',
                            ],
                        ],
                        'carousel' => [
                            'mode' => 'latest', // latest | manual | by_location
                            'per_page' => 4,
                            'total' => 8,
                        ],
                    ],

                    // ======================
                    // GEZİ REHBERİ
                    // ======================
                    'travel_guides' => [
                        'hero_title' => [
                            'tr' => 'Gezi Rehberi',
                            'en' => 'Travel Guide',
                        ],
                        'title' => [
                            'tr' => 'Bölgeler, gezilecek yerler ve ipuçları — keşfe başlayın.',
                            'en' => 'Regions, places to visit and tips — start exploring.',
                        ],
                        'description' => [
                            'tr' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\nAliquam libero augue, tristique ut nunc eget.",
                            'en' => "Discover destinations, sights and local tips.\nPlan your journey with our curated guides.",
                        ],
                        'grid' => [
                            'mode' => 'latest', // latest | manual
                            'limit' => 4,
                        ],
                    ],
                ],
            ]
        );
    }
}
