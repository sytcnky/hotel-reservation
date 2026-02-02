<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class MsgTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = [
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.attachments.errors.invalid_generic',
                'values' => ['tr' => 'Dosya yüklenemedi. Lütfen farklı bir dosya deneyin.', 'en' => 'The file could not be uploaded. Please try a different file.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.attachments.errors.too_large',
                'values' => ['tr' => 'Dosya boyutu çok büyük. Lütfen 2MB\'dan küçük bir dosya yükleyin.', 'en' => 'The file size is too large. Please upload a file smaller than 2MB.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.attachments.errors.type_unsupported',
                'values' => ['tr' => 'Dosya türü desteklenmiyor. Lütfen .jpg, .jpeg, .png veya .webp yükleyin.', 'en' => 'This file type is not supported. Please upload a .jpg, .jpeg, .png, or .webp file.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.auth.login_required',
                'values' => ['tr' => 'Giriş yapılması gerekli', 'en' => 'Login required'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.cart.currency_mismatch',
                'values' => ['tr' => 'Para birimi uyuşmuyor.', 'en' => 'Currency mismatch.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.cart.empty',
                'values' => ['tr' => 'Sepetinizde ürün yok.', 'en' => 'Your cart is empty.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.cart.no_amount',
                'values' => ['tr' => 'Sepette geçerli bir ödeme tutarı bulunamadı.', 'en' => 'No valid payment amount was found in the cart.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.coupon.exclusive_conflict',
                'values' => ['tr' => 'Bu kupon başka bir kuponla birlikte kullanılamaz.', 'en' => 'This coupon cannot be combined with other coupons.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.coupon.not_applicable',
                'values' => ['tr' => 'Bu kupon şu an sepet için kullanılamıyor.', 'en' => 'This coupon cannot be applied to the current cart.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.hotel.currency_missing',
                'values' => ['tr' => 'Para birimi bulunamadı (otel).', 'en' => 'Currency not found (hotel).'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.hotel.dates_invalid',
                'values' => ['tr' => 'Geçersiz tarih', 'en' => 'Invalid dates'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.hotel.not_available',
                'values' => ['tr' => 'Seçilen tarihlerde müsait değil.', 'en' => 'Not available on the selected dates.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.hotel.not_found',
                'values' => ['tr' => 'Otel bulunamadı', 'en' => 'Hotel not found'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_failed',
                'values' => ['tr' => '3D ödeme başarısız oldu.', 'en' => '3D payment failed.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_invalid_attempt',
                'values' => ['tr' => 'Geçersiz 3d ödeme denemesi.', 'en' => 'Invalid 3D payment attempt.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_invalid_token',
                'values' => ['tr' => 'Geçersiz token.', 'en' => 'Invalid token.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_missing_params',
                'values' => ['tr' => 'Ödeme doğrulaması için gerekli bilgiler eksik. Lütfen ödeme işlemini tekrar başlatın.', 'en' => 'The required information for payment verification is incomplete. Please restart the payment process.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_not_supported',
                'values' => ['tr' => '3D ödeme desteklenmiyor.', 'en' => '3D payment not supported.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.finalize_failed',
                'values' => ['tr' => 'Ödeme işlemi tamanlanmadı.', 'en' => 'Payment not completed.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.3ds_start_failed',
                'values' => ['tr' => '3D başlatılamadı.', 'en' => '3D not start.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.payment.session_expired',
                'values' => ['tr' => 'Ödeme oturumu zaman aşımına uğradı. Lütfen tekrar deneyin.', 'en' => 'Payment session has expired. Please try again.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.tour.currency_missing',
                'values' => ['tr' => 'Para birimi bulunamadı (tur).', 'en' => 'Currency not found (tour).'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.tour.not_found',
                'values' => ['tr' => 'Tur bulunamadı', 'en' => 'Tour not found.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.transfer.currency_missing',
                'values' => ['tr' => 'Para birimi bulunamadı (transfer).', 'en' => 'Currency not found (transfer).'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.transfer.pickup_pair_required',
                'values' => ['tr' => 'Seçili alanda bilgi girmelisiniz. (Saat veya Uçuş Numarası)', 'en' => 'You must enter a value for the selected field. (Time or Flight Number)'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.transfer.route_not_found',
                'values' => ['tr' => 'Rota bulunamadı.', 'en' => 'Route not found.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.transfer.vehicle_not_found',
                'values' => ['tr' => 'Araç bulunamadı.', 'en' => 'Vehicle not found.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.villa.currency_missing',
                'values' => ['tr' => 'Para birimi bulunamadı (villa).', 'en' => 'Currency not found (villa).'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.villa.dates_invalid',
                'values' => ['tr' => 'Tarihler geçersiz.', 'en' => 'Invalid dates'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.villa.max_nights',
                'values' => ['tr' => 'Bu villa maksimum {count} gece rezerve edilebilir.', 'en' => 'This villa can be booked for a maximum of {count} nights.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.villa.min_nights',
                'values' => ['tr' => 'En az {count} gece seçmelisiniz.', 'en' => 'You must select at least {count} nights.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.villa.not_found',
                'values' => ['tr' => 'Villa bulunmadı.', 'en' => 'Villa not found.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'info.not_available',
                'values' => ['tr' => 'Seçilen tarihlerde müsait değil.', 'en' => 'Not available on the selected dates.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'info.price_not_found',
                'values' => ['tr' => 'Fiyat bulunamadı.', 'en' => 'Price not Found.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'ok.account.support_tickets.created',
                'values' => ['tr' => 'Destek talebi oluşturuldu', 'en' => 'Support ticket created.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'ok.account.support_tickets.message_sent',
                'values' => ['tr' => 'Destek talebi gönderildi.', 'en' => 'Support ticket send.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.order_already_has_ticket',
                'values' => ['tr' => 'Bu sipariş için zaten bir destek talebi bulunmaktadır.', 'en' => 'A support ticket already exists for this order.',],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.order_required',
                'values' => ['tr' => 'Bu konu tipi için sipariş seçimi zorunludur.', 'en' => 'Selecting an order is required for this category.',],
            ],
            [
                'group'  => 'msg',
                'key'    => 'err.account.tickets.attachments.errors.too_many',
                'values' => ['tr' => 'En fazla 5 dosya ekleyebilirsiniz.', 'en' => 'You can attach up to 5 files.',],
            ],
            [
                'group'  => 'msg',
                'key'    => 'ok.cart.item_added',
                'values' => ['tr' => 'Ürün sepete eklendi.', 'en' => 'Product added to cart.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'warn.price_cannot_calculated',
                'values' => ['tr' => 'Fiyat şu an hesaplanamadı.', 'en' => 'The price cannot be calculated at this time.'],
            ],
            [
                'group'  => 'msg',
                'key'    => 'warn.villa_price_missing_for_currency',
                'values' => ['tr' => 'Mevcut para birimi için fiyat bulunmuyor.', 'en' => 'Price missing for currency.'],
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
