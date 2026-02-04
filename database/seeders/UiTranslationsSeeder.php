<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class UiTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            [
                'group' => 'ui',
                'key' => 'order',
                'values' => [
                    'tr' => 'Sıralama',
                    'en' => 'Sort',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'order_price_asc',
                'values' => [
                    'tr' => 'Fiyat (Artan)',
                    'en' => 'Price (Low to High)',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'order_price_desc',
                'values' => [
                    'tr' => 'Fiyat (Azalan)',
                    'en' => 'Price (High to Low)',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'order_name_asc',
                'values' => [
                    'tr' => 'A-Z',
                    'en' => 'A–Z',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'order_name_desc',
                'values' => [
                    'tr' => 'Z-A',
                    'en' => 'Z–A',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'video',
                'values' => [
                    'tr' => 'Video',
                    'en' => 'Video',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'back',
                'values' => [
                    'tr' => 'Geri dön',
                    'en' => 'Go back',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'checkin_checkout_dates',
                'values' => [
                    'tr' => 'Giriş - Çıkış Tarihi',
                    'en' => 'Checkin - Checkout Tarihi',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'choose_dates',
                'values' => [
                    'tr' => 'Tarih seçin',
                    'en' => 'Choose dates',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'board_type',
                'values' => [
                    'tr' => 'Konaklama Tipi',
                    'en' => 'Board Type',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'guests',
                'values' => [
                    'tr' => 'Misafirler',
                    'en' => 'Guests',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'guest.placeholder',
                'values' => [
                    'tr' => 'Kişi sayısı seçin',
                    'en' => 'Select number of guests',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'adult',
                'values' => [
                    'tr' => 'Yetişkin',
                    'en' => 'Adult',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'child',
                'values' => [
                    'tr' => 'Çocuk',
                    'en' => 'Child',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'infant',
                'values' => [
                    'tr' => 'Bebek',
                    'en' => 'Infant',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'search',
                'values' => [
                    'tr' => 'Ara',
                    'en' => 'Search',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'nearby_places',
                'values' => [
                    'tr' => 'Nearby Places',
                    'en' => 'Yakındaki Yerler',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'for_price',
                'values' => [
                    'tr' => 'Select the date and number of guests for pricing.',
                    'en' => 'Fiyat için tarih ve misafir sayısı seçin',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'capacity_message',
                'values' => [
                    'tr' => 'Bu odanın maksimum kapasitesi {count} kişidir.',
                    'en' => 'The maximum capacity of this room is {count} guests.',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'night',
                'values' => [
                    'tr' => 'gece',
                    'en' => 'night',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'room_features',
                'values' => [
                    'tr' => 'Oda Özellikleri:',
                    'en' => 'Room Features:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'room_size',
                'values' => [
                    'tr' => 'Oda Boyutu:',
                    'en' => 'Room Size:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'bed_type',
                'values' => [
                    'tr' => 'Yatak Tipi:',
                    'en' => 'Bed Type:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'room_capacity',
                'values' => [
                    'tr' => 'Kapasite:',
                    'en' => 'Capacity:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'room_view',
                'values' => [
                    'tr' => 'Manzara:',
                    'en' => 'View:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'smoking',
                'values' => [
                    'tr' => 'Sigara:',
                    'en' => 'Smoking:',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'smoking.allowed',
                'values' => [
                    'tr' => 'İçilebilir',
                    'en' => 'Smoking allowed',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'smoking.not_allowed',
                'values' => [
                    'tr' => 'İçilmez',
                    'en' => 'Non-smoking',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'add_cart',
                'values' => [
                    'tr' => 'Sepete ekle',
                    'en' => 'Add to Cart',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'passengers',
                'values' => [
                    'tr' => 'Yolcular',
                    'en' => 'Passengers',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'route',
                'values' => [
                    'tr' => 'Rota',
                    'en' => 'Route',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'pickup_date',
                'values' => [
                    'tr' => 'Geliş Tarihi',
                    'en' => 'Pickup Date',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'departure_date',
                'values' => [
                    'tr' => 'Geliş Tarihi',
                    'en' => 'Departure Date',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'return_date',
                'values' => [
                    'tr' => 'Dönüş Tarihi',
                    'en' => 'Return Date',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'estimated_duration',
                'values' => [
                    'tr' => 'Tahmini Süre',
                    'en' => 'Estimated Duration',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'duration',
                'values' => [
                    'tr' => 'Süre',
                    'en' => 'Duration',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'starting_time',
                'values' => [
                    'tr' => 'Başlangıç Saati',
                    'en' => 'Starting Time',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'days',
                'values' => [
                    'tr' => 'Günler',
                    'en' => 'Days',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.mon',
                'values' => [
                    'tr' => 'Pzt',
                    'en' => 'Mon',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.tue',
                'values' => [
                    'tr' => 'Sal',
                    'en' => 'Tue',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.wed',
                'values' => [
                    'tr' => 'Çar',
                    'en' => 'Wed',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.thu',
                'values' => [
                    'tr' => 'Per',
                    'en' => 'Thu',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.fri',
                'values' => [
                    'tr' => 'Cum',
                    'en' => 'Fri',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.sat',
                'values' => [
                    'tr' => 'Cum',
                    'en' => 'Sat',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'weekdays.sun',
                'values' => [
                    'tr' => 'Paz',
                    'en' => 'Sun',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'min_age',
                'values' => [
                    'tr' => 'Min. Yaş',
                    'en' => 'Min. Age',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'estimated_duration_min',
                'values' => [
                    'tr' => 'dk',
                    'en' => 'min',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'pickup',
                'values' => [
                    'tr' => 'Alınış',
                    'en' => 'Pickup',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'pickup.time',
                'values' => [
                    'tr' => 'Saati',
                    'en' => 'Time',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'pickup.flight_number',
                'values' => [
                    'tr' => 'Uçuş Numarası',
                    'en' => 'Flight Number',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'return',
                'values' => [
                    'tr' => 'Dönüş',
                    'en' => 'Return',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'return.time',
                'values' => [
                    'tr' => 'Saati',
                    'en' => 'Time',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'return.flight_number',
                'values' => [
                    'tr' => 'Uçuş Numarası',
                    'en' => 'Flight Number',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'total_price',
                'values' => [
                    'tr' => 'Toplam ücret',
                    'en' => 'Total price',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'price',
                'values' => [
                    'tr' => 'Ücret',
                    'en' => 'Price',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'free',
                'values' => [
                    'tr' => 'Ücretsiz',
                    'en' => 'Free',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'total_price',
                'values' => [
                    'tr' => 'Toplam ücret',
                    'en' => 'Total price',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'one_way',
                'values' => [
                    'tr' => 'Tek Yön',
                    'en' => 'One Way',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'round_trip',
                'values' => [
                    'tr' => 'Gidiş-Dönüş',
                    'en' => 'Round Trip',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'transfer_from',
                'values' => [
                    'tr' => 'Nereden',
                    'en' => 'From',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'transfer_to',
                'values' => [
                    'tr' => 'Nereye',
                    'en' => 'To',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'flight_number_example',
                'values' => [
                    'tr' => 'Örn: TK1235',
                    'en' => 'e.g.: TK1235',
                ],
            ],
            [
                'group' => 'validation',
                'key' => 'transfer.pickup_pair_required',
                'values' => [
                    'tr' => 'Seçili alanda bilgi girmelisiniz. (Saat veya Uçuş Numarası)',
                    'en' => 'You must enter a value for the selected field. (Time or Flight Number)',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'bedroom',
                'values' => [
                    'tr' => 'Yatak odası',
                    'en' => 'Bedroom',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'bathroom',
                'values' => [
                    'tr' => 'Banyo',
                    'en' => 'Bathroom',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'villa.prepayment',
                'values' => [
                    'tr' => 'Sadece ön ödeme yaparsınız, kalan tutar konaklama sırasında nakit yapılır.',
                    'en' => 'You only make a prepayment; the remaining amount is paid in cash during your stay.',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'nightly',
                'values' => [
                    'tr' => 'Gecelik',
                    'en' => 'Nightly',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'prepayment',
                'values' => [
                    'tr' => 'Ön ödeme',
                    'en' => 'Pre-payment',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'key_features',
                'values' => [
                    'tr' => 'Öde Çıkan Özellikler',
                    'en' => 'Key Features',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'about_this_stay',
                'values' => [
                    'tr' => 'About This Stay',
                    'en' => 'Key Features',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'included',
                'values' => [
                    'tr' => 'Dahil Olanlar',
                    'en' => 'Included',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'not_included',
                'values' => [
                    'tr' => 'Dahil Olmayanlar',
                    'en' => 'Not Included',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'all_excursions',
                'values' => [
                    'tr' => 'Tüm Turlar',
                    'en' => 'All Excursions',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'recommended_accommodation',
                'values' => [
                    'tr' => 'Önerilen Konaklama',
                    'en' => 'Recommended Accommodation',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'recommended_villa',
                'values' => [
                    'tr' => 'Önerilen Villa',
                    'en' => 'Recommended Villa',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'popular_excursions',
                'values' => [
                    'tr' => 'Bölgenin Popüler Turları',
                    'en' => 'Popular Excursions in the Region',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'active_coupons',
                'values' => [
                    'tr' => 'Aktif Kuponlar',
                    'en' => 'Active Coupons',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'old_coupons',
                'values' => [
                    'tr' => 'Geçmiş Kuponlar',
                    'en' => 'Old Coupons',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'no_active_coupons',
                'values' => [
                    'tr' => 'Şu anda aktif kuponunuz bulunmamaktadır.',
                    'en' => 'You currently have no active coupons.',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'no_old_coupons',
                'values' => [
                    'tr' => 'Geçmiş kupon kaydınız bulunmamaktadır.',
                    'en' => 'You have no previous coupon records.',
                ],
            ],
            [
                'group' => 'ui',
                'key' => 'detail',
                'values' => [
                    'tr' => 'Detay',
                    'en' => 'Detail',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'status.used',
                'values' => [
                    'tr' => 'Kullanıldı',
                    'en' => 'Used',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'status.not_started',
                'values' => [
                    'tr' => 'Henüz başlamadı',
                    'en' => 'Not started yet',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'status.expired',
                'values' => [
                    'tr' => 'Süresi doldu',
                    'en' => 'Expired',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'status.active',
                'values' => [
                    'tr' => 'Aktif',
                    'en' => 'Active',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'validity.range',
                'values' => [
                    'tr' => 'Geçerlilik: {from} – {to}',
                    'en' => 'Validity: {from} – {to}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'validity.until',
                'values' => [
                    'tr' => 'Son kullanım: {to}',
                    'en' => 'Valid until: {to}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'validity.from',
                'values' => [
                    'tr' => 'Başlangıç: {from}',
                    'en' => 'Starts from: {from}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'validity.unspecified',
                'values' => [
                    'tr' => 'Geçerlilik tarihi: Belirtilmemiş',
                    'en' => 'Validity date: Not specified',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'limit.amount',
                'values' => [
                    'tr' => 'Alt limit: {amount}',
                    'en' => 'Minimum limit: {amount}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'limit.nights',
                'values' => [
                    'tr' => 'Alt limit: {count} Gece',
                    'en' => 'Minimum limit: {count} Nights',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'limit.none',
                'values' => [
                    'tr' => 'Alt limit: Yok',
                    'en' => 'Minimum limit: None',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'discount.max',
                'values' => [
                    'tr' => 'Maksimum indirim: {amount}',
                    'en' => 'Maximum discount: {amount}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'usage.remaining',
                'values' => [
                    'tr' => 'Kalan kullanım: {count}',
                    'en' => 'Remaining uses: {count}',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'usage.unlimited',
                'values' => [
                    'tr' => 'Kullanım sınırı yok',
                    'en' => 'No usage limit',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'label.status',
                'values' => [
                    'tr' => 'Durum',
                    'en' => 'Status',
                ],
            ],
            [
                'group' => 'coupon',
                'key'   => 'label.discount_amount',
                'values' => [
                    'tr' => 'İndirim tutarı',
                    'en' => 'Discount amount',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.empty.title',
                'values' => [
                    'tr' => 'Henüz destek talebinde bulunmadınız.',
                    'en' => 'You have not created any support tickets yet.',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.empty.desc',
                'values' => [
                    'tr' => 'Bir sorun yaşarsanız buradan yeni talep oluşturabilirsiniz.',
                    'en' => 'If you have an issue, you can create a new ticket here.',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.action.create',
                'values' => [
                    'tr' => 'Yeni Talep Oluştur',
                    'en' => 'Create New Ticket',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.help.title',
                'values' => [
                    'tr' => 'Yardıma mı ihtiyacınız var?',
                    'en' => 'Do you need help?',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.help.desc',
                'values' => [
                    'tr' => 'Talep oluşturup sorununuzu bizimle paylaşın.',
                    'en' => 'Create a ticket and share your issue with us.',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.sort.new_old',
                'values' => [
                    'tr' => 'Tarih: Yeni → Eski',
                    'en' => 'Date: New → Old',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.sort.old_new',
                'values' => [
                    'tr' => 'Tarih: Eski → Yeni',
                    'en' => 'Date: Old → New',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.filter.all',
                'values' => [
                    'tr' => 'Tümü',
                    'en' => 'All',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.status.waiting',
                'values' => [
                    'tr' => 'Yanıt Bekliyor',
                    'en' => 'Waiting for reply',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.status.answered',
                'values' => [
                    'tr' => 'Yanıtlandı',
                    'en' => 'Answered',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.status.closed',
                'values' => [
                    'tr' => 'Kapalı',
                    'en' => 'Closed',
                ],
            ],
            [
                'group'  => 'account',
                'key'    => 'tickets.aria.open',
                'values' => [
                    'tr' => 'Talep detayına git',
                    'en' => 'Go to ticket details',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.meta.no',
                'values' => [
                    'tr' => 'Talep No',
                    'en' => 'Ticket No',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.status.waiting',
                'values' => [
                    'tr' => 'Yanıt Bekliyor',
                    'en' => 'Waiting for reply',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.status.answered',
                'values' => [
                    'tr' => 'Yanıtlandı',
                    'en' => 'Answered',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.status.closed',
                'values' => [
                    'tr' => 'Kapalı',
                    'en' => 'Closed',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.meta.created',
                'values' => [
                    'tr' => 'Oluşturulma',
                    'en' => 'Created',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.meta.updated',
                'values' => [
                    'tr' => 'Son Güncelleme',
                    'en' => 'Last Updated',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.meta.order',
                'values' => [
                    'tr' => 'Sipariş',
                    'en' => 'Order',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.author.support',
                'values' => [
                    'tr' => 'Destek',
                    'en' => 'Support',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.author.user',
                'values' => [
                    'tr' => 'Kullanıcı',
                    'en' => 'User',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.messages.empty',
                'values' => [
                    'tr' => 'Henüz mesaj yok.',
                    'en' => 'No messages yet.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.reply.placeholder',
                'values' => [
                    'tr' => 'Mesajınızı yazın...',
                    'en' => 'Write your message...',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.reply.required',
                'values' => [
                    'tr' => 'Mesaj alanı zorunludur.',
                    'en' => 'Message is required.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.add',
                'values' => [
                    'tr' => 'Dosya Ekle',
                    'en' => 'Add File',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.add_more',
                'values' => [
                    'tr' => 'Başka Dosya Ekle',
                    'en' => 'Add Another File',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.hint',
                'values' => [
                    'tr' => '.jpg .jpeg .png .webp — Maks 2MB',
                    'en' => '.jpg .jpeg .png .webp — Max 2MB',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.security_notice',
                'values' => [
                    'tr' => 'Kişisel bilgileriniz, hesap veya kredi kartı şifreniz gibi bilgileri kesinlikle paylaşmayın.',
                    'en' => 'Never share personal, account, or credit card information.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.action.reply',
                'values' => [
                    'tr' => 'Yanıtla',
                    'en' => 'Reply',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.action.back',
                'values' => [
                    'tr' => 'Geri dön',
                    'en' => 'Go back',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.create.title',
                'values' => [
                    'tr' => 'Yeni Destek Talebi',
                    'en' => 'New Support Ticket',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.create.subtitle',
                'values' => [
                    'tr' => 'Konu tipini seçip talebinizi oluşturabilirsiniz.',
                    'en' => 'Select a topic and create your request.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.form.category',
                'values' => [
                    'tr' => 'Konu Tipi',
                    'en' => 'Category',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.form.order',
                'values' => [
                    'tr' => 'Sipariş',
                    'en' => 'Order',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.form.subject',
                'values' => [
                    'tr' => 'Konu Başlığı',
                    'en' => 'Subject',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.form.select',
                'values' => [
                    'tr' => 'Seçiniz',
                    'en' => 'Select',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.form.order_has_ticket',
                'values' => [
                    'tr' => 'Mevcut destek talebi var',
                    'en' => 'Support ticket already exists',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.reply.placeholder',
                'values' => [
                    'tr' => 'Mesajınızı yazın...',
                    'en' => 'Write your message...',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.add',
                'values' => [
                    'tr' => 'Dosya Ekle',
                    'en' => 'Add File',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.add_more',
                'values' => [
                    'tr' => 'Başka Dosya Ekle',
                    'en' => 'Add Another File',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.attachments.optional',
                'values' => [
                    'tr' => 'Dosya ekleri isteğe bağlıdır.',
                    'en' => 'Attachments are optional.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.security_notice',
                'values' => [
                    'tr' => 'Kişisel bilgileriniz, hesap veya kredi kartı şifreniz gibi bilgileri kesinlikle paylaşmayın.',
                    'en' => 'Never share personal, account, or credit card information.',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.action.create',
                'values' => [
                    'tr' => 'Talep Oluştur',
                    'en' => 'Create Ticket',
                ],
            ],
            [
                'group' => 'account',
                'key'   => 'tickets.action.back',
                'values' => [
                    'tr' => 'Geri dön',
                    'en' => 'Go back',
                ],
            ],
            // Alerts
            [
                'group' => 'account',
                'key'   => 'settings.alert.profile_updated',
                'values'=> ['tr' => 'Bilgileriniz güncellendi.', 'en' => 'Your information has been updated.'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.alert.password_updated',
                'values'=> ['tr' => 'Şifreniz güncellendi.', 'en' => 'Your password has been updated.'],
            ],

            // Profile
            [
                'group' => 'account',
                'key'   => 'settings.profile.title',
                'values'=> ['tr' => 'Üyelik Bilgilerim', 'en' => 'Account Information'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.first_name.label',
                'values'=> ['tr' => 'Ad', 'en' => 'First Name'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.first_name.placeholder',
                'values'=> ['tr' => 'Adınız', 'en' => 'Your first name'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.last_name.label',
                'values'=> ['tr' => 'Soyad', 'en' => 'Last Name'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.last_name.placeholder',
                'values'=> ['tr' => 'Soyadınız', 'en' => 'Your last name'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.phone.label',
                'values'=> ['tr' => 'Telefon', 'en' => 'Phone'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.profile.email.placeholder',
                'values'=> ['tr' => 'ornek@eposta.com', 'en' => 'example@email.com'],
            ],

            // Common actions
            [
                'group' => 'account',
                'key'   => 'settings.actions.save',
                'values'=> ['tr' => 'Kaydet', 'en' => 'Save'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.actions.cancel',
                'values'=> ['tr' => 'İptal', 'en' => 'Cancel'],
            ],

            // Password
            [
                'group' => 'account',
                'key'   => 'settings.password.title',
                'values'=> ['tr' => 'Şifre Değiştir', 'en' => 'Change Password'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.password.action.start_change',
                'values'=> ['tr' => 'Değiştir', 'en' => 'Change'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.password.current.label',
                'values'=> ['tr' => 'Mevcut Şifre', 'en' => 'Current Password'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.password.new.label',
                'values'=> ['tr' => 'Yeni Şifre', 'en' => 'New Password'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.password.confirm.label',
                'values'=> ['tr' => 'Yeni Şifre (Tekrar)', 'en' => 'Confirm New Password'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.password.action.submit',
                'values'=> ['tr' => 'Şifremi Değiştir', 'en' => 'Update Password'],
            ],

            // Connected accounts
            [
                'group' => 'account',
                'key'   => 'settings.connected_accounts.title',
                'values'=> ['tr' => 'Bağlı Hesaplar', 'en' => 'Connected Accounts'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.connected_accounts.status.not_connected',
                'values'=> ['tr' => 'Bağlı değil', 'en' => 'Not connected'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.connected_accounts.status.connected',
                'values'=> ['tr' => 'Bağlı', 'en' => 'Connected'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.connected_accounts.actions.connect',
                'values'=> ['tr' => 'Bağla', 'en' => 'Connect'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.connected_accounts.actions.remove',
                'values'=> ['tr' => 'Kaldır', 'en' => 'Remove'],
            ],

            // Email settings
            [
                'group' => 'account',
                'key'   => 'settings.email.title',
                'values'=> ['tr' => 'E-Posta Ayarlarım', 'en' => 'Email Settings'],
            ],
            [
                'group' => 'account',
                'key'   => 'settings.email.marketing',
                'values'=> ['tr' => 'Kampanyalar ve fırsatlar', 'en' => 'Campaigns and offers'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.filter.status_all',
                'values'=> ['tr' => 'Tümü', 'en' => 'All'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.sort.date_desc',
                'values'=> ['tr' => 'Tarih (Yeni → Eski)', 'en' => 'Date (Newest → Oldest)'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.sort.date_asc',
                'values'=> ['tr' => 'Tarih (Eski → Yeni)', 'en' => 'Date (Oldest → Newest)'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.sort.price_desc',
                'values'=> ['tr' => 'Tutar (Yüksek → Düşük)', 'en' => 'Amount (High → Low)'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.sort.price_asc',
                'values'=> ['tr' => 'Tutar (Düşük → Yüksek)', 'en' => 'Amount (Low → High)'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.empty',
                'values'=> ['tr' => 'Henüz bir rezervasyonunuz yok.', 'en' => 'You have no bookings yet.'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.value.empty',
                'values'=> ['tr' => '-', 'en' => '-'],
            ],

            // Fields (DT)
            [
                'group' => 'account',
                'key'   => 'bookings.fields.hotel',
                'values'=> ['tr' => 'Otel', 'en' => 'Hotel'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.room',
                'values'=> ['tr' => 'Oda', 'en' => 'Room'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.board_type',
                'values'=> ['tr' => 'Konaklama Tipi', 'en' => 'Board Type'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.dates',
                'values'=> ['tr' => 'Tarihler', 'en' => 'Dates'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.guests',
                'values'=> ['tr' => 'Misafirler', 'en' => 'Guests'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.fee',
                'values'=> ['tr' => 'Ücret', 'en' => 'Fee'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.fields.route',
                'values'=> ['tr' => 'Rota', 'en' => 'Route'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.vehicle',
                'values'=> ['tr' => 'Araç', 'en' => 'Vehicle'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.departure',
                'values'=> ['tr' => 'Geliş', 'en' => 'Arrival'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.return',
                'values'=> ['tr' => 'Dönüş', 'en' => 'Return'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.passengers',
                'values'=> ['tr' => 'Yolcular', 'en' => 'Passengers'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.fields.villa',
                'values'=> ['tr' => 'Villa', 'en' => 'Villa'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.prepayment',
                'values'=> ['tr' => 'Ön ödeme', 'en' => 'Prepayment'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.total_fee',
                'values'=> ['tr' => 'Toplam Ücret', 'en' => 'Total Amount'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.remaining',
                'values'=> ['tr' => 'Kalan :amount', 'en' => 'Remaining: :amount'],
            ],

            [
                'group' => 'account',
                'key'   => 'bookings.fields.tour',
                'values'=> ['tr' => 'Tur', 'en' => 'Tour'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.fields.date',
                'values'=> ['tr' => 'Tarih', 'en' => 'Date'],
            ],

            // Discounts
            [
                'group' => 'account',
                'key'   => 'bookings.discounts.title',
                'values'=> ['tr' => 'Uygulanmış İndirimler', 'en' => 'Applied Discounts'],
            ],

            // Refunds
            [
                'group' => 'account',
                'key'   => 'bookings.refunds.title',
                'values'=> ['tr' => 'Geri Ödemeler', 'en' => 'Refunds'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.refunds.default_reason',
                'values'=> ['tr' => 'Geri ödeme', 'en' => 'Refund'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.refunds.bank_note',
                'values'=> ['tr' => 'Bankanıza bağlı olarak 1-7 iş günü içinde tutar kartınıza yansıyabilir.', 'en' => 'Depending on your bank, the amount may appear on your card within 1–7 business days.'],
            ],

            // Actions + aria
            [
                'group' => 'account',
                'key'   => 'bookings.actions.hide_details',
                'values'=> ['tr' => 'Detayları Gizle', 'en' => 'Hide Details'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.actions.support_existing',
                'values'=> ['tr' => 'Mevcut Destek Talebi', 'en' => 'Existing Support Ticket'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.actions.support_create',
                'values'=> ['tr' => 'Destek talebi oluştur', 'en' => 'Create support ticket'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.aria.go_to_ticket',
                'values'=> ['tr' => 'Mevcut destek talebine git', 'en' => 'Go to existing support ticket'],
            ],
            [
                'group' => 'account',
                'key'   => 'bookings.aria.create_ticket',
                'values'=> ['tr' => 'Bu sipariş için destek talebi oluştur', 'en' => 'Create a support ticket for this order'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.title',
                'values'=> ['tr' => 'Giriş / Misafir', 'en' => 'Login / Guest'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.heading',
                'values'=> ['tr' => 'Giriş Yap', 'en' => 'Sign In'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.no_account',
                'values'=> ['tr' => 'Hesabın yok mu? Hemen', 'en' => "Don't have an account?"],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.register_link',
                'values'=> ['tr' => 'Kayıt Ol', 'en' => 'Register'],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.email.placeholder',
                'values'=> ['tr' => 'ornek@mail.com', 'en' => 'example@mail.com'],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.password.label',
                'values'=> ['tr' => 'Şifre', 'en' => 'Password'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.password.placeholder',
                'values'=> ['tr' => '••••••••', 'en' => '••••••••'],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.remember_me',
                'values'=> ['tr' => 'Beni hatırla', 'en' => 'Remember me'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.forgot_password',
                'values'=> ['tr' => 'Şifremi unuttum', 'en' => 'Forgot password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.actions.continue',
                'values'=> ['tr' => 'Devam Et', 'en' => 'Continue'],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.or',
                'values'=> ['tr' => 'veya', 'en' => 'or'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.social.google',
                'values'=> ['tr' => 'Google ile devam et', 'en' => 'Continue with Google'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.social.facebook',
                'values'=> ['tr' => 'Facebook ile devam et', 'en' => 'Continue with Facebook'],
            ],

            // Guest fields
            [
                'group' => 'auth',
                'key'   => 'login.guest.first_name.label',
                'values'=> ['tr' => 'Ad', 'en' => 'First name'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.last_name.label',
                'values'=> ['tr' => 'Soyad', 'en' => 'Last name'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.phone.label',
                'values'=> ['tr' => 'Telefon', 'en' => 'Phone'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.phone.placeholder',
                'values'=> ['tr' => '+90 5xx xxx xx xx', 'en' => '+90 5xx xxx xx xx'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.toggle',
                'values'=> ['tr' => 'Üye olmadan devam et', 'en' => 'Continue as guest'],
            ],
            [
                'group' => 'auth',
                'key'   => 'login.guest.note',
                'values'=> [
                    'tr' => 'Hesap oluşturmak zorunda değilsiniz. Bilgileriniz sadece bu rezervasyon için kullanılacaktır.',
                    'en' => 'You do not have to create an account. Your information will be used only for this booking.',
                ],
            ],

            [
                'group' => 'auth',
                'key'   => 'login.footer_notice',
                'values'=> [
                    'tr' => 'Devam ederek KVKK ve ön bilgilendirme metinlerini okuduğunuzu onaylamış olursunuz.',
                    'en' => 'By continuing, you confirm that you have read the privacy and pre-information texts.',
                ],
            ],
            [
                'group' => 'auth',
                'key'   => 'register.title',
                'values'=> ['tr' => 'Kayıt Ol', 'en' => 'Register'],
            ],
            [
                'group' => 'auth',
                'key'   => 'register.heading',
                'values'=> ['tr' => 'Kayıt Ol', 'en' => 'Create Account'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.first_name.label',
                'values'=> ['tr' => 'Ad', 'en' => 'First Name'],
            ],
            [
                'group' => 'auth',
                'key'   => 'register.last_name.label',
                'values'=> ['tr' => 'Soyad', 'en' => 'Last Name'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.phone.label',
                'values'=> ['tr' => 'Telefon (opsiyonel)', 'en' => 'Phone (optional)'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.password.label',
                'values'=> ['tr' => 'Şifre', 'en' => 'Password'],
            ],
            [
                'group' => 'auth',
                'key'   => 'register.password_confirm.label',
                'values'=> ['tr' => 'Şifre (Tekrar)', 'en' => 'Confirm Password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.actions.submit',
                'values'=> ['tr' => 'Kayıt Ol', 'en' => 'Register'],
            ],

            [
                'group' => 'auth',
                'key'   => 'register.have_account',
                'values'=> [
                    'tr' => 'Zaten hesabın var mı? Giriş yap',
                    'en' => 'Already have an account? Sign in',
                ],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_forgot.title',
                'values'=> ['tr' => 'Şifremi Unuttum', 'en' => 'Forgot Password'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_forgot.heading',
                'values'=> ['tr' => 'Şifremi Unuttum', 'en' => 'Forgot Password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_forgot.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_forgot.actions.send_link',
                'values'=> ['tr' => 'Sıfırlama bağlantısı gönder', 'en' => 'Send reset link'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_forgot.actions.back',
                'values'=> ['tr' => 'Geri dön', 'en' => 'Back'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_forgot.retry_suffix',
                'values'=> ['tr' => 'saniye sonra tekrar deneyebilirsin.', 'en' => 'seconds until you can try again.'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_reset.title',
                'values'=> ['tr' => 'Şifre Yenile', 'en' => 'Reset Password'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_reset.heading',
                'values'=> ['tr' => 'Şifre Yenile', 'en' => 'Reset Password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_reset.email.label',
                'values'=> ['tr' => 'E-posta', 'en' => 'Email'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_reset.password.label',
                'values'=> ['tr' => 'Yeni Şifre', 'en' => 'New Password'],
            ],
            [
                'group' => 'auth',
                'key'   => 'password_reset.password_confirm.label',
                'values'=> ['tr' => 'Yeni Şifre (Tekrar)', 'en' => 'Confirm New Password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_reset.actions.update',
                'values'=> ['tr' => 'Şifreyi Güncelle', 'en' => 'Update Password'],
            ],

            [
                'group' => 'auth',
                'key'   => 'password_reset.retry_suffix',
                'values'=> ['tr' => 'saniye sonra tekrar deneyebilirsin.', 'en' => 'seconds until you can try again.'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.title',
                'values'=> ['tr' => 'E-posta Doğrulaması', 'en' => 'Email Verification'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.heading',
                'values'=> ['tr' => 'E-posta Doğrulaması Gerekli', 'en' => 'Email Verification Required'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.link_sent',
                'values'=> ['tr' => 'Doğrulama bağlantısı e-postana gönderildi.', 'en' => 'A verification link has been sent to your email.'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.instructions',
                'values'=> [
                    'tr' => 'Hesabını tamamlamak için e-postana gelen doğrulama bağlantısına tıkla.',
                    'en' => 'To complete your account, click the verification link sent to your email.',
                ],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.actions.resend',
                'values'=> ['tr' => 'Bağlantıyı Tekrar Gönder', 'en' => 'Resend Link'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.actions.logout',
                'values'=> ['tr' => 'Çıkış yap', 'en' => 'Log out'],
            ],
            [
                'group' => 'auth',
                'key'   => 'email_verify.retry_suffix',
                'values'=> ['tr' => 'saniye sonra tekrar deneyebilirsin.', 'en' => 'seconds until you can try again.'],
            ],
            [
                'group' => 'ui',
                'key'   => 'currency_switch_modal_title',
                'values'=> ['tr' => 'Para birimi değişimini şimdi yaparsanız, sepetinizdeki ürünler silinecek.', 'en' => 'If you change the currency now, the items in your cart will be deleted.'],
            ],
            [
                'group' => 'ui',
                'key'   => 'currency_switch_modal_text',
                'values'=> ['tr' => 'Para birimini değiştirmek istediğinizden emin misiniz?', 'en' => 'Are you sure you want to change the currency?'],
            ],
            [
                'group' => 'ui',
                'key'   => 'currency_switch_modal_cancel',
                'values'=> ['tr' => 'İptal', 'en' => 'Cancel'],
            ],
            [
                'group' => 'ui',
                'key'   => 'currency_switch_modal_confirm',
                'values'=> ['tr' => 'Para Birimini Değiştir', 'en' => 'Change Currency'],
            ],
        ];

        foreach ($translations as $data) {
            Translation::updateOrCreate(
                [
                    'group' => $data['group'],
                    'key'   => $data['key'],
                ],
                [
                    'values' => $data['values'],
                ]
            );
        }
    }
}
