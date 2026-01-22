<?php

use App\Models\Translation;

if (! function_exists('t')) {
    function t(string $dotKey, array $replacements = [], ?string $locale = null): string
    {
        if (! str_contains($dotKey, '.')) {
            return $dotKey;
        }

        [$group, $key] = explode('.', $dotKey, 2);

        $value = Translation::getValue($group, $key, $locale)
            ?? __($dotKey)
            ?? $dotKey;

        // Placeholder replace: {key}
        if ($replacements) {
            foreach ($replacements as $k => $v) {
                $value = str_replace('{' . $k . '}', (string) $v, $value);
            }
        }

        return $value;
    }
}

