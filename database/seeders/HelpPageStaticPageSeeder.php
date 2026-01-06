<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticPage;

class HelpPageStaticPageSeeder extends Seeder
{
    public function run(): void
    {
        StaticPage::updateOrCreate(
        // Sayfa yoksa oluşturulacak anahtar
            ['key' => 'help_page'],
            [
                'is_active' => true,
                'content' => [
                    'page_header' => [
                        'title' => [
                            'tr' => 'Yardım & Sık Sorulan Sorular',
                            'en' => 'Help & Frequently Asked Questions',
                        ],
                        'description' => [
                            'tr' => 'Nasıl yardımcı olabiliriz?',
                            'en' => 'How can we help you?',
                        ],
                    ],

                    'faq_items' => [
                        [
                            'question' => [
                                'tr' => 'Ödemem onaylandı mı, rezervasyonum kesinleşti mi?',
                                'en' => 'Is my payment approved, is my reservation confirmed?',
                            ],
                            'answer' => [
                                'tr' => 'Ödeme başarıyla tamamlandığında rezervasyonunuz otomatik olarak oluşturulur ve onay e-postası gönderilir.',
                                'en' => 'Once the payment is completed successfully, your reservation is created automatically and a confirmation email is sent.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Rezervasyonumu nasıl iptal edebilirim?',
                                'en' => 'How can I cancel my reservation?',
                            ],
                            'answer' => [
                                'tr' => 'Hesabım > Rezervasyonlarım bölümünden ilgili rezervasyonu seçerek iptal talebi oluşturabilirsiniz.',
                                'en' => 'You can submit a cancellation request from My Account > My Reservations by selecting the relevant booking.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'İptal ve iade koşulları nelerdir?',
                                'en' => 'What are the cancellation and refund conditions?',
                            ],
                            'answer' => [
                                'tr' => 'İptal ve iade koşulları seçilen ürün ve tarihe göre değişiklik gösterebilir.',
                                'en' => 'Cancellation and refund conditions may vary depending on the selected product and dates.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Rezervasyon bilgilerini nereden görüntüleyebilirim?',
                                'en' => 'Where can I view my reservation details?',
                            ],
                            'answer' => [
                                'tr' => 'Rezervasyon bilgilerinize Hesabım > Rezervasyonlarım sayfasından ulaşabilirsiniz.',
                                'en' => 'You can view your reservation details from My Account > My Reservations.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Ödeme yöntemleri nelerdir?',
                                'en' => 'What payment methods are available?',
                            ],
                            'answer' => [
                                'tr' => 'Kredi kartı ve desteklenen diğer online ödeme yöntemleri ile ödeme yapabilirsiniz.',
                                'en' => 'You can pay using credit cards and other supported online payment methods.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Transfer hizmeti nasıl çalışır?',
                                'en' => 'How does the transfer service work?',
                            ],
                            'answer' => [
                                'tr' => 'Transfer rezervasyonunuzda belirttiğiniz tarih ve saatlerde şoförümüz sizi karşılar.',
                                'en' => 'Our driver will meet you at the specified date and time you provided in your transfer booking.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Çoklu dil desteği var mı?',
                                'en' => 'Is multi-language support available?',
                            ],
                            'answer' => [
                                'tr' => 'Evet, sitemiz birden fazla dili desteklemektedir.',
                                'en' => 'Yes, our website supports multiple languages.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Rezervasyon sonrası destek alabilir miyim?',
                                'en' => 'Can I get support after making a reservation?',
                            ],
                            'answer' => [
                                'tr' => 'Her zaman destek ekibimize destek talebi oluşturarak ulaşabilirsiniz.',
                                'en' => 'You can always reach our support team by creating a support request.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Fiyatlara vergiler dahil mi?',
                                'en' => 'Are taxes included in the prices?',
                            ],
                            'answer' => [
                                'tr' => 'Gösterilen fiyatlara ilgili vergiler dahildir.',
                                'en' => 'All applicable taxes are included in the displayed prices.',
                            ],
                        ],
                        [
                            'question' => [
                                'tr' => 'Rezervasyonumu başkasına devredebilir miyim?',
                                'en' => 'Can I transfer my reservation to someone else?',
                            ],
                            'answer' => [
                                'tr' => 'Bazı ürünlerde mümkün olabilir; detaylar için destek ekibimizle iletişime geçin.',
                                'en' => 'It may be possible for some products; please contact our support team for details.',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
