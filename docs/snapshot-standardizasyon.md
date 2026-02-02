# Snapshot Standardizasyon Dokümanı (Ürün Bazlı)

Bu doküman, sistemde kullanılan **snapshot** yapılarının alan bazında **kaynağını**, **yetki durumunu** ve **kuralını** tanımlar.  
Her alan için üç eksen sabittir:

- **source**
    - `request-accepted`: Request whitelist’inden gelir
    - `server-derived`: Server tarafından üretilir / overwrite edilir
    - `derived`: Server hesaplar
- **rule**
    - `prohibited`: Client tarafından gönderilmesi yasak / güvenilmez
    - `nullable`: Snapshot’ta bulunabilir veya boş olabilir

---

## 1) HOTEL (`product_type = hotel_room`)

**Snapshot üretimi**
- Request: `HotelBookingRequest::validated()`
- Server overlay: `HotelPriceQuoteService::quote(...)->snapshot`
- Birleşim: request snapshot + server overlay (server ana otorite)

| field | source | rule |
|---|---|---|
| hotel_id | request-accepted | prohibited |
| room_id | server-derived | prohibited |
| checkin | server-derived | prohibited |
| checkout | server-derived | prohibited |
| nights | derived | prohibited |
| adults | request-accepted | nullable |
| children | request-accepted | nullable |
| board_type_id | request-accepted | nullable |
| currency | server-derived | prohibited |
| price_total | derived | prohibited |
| hotel_name | server-derived | nullable |
| room_name | server-derived | nullable |
| board_type_name | server-derived | nullable |
| location_label | server-derived | nullable |
| hotel_image | server-derived | nullable |

**Kurallar**
- Display/name/label alanları request’te prohibited; yalnız server-derived olarak taşınır.
- `hotel_image` yalnız `cover_image.exists === true` ise snapshot’a eklenir.

---

## 2) VILLA (`product_type = villa`)

**Snapshot üretimi**
- Request: `VillaBookingRequest::validated()`
- Server overlay: `VillaPriceQuoteService::quote(...)->snapshot`
- Birleşim: request snapshot + server overlay (server ana otorite)

| field | source | rule |
|---|---|---|
| villa_id | server-derived | prohibited |
| checkin | server-derived | prohibited |
| checkout | server-derived | prohibited |
| nights | derived | prohibited |
| adults | request-accepted | nullable |
| children | request-accepted | nullable |
| currency | server-derived | prohibited |
| price_nightly | derived | prohibited |
| price_total | derived | prohibited |
| price_prepayment | derived | prohibited |
| villa_name | server-derived | nullable |
| location_label | server-derived | nullable |
| villa_image | server-derived | nullable |

**Kurallar**
- Display/name/label alanları request’te prohibited; yalnız server-derived olarak taşınır.
- `villa_image` yalnız `cover_image.exists === true` ise snapshot’a eklenir.
- Tahsil edilecek tutar (cart item `amount`) = `price_prepayment`.

---

## 3) TOUR (`product_type = tour`)

**Snapshot üretimi**
- Request: `TourBookingRequest::validated()`
- Server overlay: `TourPriceQuoteService::quote(...)->snapshot`
- Birleşim: request snapshot + server overlay (server ana otorite)

| field | source | rule |
|---|---|---|
| tour_id | server-derived | prohibited |
| date | server-derived | prohibited |
| adults | request-accepted | nullable |
| children | request-accepted | nullable |
| infants | request-accepted | nullable |
| currency | server-derived | prohibited |
| price_total | derived | prohibited |
| tour_name | server-derived | nullable |
| category_name | server-derived | nullable |
| cover_image | server-derived | nullable |

**Kurallar**
- Display/name/label alanları request’te prohibited; yalnız server-derived olarak taşınır.
- `cover_image` yalnız `cover_image.exists === true` ise snapshot’a eklenir.

---

## 4) TRANSFER (`product_type = transfer`)

**Snapshot üretimi**
- Request: `TransferBookingRequest::validated()`
- Server overlay: `TransferPriceQuoteService::quote(...)->snapshot`
- Birleşim: request snapshot + server overlay (server ana otorite)

| field | source | rule |
|---|---|---|
| route_id | server-derived | prohibited |
| vehicle_id | server-derived | prohibited |
| direction | server-derived | prohibited |
| from_location_id | request-accepted | nullable |
| to_location_id | request-accepted | nullable |
| departure_date | request-accepted | nullable |
| return_date | request-accepted | nullable |
| pickup_time_outbound | request-accepted | nullable |
| flight_number_outbound | request-accepted | nullable |
| pickup_time_return | request-accepted | nullable |
| flight_number_return | request-accepted | nullable |
| adults | request-accepted | nullable |
| children | request-accepted | nullable |
| infants | request-accepted | nullable |
| currency | server-derived | prohibited |
| price_total | derived | prohibited |
| from_label | server-derived | nullable |
| to_label | server-derived | nullable |
| vehicle_name | server-derived | nullable |
| vehicle_cover | server-derived | nullable |

**Kurallar**
- Client-sourced display/name/label alanları request’te prohibited; yalnız server-derived olarak taşınır.
- `vehicle_cover` yalnız `cover_image.exists === true` ise snapshot’a eklenir.
- `return_date`, `pickup_time_return`, `flight_number_return` alanları yalnız `direction = roundtrip` iken geçerlidir.

---

## Genel İlkeler (Kilitli)

- Snapshot alanları **server-authoritative**’tir.
- Client’tan gelen fiyat, para birimi, label/name alanları **kabul edilmez**.
- Snapshot, sipariş anındaki verinin **donmuş** halidir; sonradan türetilmez.
