<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // Eğer model özel logAttributes tanımladıysa onları alır,
        // yoksa fillable alanları loglar.
        $attributes = property_exists($this, 'logAttributes')
            ? (array) static::$logAttributes
            : ($this->getFillable() ?: []);

        // Varsayılan log adı: modelin sınıf adı (küçük harf)
        $logName = method_exists($this, 'activityLogName')
            ? (string) $this->activityLogName()
            : strtolower(class_basename(static::class));

        return LogOptions::defaults()
            ->useLogName($logName)
            ->logOnly($attributes)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
