<?php

namespace App\Services;

use App\Models\Campaign;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;

class CampaignPlacementViewService
{
    /**
     * Site sayfaları için placement kampanyaları.
     * - is_active = true
     * - placements JSON array içinde ilgili placement olmalı
     * - priority desc (stabil: id desc)
     * - tarih/device filtresi yok
     *
     * @return array<int,array<string,mixed>>
     */
    public function buildForPlacement(string $placement): array
    {
        $campaigns = Campaign::query()
            ->where('is_active', true)
            ->whereJsonContains('placements', $placement)
            // priority NULL en sonda, sonra priority DESC, stabil: id DESC
            ->orderByRaw('priority is null asc')
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        return $campaigns
            ->map(fn (Campaign $campaign) => $this->toViewModel($campaign))
            ->values()
            ->all();
    }

    protected function toViewModel(Campaign $campaign): array
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $content = (array) ($campaign->content ?? []);
        $block   = I18nHelper::pick($content, $uiLocale, $baseLocale);

        // pick() array döndürmüyorsa güvenli boş array (fallback üretmeden)
        $block = is_array($block) ? $block : [];

        $discount = (array) ($campaign->discount ?? []);
        $bgClass  = (string) ($discount['background_class'] ?? 'bg-primary');

        $ctaText = $block['cta_text'] ?? null;
        $ctaLink = $block['cta_link'] ?? null;

        // CTA: ikisi dolu değilse hiç render etmeyelim.
        if (! $ctaText || ! $ctaLink) {
            $ctaText = null;
            $ctaLink = null;
        }

        return [
            'id'                => $campaign->id,
            'title'             => $block['title'] ?? null,
            'subtitle'          => $block['subtitle'] ?? null,
            'description'       => $block['description'] ?? null,
            'cta_text'          => $ctaText,
            'cta_link'          => $ctaLink,

            'background_class'  => $bgClass,

            // Accessor'lar ImageHelper::normalize() shape'inde döner
            'background_image'  => $campaign->background_image,
            'transparent_image' => $campaign->transparent_image,
        ];
    }
}
