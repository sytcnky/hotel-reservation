<?php

use App\Models\Translation;

if (! function_exists('t')) {
    function t(string $dotKey, array $replacements = [], ?string $locale = null): string
    {
        if (! str_contains($dotKey, '.')) {
            return $dotKey;
        }

        [$group, $key] = explode('.', $dotKey, 2);

        return Translation::getValue($group, $key, $locale)
            ?? __($dotKey)   // Laravel lang fallback (varsa)
            ?? $dotKey;      // En son plain key
    }
}
