<?php

namespace App\Support\Mail;

use App\Support\Helpers\LocaleHelper;
use Illuminate\Mail\Mailable;

class MailDefaults
{
    /**
     * Apply configured From (icr.mail.from) if present.
     */
    public static function applyFrom(Mailable $mailable): void
    {
        $fromAddress = (string) config('icr.mail.from.address');
        $fromName    = (string) config('icr.mail.from.name');

        if ($fromAddress !== '') {
            $mailable->from($fromAddress, $fromName);
        }
    }

    /**
     * Canonical locale for mail sending (record-based).
     * Contract: record->locale is authoritative; normalize it.
     */
    public static function mailLocale(?string $locale): string
    {
        return LocaleHelper::normalizeCode($locale);
    }

    /**
     * Idempotency TTL in days (single standard).
     */
    public static function idempotencyDays(): int
    {
        $days = (int) config('icr.mail.idempotency_days', 30);
        return $days > 0 ? $days : 30;
    }
}
