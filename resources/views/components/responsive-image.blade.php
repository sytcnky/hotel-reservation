@props([
'image',
'preset' => null,
'class' => '',
'sizes' => null,   // override edilebilir
'no2x' => false,
])

@php
// ----------------------------------------------------
// PRESET SIZES (default)
// ----------------------------------------------------
$presetSizes = [
'listing-card' => '(max-width: 576px) 100vw,
(max-width: 992px) 50vw,
33vw',

'gallery' => '(max-width: 576px) 100vw,
900px',

'gallery-thumb' => '92px',
];

// Eğer sizes parametresi verilmemişse preset'tekini kullan
$resolvedSizes = $sizes ?? ($preset ? ($presetSizes[$preset] ?? '100vw') : '100vw');

// ----------------------------------------------------
// Görsel kaynakları
// ----------------------------------------------------
$thumb   = $image['thumb']   ?? null;
$thumb2x = $image['thumb2x'] ?? null;
$small   = $image['small']   ?? null;
$small2x = $image['small2x'] ?? null;
$large   = $image['large']   ?? null;
$large2x = $image['large2x'] ?? null;

// fallback
$src = $large ?? $small ?? $thumb ?? '/images/placeholder-1x1.png';

// ----------------------------------------------------
// PRESET → hangi görseller srcset'e girsin?
// ----------------------------------------------------
$presetVariants = [
'listing-card'  => ['thumb','small','large'],
'gallery'       => ['small','large'],
'gallery-thumb' => ['thumb'],
];

$variants = $preset ? ($presetVariants[$preset] ?? ['thumb']) : ['thumb','small','large'];

// ----------------------------------------------------
// srcset oluştur
// ----------------------------------------------------
$srcsetParts = [];

foreach ($variants as $variant) {
if ($variant === 'thumb' && $thumb) {
$srcsetParts[] = "{$thumb} 150w";
if (!$no2x && $thumb2x) $srcsetParts[] = "{$thumb2x} 300w";
}

if ($variant === 'small' && $small) {
$srcsetParts[] = "{$small} 320w";
if (!$no2x && $small2x) $srcsetParts[] = "{$small2x} 640w";
}

if ($variant === 'large' && $large) {
$srcsetParts[] = "{$large} 900w";
if (!$no2x && $large2x) $srcsetParts[] = "{$large2x} 1800w";
}
}

$srcset = implode(', ', $srcsetParts);

$alt = $image['alt'] ?? '';
@endphp

<img
    src="{{ $src }}"
    @if($srcset) srcset="{{ $srcset }}" @endif
    sizes="{{ $resolvedSizes }}"
    alt="{{ $alt }}"
    loading="lazy"
    decoding="async"
    {{ $attributes->class(trim("img-fluid {$class}")) }}
/>
