1️⃣ Mevcut Sistem Durumu — Tespit (Netleşti)
Global Ayarlar

config/app.php

timezone = UTC ✅ (doğru, korunacak)

FilamentTimezone::set('Europe/Istanbul') ✅

Admin panel her zaman TR saati gösteriyor

Operasyon varsayımı: Türkiye (ileride değişebilir)

2️⃣ Tarih Kullanan Ürünler — Envanter

Şu an tüm ürünlerde civil date kullanılıyor, fakat format & parse standardı yok.

🏨 Otel

Dosyalar

Hotel detail controller (show)

Hotel listing (sen az önce hatırlattın → eklendi)

Kullanım

Tek input, range string:

"18.11.2025 - 22.11.2025"

"18.11.2025" (fallback)

Parse:

Controller içinde özel parseDateRange() fonksiyonu

İçerik:

checkin, checkout, nights

Problem:

Format string’e bağlı

Otel’e özel çözüm, reusable değil

🚗 Transfer

Dosyalar

TransferController@index

resources/js/pages/transfer.js

Kullanım

2 ayrı input:

departure_date

return_date

Format:

d.m.Y

Controller:

Tarihler string olarak taşınıyor

Henüz Carbon parse / normalize yok

Problem:

Otelden tamamen farklı yapı

Tarih domain logic’e girmeden snapshot’a gidiyor

🏡 Villa

(Henüz dosya verilmedi ama senin dediğin doğru varsayım şu)

Tek tarih veya range kullanımı var

Büyük ihtimalle:

d.m.Y

Controller içinde lokal parse veya hiç parse yok

➡️ Envantere dahil

🚌 Tur

Tarih seçimi var (tek gün)

Format / parse büyük ihtimalle villa & transfer’e benzer
➡️ Envantere dahil

3️⃣ Sorunun Kök Nedeni (Özet)

Şu an sistemde:

❌ Ürün bazlı farklı parse fonksiyonları

❌ Tarih formatı FE’ye bağımlı

❌ Controller’larda “string parse” domain logic’e karışmış

❌ Civil date ↔ instant ayrımı kodda net değil

Ama:

✅ Timezone kararı doğru

✅ DB ve Filament tarafı doğru yerde

Yani temel sağlam, standart eksik.

4️⃣ Sprint’in Gerçek Amacı (Net Tanım)

Bu sprintin amacı:

“Tüm ürünlerde tarih bilgisini aynı sözleşmeyle almak, parse etmek, taşımak ve göstermek.”

Sprint bu soruları çözecek:

Sunucuya hangi format gelir?

Range / single date tek mi ayrı mı?

Parse nerede ve kimde yapılır?

Controller’lar tarihle ne kadar ilgilenir?

Civil date ile timestamp kesin ayrıldı mı?


VillaController envantere eklendi. Bu dosyada tarih/timezone açısından kritik nokta: hiç tarih kullanılmıyor (ne filtre, ne availability, ne rezervasyon bağlamı). Bu iyi; sprint kapsamını daraltıyor.

VillaController — Tarih/Timezone Envanter Kaydı
1) Villa listeleme (index)

Tarih alanı yok

Tarih filtresi yok

Civil date / range / flatpickr ile bağlantı yok

Sadece fiyat/currency ve içerik render

2) Villa detay (show)

Tarih alanı yok

JS tarafında (sen daha önce paylaşmıştın) villa-details.js’de checkin flatpickr vardı; ama controller burada onu okumuyor.

Yani villa detay sayfasındaki tarih seçimi şu an muhtemelen yalnızca frontend hesaplama/UX veya başka bir endpoint’e bağlı.

Burada sadece:

fiyat kuralı seçimi (currency)

min/max nights verisini view’a veriyor

Sonuç: VillaController sprintin “tarih parse standardizasyonu” bölümünde doğrudan refactor hedefi değil.

Sprint listesine etkisi

Bu dosyaya göre sprint kapsamı şu şekilde güncellenir:

“Tarih kullanan BE akışları” (refactor hedefi olacaklar)

Otel detail (range parse var)

Otel listing (tarih filtresi var dedin → bekleniyor)

Transfer index (departure/return var, parse yok)

Tur (bekleniyor)

(Villa için) tarih seçimi varsa, controller değil başka yerde: muhtemelen rezervasyon/checkout/cart tarafında veya ayrı bir endpoint (bunu ayrıca bulacağız)

“Tarih kullanan FE bileşenleri”

Villa-details.js (checkin)

Hotel-details.js (range)

Transfer form (departure/return)

Tur (muhtemelen single date)

Villa için açık “envanter sorusu” (kod yazmadan)

Villa detayda kullanıcı tarih seçiyor ama controller okumuyor. Bu nedenle envanter için şunu işaretliyorum:

Villa tarih seçimi BE’de nerede işleniyor?

seçenekler: Cart/Checkout controller, ayrı bir VillaReservationController, veya JS sadece fiyat hesaplıyor.

Bunu bulmak için bir sonraki dosyada tur controller veya otel listing ile devam edebiliriz; ardından villa tarihinin BE’ye hangi noktadan girdiğini bulmak için senin vereceğin dosyalardan iz süreriz.


TransferController’ı envantere “tarih” açısından net şekilde kaydettim. Bu dosya, sprintin neden gerekli olduğunu çok iyi gösteriyor: tarih var ama civil date standardı + parse/normalize katmanı yok.

TransferController — Tarih/Timezone Envanter Kaydı
Tarih alanları

departure_date (zorunlu)

return_date (roundtrip ise zorunlu)

Kaynak / format

Kaynak: request input ($request->input(...))

Format: FE flatpickr’da dateFormat: "d.m.Y" (sen paylaştın)

Controller tarafında parse yok:

Carbon’a çevrilmiyor

Y-m-d normalize edilmiyor

“departure <= return” gibi tarih mantığı kontrolü yok (sadece return’ın dolu olması kontrol ediliyor)

Taşınma biçimi

Tarihler transferOffer içine string olarak aynen konuluyor:

'departure_date' => $departureDate

'return_date' => $returnDate

Bu, daha sonra cart/checkout/snapshot tarafına bu şekilde taşınabilir (dosyayı görmedik ama risk burada).

Sprint için çıkarım

Transfer tarafı sprintte iki başlık altında ele alınmalı:

Civil date normalizasyonu

FE hangi formatı üretirse üretsin, BE tarafında tek bir standarda dönüştürülmeli (hedef: Y-m-d)

“roundtrip” ise return >= departure kontrolü (civil date compare)

Tek parse otoritesi

Oteldeki gibi controller içi özel fonksiyon yerine, tüm ürünlerin kullanacağı ortak “DateInput / DateParser” katmanı

TourController — Tarih/Timezone Envanter Kaydı
1) Tur listeleme (index)

Tarih alanı yok

Kullanıcıdan tarih input’u alınmıyor

Flatpickr / civil date / range yok

2) Tur detay (show)

Zaman alanları (time-of-day)

start_time:

Controller: $tour->start_time ? $tour->start_time->format('H:i') : null

Bu, “operasyon TR” varsayımında mantıklı.

Ancak bu alanın DB tipi ve timezone anlamı sprintte netleşmeli:

time mi (sadece saat)

yoksa datetime/timestamp mı (an)

Kod format('H:i') dediği için “sadece saat” (civil time) gibi kullanılıyor.

Gün alanları

days_of_week raw array

3) Sprint için çıkarım

Tur tarafı sprintte civil date standardizasyonu kısmında değil; ama “timezone/tarih formatı” sprintine şu iki maddeden dahil edilmeli:

Civil time standardı (start_time)

Bu alan “an” değil, “günün saati” ise:

saklama türü/timezone bağı netleşmeli

gösterim formatı standardı: HH:mm (şu an böyle)

Eğer ileride kullanıcı timezone’una göre “sizin saatiniz” göstermek istenirse, bu ayrı bir UX katmanı olacak.

Gün isimleri standardı

days_of_week değerleri: TR/EN gösterim vs canonical değerler (mon,tue… gibi) ayrı bir sözleşme konusu olabilir.

Şimdilik sprint notu olarak: “date/time sprinti kapsamına dahil edilecek mi?” diye işaretleyebiliriz.

Envanter tablosu güncelleme (özet)
Civil date input kullanan BE (refactor hedefi)

Hotel detail (range parse var)

Transfer index (date var, parse yok)

Hotel listing (tarih filtresi var → bekleniyor)

Villa date picker BE entrypoint (controller’da yok → bulunacak)

Time-of-day kullanan BE

Tour detail: start_time → HH:mm format

Kritik eksik parça: Otel listing

Senin sprint hedefin için şu an en kritik kalan dosya otel listeleme (tarih filtresi olan). Çünkü:

Listing sayfası “filtre standardı” demek

Hotel detail’deki range parse ile aynı formatı mı kullanıyor, yoksa farklı mı, bunu görmeden “tek standart” kararı kilitlenmez.

HotelController envanter açısından kritik iki şeyi netleştirdi:

Otel detail tarafında tarih standardı “controller içi parse” ile gidiyor (parseDateRange).

Otel listing tarafı “tek kaynak” olarak HotelListingPageService’e devredilmiş → tarih filtresi asıl orada.

Bu, sprintin en önemli refactor hedefini de işaret ediyor: tüm tarih parse/normalize işini controller/service bazında dağınık olmaktan çıkarıp tek standarda toplamak.

HotelController — Tarih/Timezone Envanter Kaydı
A) Otel detay (show)

Girdi

Query param: checkin

Format bekleniyor:

"18.11.2025 - 22.11.2025" veya "18.11.2025"

Parse:

Controller içindeki parseDateRange() ile

Çıktı: Carbon startOfDay + nights

Kullanım

context['checkin'] / context['checkout'] Carbon

resolveRoomPricing() içinde resolver’a giderken tekrar Y-m-d formatına çevriliyor:

$checkin->format('Y-m-d')

$rangeEnd->format('Y-m-d')

Sprint çıkarımı

Civil date + range parse mevcut ama sadece otelde var ve controller’a gömülü.

Çıktının son kullanımı zaten Y-m-d → bu iyi; standarda yakınız.

B) Otel listeleme (index)

Controller sadece:

$service->build($request) çağırıyor

Tarih filtresi (senin dediğin) ve parse/format işleri HotelListingPageService içinde.

✅ Bu, envanterde “tek otorite” olabilecek ideal yerlerden biri (service layer), ama şu an muhtemelen otel özelinde.


Sprint için yeni net hedef maddeleri (otel üzerinden somutlaştı)

HotelController@show parseDateRange: “otel özel” olmaktan çıkarılacak (tek parse standardına bağlanacak).

HotelListingPageService: listing filtreleri içindeki tarih parse/normalize yaklaşımı, sprintin kanonik referansı olacak (görmeden karar kilitlenmez).

TransferController@index: date stringler normalize edilmeden taşınıyor → standarda çekilecek.

HotelListingPageService, sprintin “tarih standardı” kısmı için kanonik dosya oldu. Burada tarih filtresi aktif şekilde query’yi daraltıyor ve ayrıca aynı parse mantığı HotelController@show ile kopya durumda. En önemli bulgu bu.

HotelListingPageService — Tarih/Timezone Envanter Kaydı
1) Tarih giriş alanı

Request param: checkin

Beklenen format:

"18.11.2025 - 22.11.2025" veya "18.11.2025"

Parse fonksiyonu:

parseCheckinRange() (otel listing’e özel)

Carbon::createFromFormat('d.m.Y') + startOfDay()

Tek tarih gelirse checkout = checkin + 1 day

✅ Bu, otel detail’deki parseDateRange() ile aynı sözleşme.

2) Tarihin query’ye etkisi (kritik)

Listing’de tarih seçimi:

“fiyatı değiştirmez” (not düşmüşsün)

sonuç setini daraltır

Uygulama şekli:

$rangeStart = $checkin->toDateString()

$rangeEnd = (clone $checkout)->subDay()->toDateString()

sonra applyDateOverlap($q, $rangeStart, $rangeEnd)

Bu şu anlama geliyor:

UI’da checkout seçilse bile, filtre “gecelenecek günler” mantığıyla checkout-1 üzerinden çalışıyor (doğru).

3) Tarih overlap kuralı (mevcut standart)

applyDateOverlap() şu kuralı uyguluyor:

date_start ve date_end ikisi de null ise her zaman geçerli

aksi halde overlap:

date_start null veya <= rangeEnd

date_end null veya >= rangeStart

Bu, ileride tüm ürünlerde “availability” yaklaşımı için referans olabilir.

Sprint açısından çıkan net problemler
P-1) Kopya parse mantığı

HotelController@show: parseDateRange()

HotelListingPageService: parseCheckinRange()

İkisi aynı işi yapıyor → sprintte tek parse otoritesine indirilmeli.

P-2) Format bağımlılığı (d.m.Y)

Hem listing hem detail tamamen d.m.Y’ye bağlı.

Transfer de d.m.Y string taşıyor (parse yok).

Villa FE d.m.Y (senin eski snippet).

Bu, “standardizasyon sprinti”nin ana konusu.

P-3) Civil date ile timezone ilişkisi

Burada Carbon startOfDay() kullanılıyor → timezone’a bağlı davranır.

config/app.php UTC olduğu için, FE’nin TR tarihini UTC’de startOfDay’a çevirmek bazı edge-case’lerde gün kayması riskini teorik olarak doğurur (özellikle ileride timezone değişirse).

Bu yüzden sprintte “civil date parse”yi timezone’dan bağımsız bir sözleşme olarak tanımlamalıyız.

Bu pencere için sprint yapılacaklar listesi (dosya bazında)
1) Kontrat / Standart kararlar (doküman)

Civil date taşıma formatı (tek format)

Range temsil şekli (tek delimiter)

Tek parse otoritesi (hangi sınıf/katman)

Carbon yerine “immutable date” tercihi (karar maddesi)

UI gösterim formatı locale’e göre (TR: d.m.Y) ama BE formatı sabit (örn. Y-m-d)

2) Envanter refactor hedef dosyaları

Otel

HotelController@show → parseDateRange kaldırılacak / standard parse’a bağlanacak

HotelListingPageService → parseCheckinRange kaldırılacak / standard parse’a bağlanacak

View tarafında filters.checkin taşıma biçimi standarda uyacak

Transfer

TransferController@index → departure/return standard parse’a bağlanacak (string taşımak yerine normalize edilmiş “civil date” seti)

Transfer JS → valueFormat/altFormat standardı (bu daha sonra, ayrı pencere)

Villa

Villa tarih seçiminin BE’ye giriş noktası bulunacak (Cart/Checkout/Add-to-cart aksiyonu)

O noktada standard parse uygulanacak

Tur

start_time alanı civil time mı? (DB tipi/sözleşme) sprint notu olarak netleştirilecek

3) Ortak altyapı

Tek “DateInput/DateRange” helper katmanı (hangi namespace, nerede kullanılacak)

Validasyon: minimum 1 gece, checkout > checkin, roundtrip return >= departure


Villa-details.js envantere alındı; burada sprint için en kritik keşif var:

Villa’da kullanıcıya d.m.Y gösteriyorsun ama BE’ye hidden input ile YYYY-MM-DD gönderiyorsun (toISOString().slice(0,10)).

Otel ve transferde ise BE’ye hâlâ d.m.Y string gidiyor (otel checkin query, transfer departure_date/return_date).

Bu fark, sprintin ana hedefini netleştiriyor: tüm ürünlerde BE’ye taşınan tarih formatını tekleştirmek.

Villa-details.js — Tarih/Timezone Envanter Kaydı
UI (gösterim)

flatpickr:

mode: 'range'

dateFormat: 'd.m.Y'

minDate: 'today'

Min/max nights kontrolü FE’de yapılıyor.

BE’ye taşınan değer (asıl standart)

Hidden alanlara yazılan:

hidden-checkin: YYYY-MM-DD

hidden-checkout: YYYY-MM-DD

villa-nights: sayı

fiyatlar: numeric string (2 decimals)

✅ Bu, bizim daha önce konuştuğumuz hedefe birebir uyuyor:

UI locale formatlı gösterir

BE sabit format alır (Y-m-d)

Sprint çıkarımı (çok net)

Şu an projede “tarih standardı” ikiye bölünmüş:

Doğru yolda olan yaklaşım (Villa)

UI: d.m.Y

Payload: Y-m-d (hidden)

Standartsız yaklaşım (Otel + Transfer)

UI: d.m.Y

Payload: d.m.Y (query / input)

BE parse etmek zorunda kalıyor (otel parse var, transfer parse yok)

Envanter artık tamam: “Tarih nasıl taşınıyor?” matrisi
Ürün	UI	BE’ye taşınan	Parse nerede?
Villa	d.m.Y range	Y-m-d (hidden)	Parse yok (zaten normalize)
Otel detail	d.m.Y range	d.m.Y - d.m.Y (query)	BE controller parse
Otel listing	d.m.Y range	d.m.Y - d.m.Y (query)	BE service parse
Transfer	d.m.Y single	d.m.Y (input)	Parse yok (şu an risk)
Tur	tarih yok	—	—
Sprint yapılacaklar listesine eklenen somut maddeler
A) “Tek tarih taşıma standardı” kararı

Kanonik payload format: YYYY-MM-DD

Range payload: iki alan (checkin, checkout) veya tek alan (karar) ama format sabit.

Villa zaten böyle çalışıyor → bunu “referans standart” yapabiliriz.

B) Otel refactor hedefi

checkin query string’i (“18.11.2025 - 22.11.2025”) yerine:

ya hidden checkin=YYYY-MM-DD + checkout=YYYY-MM-DD

ya da single input ama value YYYY-MM-DD - YYYY-MM-DD (tercih etmeyiz)

Bu değişiklikle:

parseDateRange / parseCheckinRange gibi d.m.Y parse’ları ortadan kalkar veya sadece backward-compat için kalır.

C) Transfer refactor hedefi

departure_date / return_date payload’ı YYYY-MM-DD olacak (UI d.m.Y kalabilir)

TransferController artık string taşımak yerine normalize edilmiş date seti taşır.

Bu pencereyi “sprint planı” ile kapatmak için eksik tek veri

Villa’nın hidden input’ları BE’de kim okuyor? (cart/add-to-cart/checkout)

Envanterin son parçası için sıradaki dosya:

Villa’yı sepete ekleyen action/controller (hidden-checkin/hidden-checkout alanlarını alan endpoint)

Tur notu (tarih olmalı)

Envanter açısından şu an net olan: TourController’da tarih seçimi yok, ama CheckoutController@bookTour var ve TourBookingRequest ile payload alıyor. Yani tur tarihinin “nerede” olduğuna dair aradığımız yer TourBookingRequest + tur detay blade/js formu. Şu an elimizde olmadığından sadece “bulunacak” diye listeye ekleyebiliyoruz.

Villa tarafı: Tarih standardı zaten doğru uygulanmış (envanter kaydı)
1) VillaBookingRequest sözleşmesi

checkin, checkout alanları date rule ile doğrulanıyor.

passedValidation() içinde:

checkin ve checkout → normalizeDateToYmd()

currency → normalize

Bu, sprint standardı için “referans implementasyon”.

2) Villa FE → BE akışı

FE (flatpickr) kullanıcıya d.m.Y gösteriyor ama hidden’a YYYY-MM-DD yazıyor.

BE request de passedValidation() ile tekrar normalize ediyor.
Sonuç: çift katmanlı güvenli standart.

✅ Bu artık “ICR Date Input Standard” için kanonik örnek.

CheckoutController: ürünler arası tarih standardı farkı netleşti
Villa

bookVilla(VillaBookingRequest ...)

checkin/checkout zaten YYYY-MM-DD ve normalize ediliyor.

Sepete snapshot’a bu şekilde giriyor.

Hotel / Transfer / Tour

Hepsi FormRequest kullanıyor:

HotelBookingRequest

TransferBookingRequest

TourBookingRequest

Ama içeriklerini görmeden şunu söyleyebiliriz:

Villa’da açıkça “Y-m-d standardı + normalize” var.

Diğer üçünde bu garanti şu an belirsiz.

Sprint envanteri açısından yapılacak iş: bu üç request’i açıp villa ile aynı standarda çekmek.

Sprint checklist’ine eklenen dosyalar (kesin)
“Date Standardizasyon Sprinti” hedef dosyaları

App\Http\Requests\VillaBookingRequest ✅ (referans)

App\Http\Requests\HotelBookingRequest (incelenecek)

App\Http\Requests\TransferBookingRequest (incelenecek)

App\Http\Requests\TourBookingRequest (incelenecek) ← tur tarihini burada göreceğiz

HotelController@show (parseDateRange) (refactor hedefi)

HotelListingPageService (parseCheckinRange) (refactor hedefi)

Transfer sayfasının controller + blade/js form payload’ı (date format uyumu)

Tur detay sayfasının blade/js booking formu (tarihi nereden alıyor?)

Tur tarafı netleşti: tarih alanı var ve standart doğru kurgulanmış.

Envanter kaydı — TourBookingRequest

Alan adı: date

Validation: required|date

Normalize: passedValidation() içinde date => normalizeDateToYmd()

Yani BE tarafında tur tarihi de kanonik YYYY-MM-DD kabul ediyor.

Bu şu anlama geliyor:

1) “Turda tarih yok” problemi teknik değil, UI/payload problemidir

Tur detay sayfasındaki form ya date göndermiyor,

ya date alanını d.m.Y gönderiyor ve normalize etmesi bekleniyor (normalize ediyor ama date rule’un parse edip etmediği tarayıcı/string formatına göre riskli),

ya da date input adı farklı.

2) Sprint için net hedef

Tur tarafında yapılacak iş: UI’da date seçimini standartlaştırıp BE’ye güvenli formatta taşımak. (Villa yaklaşımı)

Sprint checklist güncellemesi (tur)

✅ App\Http\Requests\TourBookingRequest (zaten standart)

⏳ Tur detay booking formu (Blade): name="date" var mı, hangi değer gidiyor?

⏳ Tur detay JS/flatpickr varsa: UI d.m.Y olabilir ama payload hidden YYYY-MM-DD olmalı (villa standardı)

Envanterde “date standardı” durumu (güncel)

Villa: ✅ UI d.m.Y / payload Y-m-d / request normalize

Tour: ✅ request normalize var; UI/payload belirsiz

Hotel: ❌ payload d.m.Y range; controller/service parse ediyor

Transfer: ❌ payload d.m.Y single; controller parse yok

Tur UI tarafını kesinlemek için sıradaki dosya:

Tur detay sayfasındaki form (checkout’a POST eden bölüm) ve varsa flatpickr init dosyası.

Tur UI tarafı da netleşti; şu an standart dışı bir nokta var.

Envanter — excursion-details.js (tur detay)
Tarih

Input: #excursion-date

flatpickr: dateFormat: "d.m.Y"

JS tarafında Y-m-d hidden üretimi yok (villa’daki gibi toISOString().slice(0,10) yazmıyor)

Dolayısıyla form büyük ihtimalle date alanını d.m.Y olarak POST ediyor.

Risk / Bug ihtimali

TourBookingRequest içinde date için:

önce rules(): required|date çalışıyor,

sonra passedValidation() normalize ediyor.

Laravel’in date validator’ı d.m.Y string’i her ortamda güvenle parse etmeyebilir (genelde ISO/Y-m-d daha güvenli). Bu yüzden tur tarafında “tarih var ama bazen validasyon patlıyor” tipi sorunlar çıkabilir.

Sprint hedefi (tur için) kesinleşti

Tur booking formunda:

Kullanıcıya yine d.m.Y gösterebilirsin,

ama POST edilen alan date mutlaka YYYY-MM-DD olmalı.

Villa standardını birebir uygularız:

Görünen input d.m.Y

Hidden input date (veya mevcut date alanı) Y-m-d

Checklist’e ek (tur)

resources/js/pages/excursion-details.js ✅ (envanter alındı)

Tur detay blade formu:

excursion-date input’un name’i ne? (date mi?)

Hidden alan var mı? yoksa eklenecek.

Backend: TourBookingRequest ✅ (zaten normalize var)

Şu an gördüğümüz “standartlaşma fotoğrafı”

Villa: UI d.m.Y + hidden Y-m-d ✅

Tour: UI d.m.Y + (muhtemelen) POST d.m.Y ❌ (refactor hedefi)

Transfer: UI d.m.Y + POST d.m.Y ❌ (refactor hedefi)

Hotel: UI d.m.Y - d.m.Y + controller parse ❌ (refactor hedefi; ama listing/detay bağımlılıkları var)

Tur için bir sonraki envanter parçası: tur detay blade booking formu (checkout’a POST eden form kısmı).

En kritik parça net: tur formu şu an name="date" ile d.m.Y gönderiyor. Bu, TourBookingRequest’teki required|date validasyonunu ortama göre kırılgan yapar. (Normalize passedValidation()’da çalışıyor ama önce validator koşuyor.)

Envanter (Tour detail blade)

Dosya: resources/views/pages/excursion/excursion-detail.blade.php (varsayım değil: view path senin controller’da böyleydi)

Form alanları (BE kontratı açısından)

tour_id, tour_name, currency, price_total ✅

adults/children/infants hidden ✅

date: input type=text, name="date", UI format gg.aa.yyyy ✅/❌ (UI için ok, POST için risk)

cover_image[...] hidden ✅ (image policy’ye aykırı değil; snapshot için “client taşıyor” ama sen zaten bunu bilinçli bırakmışsın)

category_name hidden ✅

JS ile ilişkisi

excursion-details.js sadece flatpickr kuruyor, hidden date üretmiyor.

Blade içindeki inline script sadece “boşsa invalid” kontrolü yapıyor.

Sprint listesine eklenecek iş (Tour — Tarih standardizasyonu)

Hedef: POST edilen date alanı her zaman Y-m-d olacak; UI yine d.m.Y kalacak.

Bunu iki şekilde yapabiliriz (karar maddesi olarak listeye yazıyorum; kod yazmıyoruz):

Seçenek A (en az risk, BE değişmez): Hidden “date” + UI input ismi değişir

UI input: name="date_ui" (veya boş name)

Hidden input: name="date" (Y-m-d buraya yazılır)

JS: flatpickr onChange ile hidden’a start.toISOString().slice(0,10) basar

TourBookingRequest değişmez.

Seçenek B (daha az HTML değişimi): Mevcut name="date" aynı kalsın, submit’te dönüştür

Submit event’inde dateInput.value parse edilir → Y-m-d string’ine çevrilip aynı input’a yazılır.

Risk: manuel yazım/format hatası parse etmezse submit engellemek gerekir.

Ben sprint açısından A’yı “kanonik” diye not düşerim; villa ile aynı pattern.

Ayrıca: “tur günleri” filtresi durumu

Form’da data-days="mon,tue..." var ama JS’de bu dataset kullanılmıyor. Bu da ayrı bir backlog maddesi:

Tour — days_of_week enforcement

Sadece seçilebilir günler enable olsun veya diğer günler disable (flatpickr enable/disable listesi).

Şu an yalnızca “minDate today” var.

Dosya envanterine ek (tur tarih işleri)

resources/views/pages/excursion/excursion-detail.blade.php ✅ (bug kaynağı)

resources/js/pages/excursion-details.js ✅ (değişecek yer)

App\Http\Requests\TourBookingRequest ✅ (şu an normalize var ama validator sırası nedeniyle kırılgan)

Sıradaki dosya

Tur tarih standardizasyonu tamamlamak için transfer tarafında da aynı problem var. Envanter için sıradaki en iyi dosya:

resources/views/pages/transfer/index.blade.php (formda departure_date/return_date input name’leri ve formatı)

Transfer sayfasında tarih/zone açısından fotoğraf net. Burada 2 ayrı “tarih standardı” konusu var: arama (GET) ve booking (POST).

1) Arama formu (GET) — şu anki durum

departure_date ve return_date:

type="text", placeholder gg.aa.yyyy

value="{{ request('departure_date') }}" (yani URL’den aynen geri basılıyor)

Bu sayfada flatpickr init’i görünmüyor (muhtemelen global bir JS dosyasında ya da sayfa bundle’ında). Şu dosyada sadece validation ve UI logic var.

Risk

GET parametresi olarak d.m.Y taşımak teknik olarak yapılabilir; ama backend tarafında parse/validation her yerde aynı helper ile yapılmazsa kırılır.

2) Booking formu (POST) — şu anki durum

Booking formundaki hidden tarih alanları:

<input type="hidden" name="departure_date" value="{{ $transferOffer['departure_date'] }}">
<input type="hidden" name="return_date" value="{{ $transferOffer['return_date'] }}">


Bu değerlerin formatı, transferOffer’ı üreten controller/service’in kararına bağlı.

Aynı dosyada card gösteriminde:

Carbon::parse($transferOffer['departure_date'])->translatedFormat('d F Y')


Bu satır bize şunu söylüyor:

$transferOffer['departure_date'] büyük ihtimalle ISO/Y-m-d veya en azından Carbon’un parse edebileceği bir şey.

Eğer d.m.Y olsaydı, parse yine bazen çalışır bazen kırılabilir.

Ama şu an kesin değil: transferOffer['departure_date'] Y-m-d mi, d.m.Y mi, datetime mı? Bu view tek başına garanti etmiyor; yalnızca “parse ediliyor” diyor.

Sprint için net karar seti (Transfer)
A) “Civil date” kontratı

Transfer için departure_date ve return_date domain alanı olarak Y-m-d saklanır/taşınır (cart snapshot dahil).

UI gösterim: locale’e göre formatlanır (TR: d.m.Y input, kartta d F Y gibi).

B) Arama formu için standart

En temiz akış:

UI input (görünen): departure_date_ui / return_date_ui → d.m.Y

URL / backend’e giden gerçek parametre: departure_date / return_date → Y-m-d

Bunu iki yöntemle yaparsın:

Hidden + UI rename (turda önerdiğim “Seçenek A” ile aynı)

Görünen input’un value’sunu submit’te Y-m-d’ye çevirip aynı input’a yazmak (daha riskli)

Ben yine Hidden + rename’i kanonik sayarım; villa pattern’iyle uyumlu.

C) Booking formu için standart

Booking POST’unda hidden departure_date / return_date zaten var.

Bu hidden’lar kesin Y-m-d olmalı.

Eğer transferOffer şu an d.m.Y üretiyorsa: service/controller’da düzeltilir (tek otoriteye bağlanır).

Bu dosyada “tarih standardı” dışında gördüğüm ek konu

Card içinde tarih gösteriminde doğrudan Carbon::parse(...) kullanılmış. Sprint scope’una uygunsa, bunu da “tek otorite presenter”a bağlamak isteyeceğiz. Ama sprint’i bölmemek için not olarak kalsın:

UI Date Presenter (örn. DatePresenter::humanDate($ymd) gibi) ileride: Blade’lerde dağınık Carbon::parse()->translatedFormat() kullanımı azalır.

Sıradaki envanter dosyası (Transfer için)

Transfer tarih standardını kesinleştirmek için artık backend tarafında transferOffer’ın üretildiği yeri görmemiz gerekiyor:

Transfer controller veya service: “search” kısmı ve $transferOffer array mapping’i nerede oluşturuluyor?

Oradaki tarih parse/normalize netleşince, “booking hidden kesin Y-m-d” maddesini kilitleriz.

Bu controller ile tablo netleşti: Transfer tarafında tarihler şu an “ham GET string” olarak taşınıyor ve $transferOffer['departure_date'] = $request->input('departure_date') yapılıyor. Yani bugün sistemde:

Kullanıcı input’u: büyük ihtimalle d.m.Y (placeholder öyle)

Offer + booking hidden’lar: aynen d.m.Y

View’de Carbon::parse($transferOffer['departure_date']): bu format garanti olmadığı için riskli (Carbon her zaman doğru anlamaz).

Buradan hareketle iş akışını şöyle kurmanı öneririm (refactor’a girmeden, adım adım “karar + envanter”):

Önerilen iş akışı (Timezone + Tarih Sprint’i)
0) Kontrat (1 sayfa) — sprint başlamadan kilitle

Service timezone: Europe/Istanbul (iş kuralları)

DB: timestamp’ler UTC (timestamptz)

Civil date alanları: date ve her yerde Y-m-d

UI input formatı: TR’de d.m.Y (yalnız görüntü)

Transport formatı: Request/Session/Cart/Snapshot: Y-m-d

Bu kararlar sprintin “done” kriteri.

1) Envanter çıkar (1–2 saat)

Projede tarih alanı geçen tüm noktaları listele:

Hotel: checkin/checkout (range, hem list hem booking)

Villa: checkin/checkout (range, booking)

Tour: date (single, booking)

Transfer: departure_date/return_date (2 input + booking)

Ayrıca: admin panelde Date/DateTime alanları (Filament)

Amaç: hangi ürün hangi alanı “civil date” olarak kullanıyor netleşsin.

2) “Normalization” tek noktaya alınacak yerleri belirle

Sende zaten doğru yaklaşım başlamış:

Villa/Tour request’leri normalizeDateToYmd() yapıyor.

HotelListingPageService parseCheckinRange(d.m.Y) yapıyor.

Eksik parça Transfer:

Transfer şu an normalize etmiyor.

Burada karar: Civil date normalize işini daima Request katmanına mı alacağız, yoksa controller/service parse mı?

Booking (POST) için zaten FormRequest pattern’in var → FormRequest doğru yer.

Search (GET) için ayrı bir TransferSearchRequest gibi FormRequest kullanmak en temiz standart.

3) İlk hedef “Transfer Search”i düzelt (en küçük ama en kritik kırılma)

Transfer şu an en riskli yerde çünkü:

tarih hem UI hem backend hem booking’de karışık

Carbon::parse ile görünürde “çalışıyormuş gibi” ama edge-case patlar.

Sprintte ilk pratik iş:

Search query param’larını normalize et: d.m.Y → Y-m-d

Offer içindeki departure_date/return_date da Y-m-d olsun.

View’de Carbon::parse(...) yerine (şimdilik) Carbon::createFromFormat('Y-m-d', ...) kullanılır veya presenter’a bağlanır.

4) Sonra Hotel/Villa/Tour ile “tek standardı” eşitle

HotelListingPageService şu an d.m.Y parse ediyor. Bu normal; ama uzun vadede GET paramı da Y-m-d olursa daha temiz.

Villa JS zaten hidden’a toISOString().slice(0,10) basıyor → doğru.

Tour JS şu an input d.m.Y bırakıyor; Request normalize ediyor → doğru. (Ama UI’daki invalid kontrol + placeholder vs standartlaşabilir.)

Şu an sende “hemen yapılacak” en doğru başlangıç

Transfer için Search Request katmanı (GET) planla: input d.m.Y kabul etsin ama $request->validated() çıktısı Y-m-d dönsün.

TransferController offer mapping’de departure_date / return_date artık validated Y-m-d değerleri olsun.

Transfer view’de tarih gösterimi için Carbon::createFromFormat('Y-m-d', ...) (veya presenter).

Bu iş akışıyla:

timezone sprint’i “kuramsal” kalmaz, gerçek bug riskini azaltır,

diğer ürünlerdeki pattern’leri bozmadan ilerlersin.

İstersen bir sonraki adımda (kod yazmadan) sadece şu 3 şeye bakıp “Transfer için kontratı kilitleyelim” diyebilirim:

TransferBookingRequest var mı / nasıl normalize ediyor?

Transfer search için ayrı Request kullanmak istiyor musun?

URL’de tarihleri d.m.Y mi taşımaya devam edeceğiz (UI uyumu) yoksa Y-m-d’ye mi geçeceğiz? (ben Y-m-d öneririm; UI d.m.Y kalsın)

Son mesajımda kullandığım tüm örnekler senin paylaştığın dosyalardan geldi:

config/app.php timezone => UTC + AppServiceProvider::boot() içinde FilamentTimezone::set('Europe/Istanbul')

HotelListingPageService::parseCheckinRange() → d.m.Y parse ediyor

villa-details.js → hidden checkin/checkout toISOString().slice(0,10) (Y-m-d)

TourBookingRequest → normalizeDateToYmd(date) (Y-m-d)

TransferController → departure_date/return_date ham string taşıyor

Transfer blade → Carbon::parse($transferOffer['departure_date']) ile gösteriyor

Kısaca: “timezone + tarih standardı” sprintine başlamak için, paylaştığın kodlar içinde en riskli ve standardı en çok bozan parça Transfer olduğu için oradan başlanmasını önerdim. Bu, bağlam kopması değil; elindeki mevcut durumdan çıkan önceliklendirme.


Mevcut durum (Transfer)

Input: departure_date / return_date request’ten string geliyor (UI’da “gg.aa.yyyy”).

Validasyon yok: Controller’da date formatı/normalize kontrolü yok (sadece “boş mu” kontrolü var).

Snapshot/offer: transferOffer['departure_date'] aynı string olarak view’a gidiyor.

Render: Blade’de Carbon::parse($transferOffer['departure_date']) ile parse edilmeye çalışılıyor.

Bu, Villa/Tour standardından farklı:

Villa/Tour: Request’te normalizeDateToYmd var → içeride Y-m-d taşınıyor.

Transfer: UI formatı doğrudan domain’e sızıyor.

Kontrat / risk noktaları

Carbon::parse riski
"16.01.2026" gibi bir değer Carbon tarafından her ortamda güvenli parse edilmez (yorum farklılaşabilir). Bu “bug” üretir.

Civil date standardı yok
Sende diğer ürünlerde “civil date = Y-m-d” standardı oluşmuş. Transfer bunu bozuyor.

Timezone karmaşası burada değil
Bu problem timezone değil; problem format + normalize katmanı eksik.

Çözüm yolu seçenekleri (kod yazmadan karar seti)
Seçenek A (kanonik): TransferBookingRequest + normalize

Transfer’da da Villa/Tour gibi bir FormRequest devreye girer:

departure_date (required) → normalizeDateToYmd

return_date (direction===roundtrip ise required) → normalizeDateToYmd

Controller TransferBookingRequest değil; bu arama sayfası. Ama book endpoint’i zaten var.
Burada iki yol:

Arama formu için ayrı TransferSearchRequest (GET) yazılır ve normalize orada yapılır.

Ya da controller içinde “parse d.m.Y → Y-m-d” tek noktada yapılır (B seçeneği).

Seçenek B (pragmatik): TransferController içinde normalize helper

departure_date / return_date alındığı anda:

d.m.Y → Y-m-d normalize edilir

Offer + hidden inputs Y-m-d taşır

Blade’de Carbon::parse() yerine Carbon::createFromFormat('Y-m-d', ...) kullanılır.

Seçenek C (UI-only): Blade’de güvenli parse (önerilmez)

Offer aynı kalır, Blade Carbon::createFromFormat('d.m.Y', ...) ile parse eder.

Domain hala UI formatına bağlı kalır; ileride daha çok yerde tekrar eder.

Benim önerim (sıralı iş akışı)

Karar: Transfer’te “civil date” standardı diğer ürünler gibi Y-m-d olacak (A veya B).

Seçtiğimiz yola göre 2. adımda benden isteyeceğin tek dosya:

A için: TransferSearchRequest (yoksa oluşturacağız) + route binding bilgisi

B için: yine sadece TransferController içinde küçük bir normalize fonksiyonu + blade’de 2 satır değişiklik

Hedef standart (tek cümle)

UI (flatpickr) “d.m.Y” gösterir, BE ise her zaman “civil date” olarak Y-m-d taşır ve kaydeder.

Başlangıç iş akışı (adım adım)
1) UI katmanı (flatpickr)

Kullanıcıya gösterim: dateFormat: 'd.m.Y' (şu anki gibi)

Ama form submit’te BE’ye gidecek değer: Y-m-d

Bunu iki yolla yapabilirsin:

flatpickr altInput kullan:

altFormat: 'd.m.Y' (gösterim)

dateFormat: 'Y-m-d' (input value, BE’ye giden)

mevcut input “d.m.Y” kalsın ama submit öncesi hidden departure_date_ymd üret (ben bunu daha riskli buluyorum)

Kanonik olan: altInput yaklaşımı.

2) Controller katmanı (TransferController)

departure_date ve return_date artık zaten Y-m-d geleceği için:

Controller sadece “var mı / direction roundtrip mi” kontrol eder.

Blade’de Carbon::parse() yerine formatı belli parse kullanılır:

Carbon::createFromFormat('Y-m-d', $date) veya direkt Carbon::parse() de güvenli hale gelir çünkü Y-m-d.

3) Görüntüleme formatı (UI)

Liste/kart üstünde “d F Y” gibi locale’e göre gösterim devam eder:

Carbon::createFromFormat('Y-m-d', $transferOffer['departure_date'])->locale(...)->translatedFormat('d F Y')

Şimdi benden istediğim “hangi dosya?”

Tek dosya ile başlayalım (en az risk):

Gönder

Transfer sayfasının JS dosyası (flatpickr kurduğun yer).1️⃣ Mevcut Sistem Durumu — Tespit (Netleşti)
Global Ayarlar

config/app.php

timezone = UTC ✅ (doğru, korunacak)

FilamentTimezone::set('Europe/Istanbul') ✅

Admin panel her zaman TR saati gösteriyor

Operasyon varsayımı: Türkiye (ileride değişebilir)

2️⃣ Tarih Kullanan Ürünler — Envanter

Şu an tüm ürünlerde civil date kullanılıyor, fakat format & parse standardı yok.

🏨 Otel

Dosyalar

Hotel detail controller (show)

Hotel listing (sen az önce hatırlattın → eklendi)

Kullanım

Tek input, range string:

"18.11.2025 - 22.11.2025"

"18.11.2025" (fallback)

Parse:

Controller içinde özel parseDateRange() fonksiyonu

İçerik:

checkin, checkout, nights

Problem:

Format string’e bağlı

Otel’e özel çözüm, reusable değil

🚗 Transfer

Dosyalar

TransferController@index

resources/js/pages/transfer.js

Kullanım

2 ayrı input:

departure_date

return_date

Format:

d.m.Y

Controller:

Tarihler string olarak taşınıyor

Henüz Carbon parse / normalize yok

Problem:

Otelden tamamen farklı yapı

Tarih domain logic’e girmeden snapshot’a gidiyor

🏡 Villa

(Henüz dosya verilmedi ama senin dediğin doğru varsayım şu)

Tek tarih veya range kullanımı var

Büyük ihtimalle:

d.m.Y

Controller içinde lokal parse veya hiç parse yok

➡️ Envantere dahil

🚌 Tur

Tarih seçimi var (tek gün)

Format / parse büyük ihtimalle villa & transfer’e benzer
➡️ Envantere dahil

3️⃣ Sorunun Kök Nedeni (Özet)

Şu an sistemde:

❌ Ürün bazlı farklı parse fonksiyonları

❌ Tarih formatı FE’ye bağımlı

❌ Controller’larda “string parse” domain logic’e karışmış

❌ Civil date ↔ instant ayrımı kodda net değil

Ama:

✅ Timezone kararı doğru

✅ DB ve Filament tarafı doğru yerde

Yani temel sağlam, standart eksik.

4️⃣ Sprint’in Gerçek Amacı (Net Tanım)

Bu sprintin amacı:

“Tüm ürünlerde tarih bilgisini aynı sözleşmeyle almak, parse etmek, taşımak ve göstermek.”

Sprint bu soruları çözecek:

Sunucuya hangi format gelir?

Range / single date tek mi ayrı mı?

Parse nerede ve kimde yapılır?

Controller’lar tarihle ne kadar ilgilenir?

Civil date ile timestamp kesin ayrıldı mı?


VillaController envantere eklendi. Bu dosyada tarih/timezone açısından kritik nokta: hiç tarih kullanılmıyor (ne filtre, ne availability, ne rezervasyon bağlamı). Bu iyi; sprint kapsamını daraltıyor.

VillaController — Tarih/Timezone Envanter Kaydı
1) Villa listeleme (index)

Tarih alanı yok

Tarih filtresi yok

Civil date / range / flatpickr ile bağlantı yok

Sadece fiyat/currency ve içerik render

2) Villa detay (show)

Tarih alanı yok

JS tarafında (sen daha önce paylaşmıştın) villa-details.js’de checkin flatpickr vardı; ama controller burada onu okumuyor.

Yani villa detay sayfasındaki tarih seçimi şu an muhtemelen yalnızca frontend hesaplama/UX veya başka bir endpoint’e bağlı.

Burada sadece:

fiyat kuralı seçimi (currency)

min/max nights verisini view’a veriyor

Sonuç: VillaController sprintin “tarih parse standardizasyonu” bölümünde doğrudan refactor hedefi değil.

Sprint listesine etkisi

Bu dosyaya göre sprint kapsamı şu şekilde güncellenir:

“Tarih kullanan BE akışları” (refactor hedefi olacaklar)

Otel detail (range parse var)

Otel listing (tarih filtresi var dedin → bekleniyor)

Transfer index (departure/return var, parse yok)

Tur (bekleniyor)

(Villa için) tarih seçimi varsa, controller değil başka yerde: muhtemelen rezervasyon/checkout/cart tarafında veya ayrı bir endpoint (bunu ayrıca bulacağız)

“Tarih kullanan FE bileşenleri”

Villa-details.js (checkin)

Hotel-details.js (range)

Transfer form (departure/return)

Tur (muhtemelen single date)

Villa için açık “envanter sorusu” (kod yazmadan)

Villa detayda kullanıcı tarih seçiyor ama controller okumuyor. Bu nedenle envanter için şunu işaretliyorum:

Villa tarih seçimi BE’de nerede işleniyor?

seçenekler: Cart/Checkout controller, ayrı bir VillaReservationController, veya JS sadece fiyat hesaplıyor.

Bunu bulmak için bir sonraki dosyada tur controller veya otel listing ile devam edebiliriz; ardından villa tarihinin BE’ye hangi noktadan girdiğini bulmak için senin vereceğin dosyalardan iz süreriz.


TransferController’ı envantere “tarih” açısından net şekilde kaydettim. Bu dosya, sprintin neden gerekli olduğunu çok iyi gösteriyor: tarih var ama civil date standardı + parse/normalize katmanı yok.

TransferController — Tarih/Timezone Envanter Kaydı
Tarih alanları

departure_date (zorunlu)

return_date (roundtrip ise zorunlu)

Kaynak / format

Kaynak: request input ($request->input(...))

Format: FE flatpickr’da dateFormat: "d.m.Y" (sen paylaştın)

Controller tarafında parse yok:

Carbon’a çevrilmiyor

Y-m-d normalize edilmiyor

“departure <= return” gibi tarih mantığı kontrolü yok (sadece return’ın dolu olması kontrol ediliyor)

Taşınma biçimi

Tarihler transferOffer içine string olarak aynen konuluyor:

'departure_date' => $departureDate

'return_date' => $returnDate

Bu, daha sonra cart/checkout/snapshot tarafına bu şekilde taşınabilir (dosyayı görmedik ama risk burada).

Sprint için çıkarım

Transfer tarafı sprintte iki başlık altında ele alınmalı:

Civil date normalizasyonu

FE hangi formatı üretirse üretsin, BE tarafında tek bir standarda dönüştürülmeli (hedef: Y-m-d)

“roundtrip” ise return >= departure kontrolü (civil date compare)

Tek parse otoritesi

Oteldeki gibi controller içi özel fonksiyon yerine, tüm ürünlerin kullanacağı ortak “DateInput / DateParser” katmanı

TourController — Tarih/Timezone Envanter Kaydı
1) Tur listeleme (index)

Tarih alanı yok

Kullanıcıdan tarih input’u alınmıyor

Flatpickr / civil date / range yok

2) Tur detay (show)

Zaman alanları (time-of-day)

start_time:

Controller: $tour->start_time ? $tour->start_time->format('H:i') : null

Bu, “operasyon TR” varsayımında mantıklı.

Ancak bu alanın DB tipi ve timezone anlamı sprintte netleşmeli:

time mi (sadece saat)

yoksa datetime/timestamp mı (an)

Kod format('H:i') dediği için “sadece saat” (civil time) gibi kullanılıyor.

Gün alanları

days_of_week raw array

3) Sprint için çıkarım

Tur tarafı sprintte civil date standardizasyonu kısmında değil; ama “timezone/tarih formatı” sprintine şu iki maddeden dahil edilmeli:

Civil time standardı (start_time)

Bu alan “an” değil, “günün saati” ise:

saklama türü/timezone bağı netleşmeli

gösterim formatı standardı: HH:mm (şu an böyle)

Eğer ileride kullanıcı timezone’una göre “sizin saatiniz” göstermek istenirse, bu ayrı bir UX katmanı olacak.

Gün isimleri standardı

days_of_week değerleri: TR/EN gösterim vs canonical değerler (mon,tue… gibi) ayrı bir sözleşme konusu olabilir.

Şimdilik sprint notu olarak: “date/time sprinti kapsamına dahil edilecek mi?” diye işaretleyebiliriz.

Envanter tablosu güncelleme (özet)
Civil date input kullanan BE (refactor hedefi)

Hotel detail (range parse var)

Transfer index (date var, parse yok)

Hotel listing (tarih filtresi var → bekleniyor)

Villa date picker BE entrypoint (controller’da yok → bulunacak)

Time-of-day kullanan BE

Tour detail: start_time → HH:mm format

Kritik eksik parça: Otel listing

Senin sprint hedefin için şu an en kritik kalan dosya otel listeleme (tarih filtresi olan). Çünkü:

Listing sayfası “filtre standardı” demek

Hotel detail’deki range parse ile aynı formatı mı kullanıyor, yoksa farklı mı, bunu görmeden “tek standart” kararı kilitlenmez.

HotelController envanter açısından kritik iki şeyi netleştirdi:

Otel detail tarafında tarih standardı “controller içi parse” ile gidiyor (parseDateRange).

Otel listing tarafı “tek kaynak” olarak HotelListingPageService’e devredilmiş → tarih filtresi asıl orada.

Bu, sprintin en önemli refactor hedefini de işaret ediyor: tüm tarih parse/normalize işini controller/service bazında dağınık olmaktan çıkarıp tek standarda toplamak.

HotelController — Tarih/Timezone Envanter Kaydı
A) Otel detay (show)

Girdi

Query param: checkin

Format bekleniyor:

"18.11.2025 - 22.11.2025" veya "18.11.2025"

Parse:

Controller içindeki parseDateRange() ile

Çıktı: Carbon startOfDay + nights

Kullanım

context['checkin'] / context['checkout'] Carbon

resolveRoomPricing() içinde resolver’a giderken tekrar Y-m-d formatına çevriliyor:

$checkin->format('Y-m-d')

$rangeEnd->format('Y-m-d')

Sprint çıkarımı

Civil date + range parse mevcut ama sadece otelde var ve controller’a gömülü.

Çıktının son kullanımı zaten Y-m-d → bu iyi; standarda yakınız.

B) Otel listeleme (index)

Controller sadece:

$service->build($request) çağırıyor

Tarih filtresi (senin dediğin) ve parse/format işleri HotelListingPageService içinde.

✅ Bu, envanterde “tek otorite” olabilecek ideal yerlerden biri (service layer), ama şu an muhtemelen otel özelinde.


Sprint için yeni net hedef maddeleri (otel üzerinden somutlaştı)

HotelController@show parseDateRange: “otel özel” olmaktan çıkarılacak (tek parse standardına bağlanacak).

HotelListingPageService: listing filtreleri içindeki tarih parse/normalize yaklaşımı, sprintin kanonik referansı olacak (görmeden karar kilitlenmez).

TransferController@index: date stringler normalize edilmeden taşınıyor → standarda çekilecek.

HotelListingPageService, sprintin “tarih standardı” kısmı için kanonik dosya oldu. Burada tarih filtresi aktif şekilde query’yi daraltıyor ve ayrıca aynı parse mantığı HotelController@show ile kopya durumda. En önemli bulgu bu.

HotelListingPageService — Tarih/Timezone Envanter Kaydı
1) Tarih giriş alanı

Request param: checkin

Beklenen format:

"18.11.2025 - 22.11.2025" veya "18.11.2025"

Parse fonksiyonu:

parseCheckinRange() (otel listing’e özel)

Carbon::createFromFormat('d.m.Y') + startOfDay()

Tek tarih gelirse checkout = checkin + 1 day

✅ Bu, otel detail’deki parseDateRange() ile aynı sözleşme.

2) Tarihin query’ye etkisi (kritik)

Listing’de tarih seçimi:

“fiyatı değiştirmez” (not düşmüşsün)

sonuç setini daraltır

Uygulama şekli:

$rangeStart = $checkin->toDateString()

$rangeEnd = (clone $checkout)->subDay()->toDateString()

sonra applyDateOverlap($q, $rangeStart, $rangeEnd)

Bu şu anlama geliyor:

UI’da checkout seçilse bile, filtre “gecelenecek günler” mantığıyla checkout-1 üzerinden çalışıyor (doğru).

3) Tarih overlap kuralı (mevcut standart)

applyDateOverlap() şu kuralı uyguluyor:

date_start ve date_end ikisi de null ise her zaman geçerli

aksi halde overlap:

date_start null veya <= rangeEnd

date_end null veya >= rangeStart

Bu, ileride tüm ürünlerde “availability” yaklaşımı için referans olabilir.

Sprint açısından çıkan net problemler
P-1) Kopya parse mantığı

HotelController@show: parseDateRange()

HotelListingPageService: parseCheckinRange()

İkisi aynı işi yapıyor → sprintte tek parse otoritesine indirilmeli.

P-2) Format bağımlılığı (d.m.Y)

Hem listing hem detail tamamen d.m.Y’ye bağlı.

Transfer de d.m.Y string taşıyor (parse yok).

Villa FE d.m.Y (senin eski snippet).

Bu, “standardizasyon sprinti”nin ana konusu.

P-3) Civil date ile timezone ilişkisi

Burada Carbon startOfDay() kullanılıyor → timezone’a bağlı davranır.

config/app.php UTC olduğu için, FE’nin TR tarihini UTC’de startOfDay’a çevirmek bazı edge-case’lerde gün kayması riskini teorik olarak doğurur (özellikle ileride timezone değişirse).

Bu yüzden sprintte “civil date parse”yi timezone’dan bağımsız bir sözleşme olarak tanımlamalıyız.

Bu pencere için sprint yapılacaklar listesi (dosya bazında)
1) Kontrat / Standart kararlar (doküman)

Civil date taşıma formatı (tek format)

Range temsil şekli (tek delimiter)

Tek parse otoritesi (hangi sınıf/katman)

Carbon yerine “immutable date” tercihi (karar maddesi)

UI gösterim formatı locale’e göre (TR: d.m.Y) ama BE formatı sabit (örn. Y-m-d)

2) Envanter refactor hedef dosyaları

Otel

HotelController@show → parseDateRange kaldırılacak / standard parse’a bağlanacak

HotelListingPageService → parseCheckinRange kaldırılacak / standard parse’a bağlanacak

View tarafında filters.checkin taşıma biçimi standarda uyacak

Transfer

TransferController@index → departure/return standard parse’a bağlanacak (string taşımak yerine normalize edilmiş “civil date” seti)

Transfer JS → valueFormat/altFormat standardı (bu daha sonra, ayrı pencere)

Villa

Villa tarih seçiminin BE’ye giriş noktası bulunacak (Cart/Checkout/Add-to-cart aksiyonu)

O noktada standard parse uygulanacak

Tur

start_time alanı civil time mı? (DB tipi/sözleşme) sprint notu olarak netleştirilecek

3) Ortak altyapı

Tek “DateInput/DateRange” helper katmanı (hangi namespace, nerede kullanılacak)

Validasyon: minimum 1 gece, checkout > checkin, roundtrip return >= departure


Villa-details.js envantere alındı; burada sprint için en kritik keşif var:

Villa’da kullanıcıya d.m.Y gösteriyorsun ama BE’ye hidden input ile YYYY-MM-DD gönderiyorsun (toISOString().slice(0,10)).

Otel ve transferde ise BE’ye hâlâ d.m.Y string gidiyor (otel checkin query, transfer departure_date/return_date).

Bu fark, sprintin ana hedefini netleştiriyor: tüm ürünlerde BE’ye taşınan tarih formatını tekleştirmek.

Villa-details.js — Tarih/Timezone Envanter Kaydı
UI (gösterim)

flatpickr:

mode: 'range'

dateFormat: 'd.m.Y'

minDate: 'today'

Min/max nights kontrolü FE’de yapılıyor.

BE’ye taşınan değer (asıl standart)

Hidden alanlara yazılan:

hidden-checkin: YYYY-MM-DD

hidden-checkout: YYYY-MM-DD

villa-nights: sayı

fiyatlar: numeric string (2 decimals)

✅ Bu, bizim daha önce konuştuğumuz hedefe birebir uyuyor:

UI locale formatlı gösterir

BE sabit format alır (Y-m-d)

Sprint çıkarımı (çok net)

Şu an projede “tarih standardı” ikiye bölünmüş:

Doğru yolda olan yaklaşım (Villa)

UI: d.m.Y

Payload: Y-m-d (hidden)

Standartsız yaklaşım (Otel + Transfer)

UI: d.m.Y

Payload: d.m.Y (query / input)

BE parse etmek zorunda kalıyor (otel parse var, transfer parse yok)

Envanter artık tamam: “Tarih nasıl taşınıyor?” matrisi
Ürün	UI	BE’ye taşınan	Parse nerede?
Villa	d.m.Y range	Y-m-d (hidden)	Parse yok (zaten normalize)
Otel detail	d.m.Y range	d.m.Y - d.m.Y (query)	BE controller parse
Otel listing	d.m.Y range	d.m.Y - d.m.Y (query)	BE service parse
Transfer	d.m.Y single	d.m.Y (input)	Parse yok (şu an risk)
Tur	tarih yok	—	—
Sprint yapılacaklar listesine eklenen somut maddeler
A) “Tek tarih taşıma standardı” kararı

Kanonik payload format: YYYY-MM-DD

Range payload: iki alan (checkin, checkout) veya tek alan (karar) ama format sabit.

Villa zaten böyle çalışıyor → bunu “referans standart” yapabiliriz.

B) Otel refactor hedefi

checkin query string’i (“18.11.2025 - 22.11.2025”) yerine:

ya hidden checkin=YYYY-MM-DD + checkout=YYYY-MM-DD

ya da single input ama value YYYY-MM-DD - YYYY-MM-DD (tercih etmeyiz)

Bu değişiklikle:

parseDateRange / parseCheckinRange gibi d.m.Y parse’ları ortadan kalkar veya sadece backward-compat için kalır.

C) Transfer refactor hedefi

departure_date / return_date payload’ı YYYY-MM-DD olacak (UI d.m.Y kalabilir)

TransferController artık string taşımak yerine normalize edilmiş date seti taşır.

Bu pencereyi “sprint planı” ile kapatmak için eksik tek veri

Villa’nın hidden input’ları BE’de kim okuyor? (cart/add-to-cart/checkout)

Envanterin son parçası için sıradaki dosya:

Villa’yı sepete ekleyen action/controller (hidden-checkin/hidden-checkout alanlarını alan endpoint)

Tur notu (tarih olmalı)

Envanter açısından şu an net olan: TourController’da tarih seçimi yok, ama CheckoutController@bookTour var ve TourBookingRequest ile payload alıyor. Yani tur tarihinin “nerede” olduğuna dair aradığımız yer TourBookingRequest + tur detay blade/js formu. Şu an elimizde olmadığından sadece “bulunacak” diye listeye ekleyebiliyoruz.

Villa tarafı: Tarih standardı zaten doğru uygulanmış (envanter kaydı)
1) VillaBookingRequest sözleşmesi

checkin, checkout alanları date rule ile doğrulanıyor.

passedValidation() içinde:

checkin ve checkout → normalizeDateToYmd()

currency → normalize

Bu, sprint standardı için “referans implementasyon”.

2) Villa FE → BE akışı

FE (flatpickr) kullanıcıya d.m.Y gösteriyor ama hidden’a YYYY-MM-DD yazıyor.

BE request de passedValidation() ile tekrar normalize ediyor.
Sonuç: çift katmanlı güvenli standart.

✅ Bu artık “ICR Date Input Standard” için kanonik örnek.

CheckoutController: ürünler arası tarih standardı farkı netleşti
Villa

bookVilla(VillaBookingRequest ...)

checkin/checkout zaten YYYY-MM-DD ve normalize ediliyor.

Sepete snapshot’a bu şekilde giriyor.

Hotel / Transfer / Tour

Hepsi FormRequest kullanıyor:

HotelBookingRequest

TransferBookingRequest

TourBookingRequest

Ama içeriklerini görmeden şunu söyleyebiliriz:

Villa’da açıkça “Y-m-d standardı + normalize” var.

Diğer üçünde bu garanti şu an belirsiz.

Sprint envanteri açısından yapılacak iş: bu üç request’i açıp villa ile aynı standarda çekmek.

Sprint checklist’ine eklenen dosyalar (kesin)
“Date Standardizasyon Sprinti” hedef dosyaları

App\Http\Requests\VillaBookingRequest ✅ (referans)

App\Http\Requests\HotelBookingRequest (incelenecek)

App\Http\Requests\TransferBookingRequest (incelenecek)

App\Http\Requests\TourBookingRequest (incelenecek) ← tur tarihini burada göreceğiz

HotelController@show (parseDateRange) (refactor hedefi)

HotelListingPageService (parseCheckinRange) (refactor hedefi)

Transfer sayfasının controller + blade/js form payload’ı (date format uyumu)

Tur detay sayfasının blade/js booking formu (tarihi nereden alıyor?)

Tur tarafı netleşti: tarih alanı var ve standart doğru kurgulanmış.

Envanter kaydı — TourBookingRequest

Alan adı: date

Validation: required|date

Normalize: passedValidation() içinde date => normalizeDateToYmd()

Yani BE tarafında tur tarihi de kanonik YYYY-MM-DD kabul ediyor.

Bu şu anlama geliyor:

1) “Turda tarih yok” problemi teknik değil, UI/payload problemidir

Tur detay sayfasındaki form ya date göndermiyor,

ya date alanını d.m.Y gönderiyor ve normalize etmesi bekleniyor (normalize ediyor ama date rule’un parse edip etmediği tarayıcı/string formatına göre riskli),

ya da date input adı farklı.

2) Sprint için net hedef

Tur tarafında yapılacak iş: UI’da date seçimini standartlaştırıp BE’ye güvenli formatta taşımak. (Villa yaklaşımı)

Sprint checklist güncellemesi (tur)

✅ App\Http\Requests\TourBookingRequest (zaten standart)

⏳ Tur detay booking formu (Blade): name="date" var mı, hangi değer gidiyor?

⏳ Tur detay JS/flatpickr varsa: UI d.m.Y olabilir ama payload hidden YYYY-MM-DD olmalı (villa standardı)

Envanterde “date standardı” durumu (güncel)

Villa: ✅ UI d.m.Y / payload Y-m-d / request normalize

Tour: ✅ request normalize var; UI/payload belirsiz

Hotel: ❌ payload d.m.Y range; controller/service parse ediyor

Transfer: ❌ payload d.m.Y single; controller parse yok

Tur UI tarafını kesinlemek için sıradaki dosya:

Tur detay sayfasındaki form (checkout’a POST eden bölüm) ve varsa flatpickr init dosyası.

Tur UI tarafı da netleşti; şu an standart dışı bir nokta var.

Envanter — excursion-details.js (tur detay)
Tarih

Input: #excursion-date

flatpickr: dateFormat: "d.m.Y"

JS tarafında Y-m-d hidden üretimi yok (villa’daki gibi toISOString().slice(0,10) yazmıyor)

Dolayısıyla form büyük ihtimalle date alanını d.m.Y olarak POST ediyor.

Risk / Bug ihtimali

TourBookingRequest içinde date için:

önce rules(): required|date çalışıyor,

sonra passedValidation() normalize ediyor.

Laravel’in date validator’ı d.m.Y string’i her ortamda güvenle parse etmeyebilir (genelde ISO/Y-m-d daha güvenli). Bu yüzden tur tarafında “tarih var ama bazen validasyon patlıyor” tipi sorunlar çıkabilir.

Sprint hedefi (tur için) kesinleşti

Tur booking formunda:

Kullanıcıya yine d.m.Y gösterebilirsin,

ama POST edilen alan date mutlaka YYYY-MM-DD olmalı.

Villa standardını birebir uygularız:

Görünen input d.m.Y

Hidden input date (veya mevcut date alanı) Y-m-d

Checklist’e ek (tur)

resources/js/pages/excursion-details.js ✅ (envanter alındı)

Tur detay blade formu:

excursion-date input’un name’i ne? (date mi?)

Hidden alan var mı? yoksa eklenecek.

Backend: TourBookingRequest ✅ (zaten normalize var)

Şu an gördüğümüz “standartlaşma fotoğrafı”

Villa: UI d.m.Y + hidden Y-m-d ✅

Tour: UI d.m.Y + (muhtemelen) POST d.m.Y ❌ (refactor hedefi)

Transfer: UI d.m.Y + POST d.m.Y ❌ (refactor hedefi)

Hotel: UI d.m.Y - d.m.Y + controller parse ❌ (refactor hedefi; ama listing/detay bağımlılıkları var)

Tur için bir sonraki envanter parçası: tur detay blade booking formu (checkout’a POST eden form kısmı).

En kritik parça net: tur formu şu an name="date" ile d.m.Y gönderiyor. Bu, TourBookingRequest’teki required|date validasyonunu ortama göre kırılgan yapar. (Normalize passedValidation()’da çalışıyor ama önce validator koşuyor.)

Envanter (Tour detail blade)

Dosya: resources/views/pages/excursion/excursion-detail.blade.php (varsayım değil: view path senin controller’da böyleydi)

Form alanları (BE kontratı açısından)

tour_id, tour_name, currency, price_total ✅

adults/children/infants hidden ✅

date: input type=text, name="date", UI format gg.aa.yyyy ✅/❌ (UI için ok, POST için risk)

cover_image[...] hidden ✅ (image policy’ye aykırı değil; snapshot için “client taşıyor” ama sen zaten bunu bilinçli bırakmışsın)

category_name hidden ✅

JS ile ilişkisi

excursion-details.js sadece flatpickr kuruyor, hidden date üretmiyor.

Blade içindeki inline script sadece “boşsa invalid” kontrolü yapıyor.

Sprint listesine eklenecek iş (Tour — Tarih standardizasyonu)

Hedef: POST edilen date alanı her zaman Y-m-d olacak; UI yine d.m.Y kalacak.

Bunu iki şekilde yapabiliriz (karar maddesi olarak listeye yazıyorum; kod yazmıyoruz):

Seçenek A (en az risk, BE değişmez): Hidden “date” + UI input ismi değişir

UI input: name="date_ui" (veya boş name)

Hidden input: name="date" (Y-m-d buraya yazılır)

JS: flatpickr onChange ile hidden’a start.toISOString().slice(0,10) basar

TourBookingRequest değişmez.

Seçenek B (daha az HTML değişimi): Mevcut name="date" aynı kalsın, submit’te dönüştür

Submit event’inde dateInput.value parse edilir → Y-m-d string’ine çevrilip aynı input’a yazılır.

Risk: manuel yazım/format hatası parse etmezse submit engellemek gerekir.

Ben sprint açısından A’yı “kanonik” diye not düşerim; villa ile aynı pattern.

Ayrıca: “tur günleri” filtresi durumu

Form’da data-days="mon,tue..." var ama JS’de bu dataset kullanılmıyor. Bu da ayrı bir backlog maddesi:

Tour — days_of_week enforcement

Sadece seçilebilir günler enable olsun veya diğer günler disable (flatpickr enable/disable listesi).

Şu an yalnızca “minDate today” var.

Dosya envanterine ek (tur tarih işleri)

resources/views/pages/excursion/excursion-detail.blade.php ✅ (bug kaynağı)

resources/js/pages/excursion-details.js ✅ (değişecek yer)

App\Http\Requests\TourBookingRequest ✅ (şu an normalize var ama validator sırası nedeniyle kırılgan)

Sıradaki dosya

Tur tarih standardizasyonu tamamlamak için transfer tarafında da aynı problem var. Envanter için sıradaki en iyi dosya:

resources/views/pages/transfer/index.blade.php (formda departure_date/return_date input name’leri ve formatı)

Transfer sayfasında tarih/zone açısından fotoğraf net. Burada 2 ayrı “tarih standardı” konusu var: arama (GET) ve booking (POST).

1) Arama formu (GET) — şu anki durum

departure_date ve return_date:

type="text", placeholder gg.aa.yyyy

value="{{ request('departure_date') }}" (yani URL’den aynen geri basılıyor)

Bu sayfada flatpickr init’i görünmüyor (muhtemelen global bir JS dosyasında ya da sayfa bundle’ında). Şu dosyada sadece validation ve UI logic var.

Risk

GET parametresi olarak d.m.Y taşımak teknik olarak yapılabilir; ama backend tarafında parse/validation her yerde aynı helper ile yapılmazsa kırılır.

2) Booking formu (POST) — şu anki durum

Booking formundaki hidden tarih alanları:

<input type="hidden" name="departure_date" value="{{ $transferOffer['departure_date'] }}">
<input type="hidden" name="return_date" value="{{ $transferOffer['return_date'] }}">


Bu değerlerin formatı, transferOffer’ı üreten controller/service’in kararına bağlı.

Aynı dosyada card gösteriminde:

Carbon::parse($transferOffer['departure_date'])->translatedFormat('d F Y')


Bu satır bize şunu söylüyor:

$transferOffer['departure_date'] büyük ihtimalle ISO/Y-m-d veya en azından Carbon’un parse edebileceği bir şey.

Eğer d.m.Y olsaydı, parse yine bazen çalışır bazen kırılabilir.

Ama şu an kesin değil: transferOffer['departure_date'] Y-m-d mi, d.m.Y mi, datetime mı? Bu view tek başına garanti etmiyor; yalnızca “parse ediliyor” diyor.

Sprint için net karar seti (Transfer)
A) “Civil date” kontratı

Transfer için departure_date ve return_date domain alanı olarak Y-m-d saklanır/taşınır (cart snapshot dahil).

UI gösterim: locale’e göre formatlanır (TR: d.m.Y input, kartta d F Y gibi).

B) Arama formu için standart

En temiz akış:

UI input (görünen): departure_date_ui / return_date_ui → d.m.Y

URL / backend’e giden gerçek parametre: departure_date / return_date → Y-m-d

Bunu iki yöntemle yaparsın:

Hidden + UI rename (turda önerdiğim “Seçenek A” ile aynı)

Görünen input’un value’sunu submit’te Y-m-d’ye çevirip aynı input’a yazmak (daha riskli)

Ben yine Hidden + rename’i kanonik sayarım; villa pattern’iyle uyumlu.

C) Booking formu için standart

Booking POST’unda hidden departure_date / return_date zaten var.

Bu hidden’lar kesin Y-m-d olmalı.

Eğer transferOffer şu an d.m.Y üretiyorsa: service/controller’da düzeltilir (tek otoriteye bağlanır).

Bu dosyada “tarih standardı” dışında gördüğüm ek konu

Card içinde tarih gösteriminde doğrudan Carbon::parse(...) kullanılmış. Sprint scope’una uygunsa, bunu da “tek otorite presenter”a bağlamak isteyeceğiz. Ama sprint’i bölmemek için not olarak kalsın:

UI Date Presenter (örn. DatePresenter::humanDate($ymd) gibi) ileride: Blade’lerde dağınık Carbon::parse()->translatedFormat() kullanımı azalır.

Sıradaki envanter dosyası (Transfer için)

Transfer tarih standardını kesinleştirmek için artık backend tarafında transferOffer’ın üretildiği yeri görmemiz gerekiyor:

Transfer controller veya service: “search” kısmı ve $transferOffer array mapping’i nerede oluşturuluyor?

Oradaki tarih parse/normalize netleşince, “booking hidden kesin Y-m-d” maddesini kilitleriz.

Bu controller ile tablo netleşti: Transfer tarafında tarihler şu an “ham GET string” olarak taşınıyor ve $transferOffer['departure_date'] = $request->input('departure_date') yapılıyor. Yani bugün sistemde:

Kullanıcı input’u: büyük ihtimalle d.m.Y (placeholder öyle)

Offer + booking hidden’lar: aynen d.m.Y

View’de Carbon::parse($transferOffer['departure_date']): bu format garanti olmadığı için riskli (Carbon her zaman doğru anlamaz).

Buradan hareketle iş akışını şöyle kurmanı öneririm (refactor’a girmeden, adım adım “karar + envanter”):

Önerilen iş akışı (Timezone + Tarih Sprint’i)
0) Kontrat (1 sayfa) — sprint başlamadan kilitle

Service timezone: Europe/Istanbul (iş kuralları)

DB: timestamp’ler UTC (timestamptz)

Civil date alanları: date ve her yerde Y-m-d

UI input formatı: TR’de d.m.Y (yalnız görüntü)

Transport formatı: Request/Session/Cart/Snapshot: Y-m-d

Bu kararlar sprintin “done” kriteri.

1) Envanter çıkar (1–2 saat)

Projede tarih alanı geçen tüm noktaları listele:

Hotel: checkin/checkout (range, hem list hem booking)

Villa: checkin/checkout (range, booking)

Tour: date (single, booking)

Transfer: departure_date/return_date (2 input + booking)

Ayrıca: admin panelde Date/DateTime alanları (Filament)

Amaç: hangi ürün hangi alanı “civil date” olarak kullanıyor netleşsin.

2) “Normalization” tek noktaya alınacak yerleri belirle

Sende zaten doğru yaklaşım başlamış:

Villa/Tour request’leri normalizeDateToYmd() yapıyor.

HotelListingPageService parseCheckinRange(d.m.Y) yapıyor.

Eksik parça Transfer:

Transfer şu an normalize etmiyor.

Burada karar: Civil date normalize işini daima Request katmanına mı alacağız, yoksa controller/service parse mı?

Booking (POST) için zaten FormRequest pattern’in var → FormRequest doğru yer.

Search (GET) için ayrı bir TransferSearchRequest gibi FormRequest kullanmak en temiz standart.

3) İlk hedef “Transfer Search”i düzelt (en küçük ama en kritik kırılma)

Transfer şu an en riskli yerde çünkü:

tarih hem UI hem backend hem booking’de karışık

Carbon::parse ile görünürde “çalışıyormuş gibi” ama edge-case patlar.

Sprintte ilk pratik iş:

Search query param’larını normalize et: d.m.Y → Y-m-d

Offer içindeki departure_date/return_date da Y-m-d olsun.

View’de Carbon::parse(...) yerine (şimdilik) Carbon::createFromFormat('Y-m-d', ...) kullanılır veya presenter’a bağlanır.

4) Sonra Hotel/Villa/Tour ile “tek standardı” eşitle

HotelListingPageService şu an d.m.Y parse ediyor. Bu normal; ama uzun vadede GET paramı da Y-m-d olursa daha temiz.

Villa JS zaten hidden’a toISOString().slice(0,10) basıyor → doğru.

Tour JS şu an input d.m.Y bırakıyor; Request normalize ediyor → doğru. (Ama UI’daki invalid kontrol + placeholder vs standartlaşabilir.)

Şu an sende “hemen yapılacak” en doğru başlangıç

Transfer için Search Request katmanı (GET) planla: input d.m.Y kabul etsin ama $request->validated() çıktısı Y-m-d dönsün.

TransferController offer mapping’de departure_date / return_date artık validated Y-m-d değerleri olsun.

Transfer view’de tarih gösterimi için Carbon::createFromFormat('Y-m-d', ...) (veya presenter).

Bu iş akışıyla:

timezone sprint’i “kuramsal” kalmaz, gerçek bug riskini azaltır,

diğer ürünlerdeki pattern’leri bozmadan ilerlersin.

İstersen bir sonraki adımda (kod yazmadan) sadece şu 3 şeye bakıp “Transfer için kontratı kilitleyelim” diyebilirim:

TransferBookingRequest var mı / nasıl normalize ediyor?

Transfer search için ayrı Request kullanmak istiyor musun?

URL’de tarihleri d.m.Y mi taşımaya devam edeceğiz (UI uyumu) yoksa Y-m-d’ye mi geçeceğiz? (ben Y-m-d öneririm; UI d.m.Y kalsın)

Son mesajımda kullandığım tüm örnekler senin paylaştığın dosyalardan geldi:

config/app.php timezone => UTC + AppServiceProvider::boot() içinde FilamentTimezone::set('Europe/Istanbul')

HotelListingPageService::parseCheckinRange() → d.m.Y parse ediyor

villa-details.js → hidden checkin/checkout toISOString().slice(0,10) (Y-m-d)

TourBookingRequest → normalizeDateToYmd(date) (Y-m-d)

TransferController → departure_date/return_date ham string taşıyor

Transfer blade → Carbon::parse($transferOffer['departure_date']) ile gösteriyor

Kısaca: “timezone + tarih standardı” sprintine başlamak için, paylaştığın kodlar içinde en riskli ve standardı en çok bozan parça Transfer olduğu için oradan başlanmasını önerdim. Bu, bağlam kopması değil; elindeki mevcut durumdan çıkan önceliklendirme.


Mevcut durum (Transfer)

Input: departure_date / return_date request’ten string geliyor (UI’da “gg.aa.yyyy”).

Validasyon yok: Controller’da date formatı/normalize kontrolü yok (sadece “boş mu” kontrolü var).

Snapshot/offer: transferOffer['departure_date'] aynı string olarak view’a gidiyor.

Render: Blade’de Carbon::parse($transferOffer['departure_date']) ile parse edilmeye çalışılıyor.

Bu, Villa/Tour standardından farklı:

Villa/Tour: Request’te normalizeDateToYmd var → içeride Y-m-d taşınıyor.

Transfer: UI formatı doğrudan domain’e sızıyor.

Kontrat / risk noktaları

Carbon::parse riski
"16.01.2026" gibi bir değer Carbon tarafından her ortamda güvenli parse edilmez (yorum farklılaşabilir). Bu “bug” üretir.

Civil date standardı yok
Sende diğer ürünlerde “civil date = Y-m-d” standardı oluşmuş. Transfer bunu bozuyor.

Timezone karmaşası burada değil
Bu problem timezone değil; problem format + normalize katmanı eksik.

Çözüm yolu seçenekleri (kod yazmadan karar seti)
Seçenek A (kanonik): TransferBookingRequest + normalize

Transfer’da da Villa/Tour gibi bir FormRequest devreye girer:

departure_date (required) → normalizeDateToYmd

return_date (direction===roundtrip ise required) → normalizeDateToYmd

Controller TransferBookingRequest değil; bu arama sayfası. Ama book endpoint’i zaten var.
Burada iki yol:

Arama formu için ayrı TransferSearchRequest (GET) yazılır ve normalize orada yapılır.

Ya da controller içinde “parse d.m.Y → Y-m-d” tek noktada yapılır (B seçeneği).

Seçenek B (pragmatik): TransferController içinde normalize helper

departure_date / return_date alındığı anda:

d.m.Y → Y-m-d normalize edilir

Offer + hidden inputs Y-m-d taşır

Blade’de Carbon::parse() yerine Carbon::createFromFormat('Y-m-d', ...) kullanılır.

Seçenek C (UI-only): Blade’de güvenli parse (önerilmez)

Offer aynı kalır, Blade Carbon::createFromFormat('d.m.Y', ...) ile parse eder.

Domain hala UI formatına bağlı kalır; ileride daha çok yerde tekrar eder.

Benim önerim (sıralı iş akışı)

Karar: Transfer’te “civil date” standardı diğer ürünler gibi Y-m-d olacak (A veya B).

Seçtiğimiz yola göre 2. adımda benden isteyeceğin tek dosya:

A için: TransferSearchRequest (yoksa oluşturacağız) + route binding bilgisi

B için: yine sadece TransferController içinde küçük bir normalize fonksiyonu + blade’de 2 satır değişiklik

Hedef standart (tek cümle)

UI (flatpickr) “d.m.Y” gösterir, BE ise her zaman “civil date” olarak Y-m-d taşır ve kaydeder.

Başlangıç iş akışı (adım adım)
1) UI katmanı (flatpickr)

Kullanıcıya gösterim: dateFormat: 'd.m.Y' (şu anki gibi)

Ama form submit’te BE’ye gidecek değer: Y-m-d

Bunu iki yolla yapabilirsin:

flatpickr altInput kullan:

altFormat: 'd.m.Y' (gösterim)

dateFormat: 'Y-m-d' (input value, BE’ye giden)

mevcut input “d.m.Y” kalsın ama submit öncesi hidden departure_date_ymd üret (ben bunu daha riskli buluyorum)

Kanonik olan: altInput yaklaşımı.

2) Controller katmanı (TransferController)

departure_date ve return_date artık zaten Y-m-d geleceği için:

Controller sadece “var mı / direction roundtrip mi” kontrol eder.

Blade’de Carbon::parse() yerine formatı belli parse kullanılır:

Carbon::createFromFormat('Y-m-d', $date) veya direkt Carbon::parse() de güvenli hale gelir çünkü Y-m-d.

3) Görüntüleme formatı (UI)

Liste/kart üstünde “d F Y” gibi locale’e göre gösterim devam eder:

Carbon::createFromFormat('Y-m-d', $transferOffer['departure_date'])->locale(...)->translatedFormat('d F Y')






