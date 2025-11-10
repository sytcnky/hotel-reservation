<?php

namespace Database\Seeders;

use App\Models\TourService;
use Illuminate\Database\Seeder;

class TourServiceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Rehberlik & girişler
            ['tr' => 'Profesyonel Rehberlik',          'en' => 'Professional Guiding'],
            ['tr' => 'Sesli Rehber (Audio Guide)',     'en' => 'Audio Guide'],
            ['tr' => 'Müze/Ören Yeri Girişleri',       'en' => 'Entrance Fees'],
            ['tr' => 'Ulusal Park Girişleri',          'en' => 'National Park Fees'],
            ['tr' => 'Hızlı Giriş (Skip-the-line)',    'en' => 'Skip-the-line Access'],

            // Ulaşım
            ['tr' => 'Otelden Alım (Pickup)',          'en' => 'Hotel Pickup'],
            ['tr' => 'Otele Bırakma (Drop-off)',       'en' => 'Hotel Drop-off'],
            ['tr' => 'Transfer (Klimalı Araç)',        'en' => 'A/C Vehicle Transfer'],
            ['tr' => 'Tekne Turu / Bot Transfer',      'en' => 'Boat Tour / Transfer'],
            ['tr' => 'Teleferik Ücreti',               'en' => 'Cable Car Ticket'],

            // Yeme/içme
            ['tr' => 'Kahvaltı',                        'en' => 'Breakfast'],
            ['tr' => 'Öğle Yemeği',                    'en' => 'Lunch'],
            ['tr' => 'Akşam Yemeği',                   'en' => 'Dinner'],
            ['tr' => 'Aperatif / Atıştırmalıklar',     'en' => 'Snacks'],
            ['tr' => 'Alkolsüz İçecekler',             'en' => 'Soft Drinks'],
            ['tr' => 'Sıcak İçecekler (Çay/Kahve)',    'en' => 'Hot Beverages'],
            ['tr' => 'Alkollü İçecekler',              'en' => 'Alcoholic Beverages'],

            // Ekipman & güvenlik
            ['tr' => 'Ekipman Kiralama (Snorkel vb.)', 'en' => 'Equipment Rental (Snorkel etc.)'],
            ['tr' => 'Güvenlik Ekipmanları',           'en' => 'Safety Gear'],
            ['tr' => 'Dalış Eğitmeni / Eğitmen',       'en' => 'Instructor'],
            ['tr' => 'Cankurtaran',                    'en' => 'Lifeguard'],

            // Diğer hizmetler
            ['tr' => 'Seyahat Sigortası',              'en' => 'Travel Insurance'],
            ['tr' => 'Fotoğraf/Video Hizmeti',         'en' => 'Photo/Video Service'],
            ['tr' => 'Wi-Fi (Araç/tekne)',             'en' => 'Wi-Fi Onboard'],
            ['tr' => 'Otopark / Park Ücretleri',       'en' => 'Parking Fees'],
            ['tr' => 'Yakıt Farkı / HGS-ÖTV',          'en' => 'Fuel/Toll Surcharge'],
            ['tr' => 'Küçük Grup Garantisi',           'en' => 'Small-Group Guarantee'],
            ['tr' => 'Özel Tur (Private)',             'en' => 'Private Tour'],
            ['tr' => 'Tekerlekli Sandalye Uygunluğu',  'en' => 'Wheelchair Accessible'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => \Str::slug($name['tr']),
                'en' => \Str::slug($name['en']),
            ];

            $existing = TourService::query()
                ->whereRaw("slug->> 'tr' = ?", [$slug['tr']])
                ->orWhereRaw("slug->> 'en' = ?", [$slug['en']])
                ->first();

            if ($existing) {
                $existing->fill([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $existing->description ?? [],
                    'is_active'   => true,
                    'sort_order'  => $i + 1,
                ])->save();
            } else {
                TourService::create([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => [],
                    'is_active'   => true,
                    'sort_order'  => $i + 1,
                ]);
            }
        }
    }
}
