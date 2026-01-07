<?php

namespace App\Services;

use App\Models\Campaign;

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
            ->orderBy('priority')
            ->orderByRaw('priority is null, priority asc')
            ->get();

        return $campaigns
            ->map(fn (Campaign $campaign) => $this->toViewModel($campaign))
            ->values()
            ->all();
    }

    protected function toViewModel(Campaign $campaign): array
    {
        $baseLocale = config('app.locale', 'tr');
        $uiLocale   = app()->getLocale() ?: $baseLocale;

        $content = (array) ($campaign->content ?? []);
        $block   = $this->pickLocaleBlock($content, $uiLocale, $baseLocale);

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

    protected function pickLocaleBlock(array $content, string $uiLocale, string $baseLocale): array
    {
        if (isset($content[$uiLocale]) && is_array($content[$uiLocale])) {
            return $content[$uiLocale];
        }

        if (isset($content[$baseLocale]) && is_array($content[$baseLocale])) {
            return $content[$baseLocale];
        }

        if (! empty($content)) {
            $first = reset($content);
            if (is_array($first)) {
                return $first;
            }
        }

        return [];
    }
}
