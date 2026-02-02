{{-- resources/views/partials/alerts.blade.php --}}

@php
    /**
     * Global Alerts + Notices Contract (LEVEL_ZORUNLU)
     *
     * Tek otorite: bu partial.
     *
     * Kaynaklar:
     * - session('notices')
     *   - ['code'=>string,'level'=>string,'params'=>array?]        (tek)
     *   - [ ['code'=>...,'level'=>...,'params'=>...], ... ]        (liste)
     * - $errors->first('global') -> string code                    => level=err
     *
     * Kurallar:
     * - Level zorunlu (err|warn|notice|ok)
     * - Controller/Service HTML/string üretmez; value daima code (+ opsiyonel params).
     * - Render: t(code, params)
     */

    /** @var array<int,string> */
    $levelOrder = ['err', 'warn', 'notice', 'ok'];

    /**
     * @param mixed $v
     * @return array<int,array{code:string,params:array,level:string}>
     */
    $coerceNotices = function ($v): array {
        $out = [];

        if (is_array($v)) {
            // Tek notice objesi mi?
            if (array_key_exists('code', $v) || array_key_exists('level', $v)) {
                $code   = is_string($v['code'] ?? null) ? trim((string) $v['code']) : '';
                $level  = is_string($v['level'] ?? null) ? trim((string) $v['level']) : '';
                $params = is_array($v['params'] ?? null) ? $v['params'] : [];

                if ($code !== '' && in_array($level, ['err', 'warn', 'notice', 'ok'], true)) {
                    $out[] = ['code' => $code, 'params' => $params, 'level' => $level];
                }

                return $out;
            }

            // Liste mi?
            foreach ($v as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $code   = is_string($row['code'] ?? null) ? trim((string) $row['code']) : '';
                $level  = is_string($row['level'] ?? null) ? trim((string) $row['level']) : '';
                $params = is_array($row['params'] ?? null) ? $row['params'] : [];

                if ($code === '' || ! in_array($level, ['err', 'warn', 'notice', 'ok'], true)) {
                    continue;
                }

                $out[] = ['code' => $code, 'params' => $params, 'level' => $level];
            }

            return $out;
        }

        return $out;
    };

    /**
     * @param array{code:string,params:array,level:string} $n
     */
    $classForNotice = function (array $n): string {
        return match ($n['level'] ?? 'notice') {
            'ok'     => 'alert-success',
            'err'    => 'alert-danger',
            'warn'   => 'alert-warning',
            'notice' => 'alert-info',
            default  => 'alert-secondary',
        };
    };

    $notices = [];

    // 1) notices (tek otorite)
    $notices = array_merge($notices, $coerceNotices(session('notices')));

    // 2) validation global fallback => err
    if (isset($errors) && $errors->has('global')) {
        $notices[] = [
            'code'   => (string) $errors->first('global'),
            'params' => [],
            'level'  => 'err',
        ];
    }

    // trim + dedupe (code + params + level)
    $normalized = [];

    foreach ($notices as $n) {
        $code = is_string($n['code'] ?? null) ? trim((string) $n['code']) : '';
        if ($code === '') {
            continue;
        }

        $params = is_array($n['params'] ?? null) ? $n['params'] : [];
        $level  = is_string($n['level'] ?? null) ? trim((string) $n['level']) : '';

        if (! in_array($level, ['err', 'warn', 'notice', 'ok'], true)) {
            continue;
        }

        $paramsKey = md5(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $key       = $level . '|' . $code . '|' . $paramsKey;

        if (isset($normalized[$key])) {
            continue;
        }

        $normalized[$key] = [
            'code'   => $code,
            'params' => $params,
            'level'  => $level,
        ];
    }

    $notices = array_values($normalized);

    // Stable ordering by level (err -> warn -> notice -> ok), preserving insertion order per level
    if (! empty($notices)) {
        $bucketed = ['err' => [], 'warn' => [], 'notice' => [], 'ok' => []];

        foreach ($notices as $n) {
            $bucketed[$n['level']][] = $n;
        }

        $notices = array_merge($bucketed['err'], $bucketed['warn'], $bucketed['notice'], $bucketed['ok']);
    }
@endphp

@if (!empty($notices))
    <div class="container py-3">
        @foreach ($notices as $notice)
            @php $alertClass = $classForNotice($notice); @endphp

            <div class="alert {{ $alertClass }} mb-2">
                {{ t($notice['code'], $notice['params'] ?? []) }}
            </div>
        @endforeach
    </div>
@endif
