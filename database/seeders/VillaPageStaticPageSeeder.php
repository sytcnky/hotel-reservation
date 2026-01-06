<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class VillaPageStaticPageSeeder extends Seeder
{
    public function run(): void
    {
        StaticPage::updateOrCreate(
            ['key' => 'villa_page'],
            [
                'is_active'  => true,
                'sort_order' => 0,
                'content'    => [
                    'page_header' => [
                        'title' => [
                            'tr' => 'Kendini evinde hisset',
                            'en' => 'Feel at home',
                        ],
                        'description' => [
                            'tr' => "İçmelerdeki birbirinden güzel villarda konforu ve size özel ayrıcalıkları keşfedin.",
                            'en' => "Discover comfort and exclusive privileges in beautiful villas in Icmeler.",
                        ],
                    ],

                    'page_content' => [
                        'title' => [
                            'tr' => "Güzel bir tatil sözü veriyoruz.",
                            'en' => "We promise a great holiday.",
                        ],
                        'description' => [
                            'tr' => "ICR olarak sunduğumuz villa kiralama hizmetiyle, sadece konaklama değil,\n"
                                . "eksiksiz bir tatil deneyimi vadediyoruz. Tüm villalarımız, profesyonel\n"
                                . "ekiplerimiz tarafından yerinde incelenir ve yalnızca en yüksek standartları\n"
                                . "karşılayan evler sistemimize dahil edilir. Her bir villa; konfor, temizlik ve\n"
                                . "güvenlik kriterlerine göre özenle seçilmiştir.",
                            'en' => "With ICR’s villa rental service, we offer not only accommodation,\n"
                                . "but a complete holiday experience. All our villas are inspected on-site\n"
                                . "by our professional teams and only homes meeting the highest standards\n"
                                . "are added to our system. Each villa is carefully selected based on\n"
                                . "comfort, cleanliness, and safety criteria.",
                        ],
                        'image_texts' => [
                            [
                                'tr' => 'Her ev uzmanlar tarafından incelenir, standartlarımızı karşılamaları gerekir.',
                                'en' => 'Every home is inspected by experts and must meet our standards.',
                            ],
                            [
                                'tr' => 'Kusursuz derecede temiz, bakımlı evler.',
                                'en' => 'Impeccably clean and well-maintained homes.',
                            ],
                            [
                                'tr' => 'Tanıdığımız, yüksek kaliteli ev sahipliği geçmişi olan ev sahipleri.',
                                'en' => 'Hosts we know, with a strong track record of quality hosting.',
                            ],
                            [
                                'tr' => 'Nadiren de olsa ev sahibinizin iptal etmesi durumunda içinizin rahat olması.',
                                'en' => 'Peace of mind even in the rare case your host cancels.',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
