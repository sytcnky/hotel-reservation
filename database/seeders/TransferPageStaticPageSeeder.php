<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class TransferPageStaticPageSeeder extends Seeder
{
    public function run(): void
    {
        StaticPage::updateOrCreate(
            ['key' => 'transfer_page'],
            [
                'is_active'  => true,
                'sort_order' => 0,
                'content'    => [
                    'page_header' => [
                        'title' => [
                            'tr' => 'Yolculuğunuzun en kolay kısmı burası',
                            'en' => 'The easiest part of your journey is here',
                        ],
                        'description' => [
                            'tr' => "Ulaşımınızı şansa bırakmayın. Tatil başlangıcınızdan dönüşünüze kadar\nkonforlu ve güvenilir transfer hizmetleriyle yanınızdayız.",
                            'en' => "Don’t leave your transportation to chance. From the start of your holiday until your return,\nwe’re here with comfortable and reliable transfer services.",
                        ],
                    ],

                    'page_content' => [
                        'title' => [
                            'tr' => "Yolculuğunuz bizimle başlar,\nkonfor hiç bitmez.",
                            'en' => "Your journey starts with us,\ncomfort never ends.",
                        ],
                        'description' => [
                            'tr' => "Havalimanından otelinize kadar tüm yolculuklarınızda Mercedes Vito ve\nSprinter araçlarımızla size özel, konforlu ve güvenli transfer hizmeti sunuyoruz.",
                            'en' => "On every trip from the airport to your hotel, we provide a private, comfortable and safe transfer\nservice with our Mercedes Vito and Sprinter vehicles.",
                        ],

                        'icons' => [
                            ['icon' => 'fi fi-sr-wifi', 'text' => ['tr' => '', 'en' => '']],
                            ['icon' => 'fi fi-sr-air-conditioner', 'text' => ['tr' => '', 'en' => '']],
                            ['icon' => 'fi fi-sr-charging-station', 'text' => ['tr' => '', 'en' => '']],
                            ['icon' => 'fi fi-sr-martini-glass-citrus', 'text' => ['tr' => '', 'en' => '']],
                            ['icon' => 'fi fi-sr-baby-carriage', 'text' => ['tr' => '', 'en' => '']],
                        ],

                        'content_title' => [
                            'tr' => 'Öncelik güvenlik ve konfor',
                            'en' => 'Safety and comfort first',
                        ],
                        'content_text' => [
                            'tr' => "Tüm yolculuklarımızda üst düzey konfor ve güvenliği standart kabul ediyoruz.\nAraçlarımızda bulunan gelişmiş donanımlar sayesinde, havalimanından otelinize kadar\nolan her anı keyifle geçirmeniz için her şeyi düşündük.",
                            'en' => "We consider superior comfort and safety as a standard in all our journeys.\nThanks to the advanced equipment in our vehicles, we’ve thought of everything\nso you can enjoy every moment from the airport to your hotel.",
                        ],

                        'features' => [
                            'tr' => [
                                ['text' => 'Derin bagaj hacmi'],
                                ['text' => 'Klima, Wifi ve mini buzdolabı'],
                                ['text' => 'Profesyonel sürücüler'],
                                ['text' => 'Bebek koltuğu opsiyonu'],
                                ['text' => 'Deri koltuklar ve geniş iç hacim'],
                                ['text' => 'USB şarj ve multimedya sistemi'],
                                ['text' => 'Karartmalı camlar'],
                                ['text' => 'Özel iç aydınlatma'],
                            ],
                            'en' => [
                                ['text' => 'Large luggage capacity'],
                                ['text' => 'A/C, Wi-Fi and mini fridge'],
                                ['text' => 'Professional drivers'],
                                ['text' => 'Baby seat option'],
                                ['text' => 'Leather seats and spacious interior'],
                                ['text' => 'USB charging and multimedia system'],
                                ['text' => 'Tinted windows'],
                                ['text' => 'Ambient interior lighting'],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
