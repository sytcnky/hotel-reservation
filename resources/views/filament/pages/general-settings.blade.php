{{-- resources/views/filament/pages/manage-coupon-assignments.blade.php --}}

<x-filament-panels::page>
    {{-- Tüm form ve butonlar Schemas tarafında tanımlı --}}
    {{ $this->form }}

    {{-- Önizleme / Sonuç --}}
    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-3">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
            {{ __('admin.coupons.bulk.preview_title') }}
        </h3>

        @if ($hasApplyResult)
        {{-- Apply sonrası özet --}}
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ __('admin.coupons.bulk.result_summary_title') }}
        </p>

        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('admin.coupons.bulk.result_total') }}
                </dt>
                <dd class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $resultTotal }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('admin.coupons.bulk.result_inserted') }}
                </dt>
                <dd class="font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ $resultInserted }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('admin.coupons.bulk.result_updated') }}
                </dt>
                <dd class="font-semibold text-blue-600 dark:text-blue-400">
                    {{ $resultUpdated }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('admin.coupons.bulk.result_skipped') }}
                </dt>
                <dd class="font-semibold text-orange-600 dark:text-orange-400">
                    {{ $resultSkipped }}
                </dd>
            </div>
        </dl>
        @elseif ($hasRunDryRun)
        {{-- Dry-run önizleme --}}
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ __('admin.coupons.bulk.preview_count', ['count' => $dryRunCount]) }}
        </p>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">ID</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Ad Soyad</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">E-posta</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($dryRunSample as $row)
                <tr>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['id'] }}</td>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['name'] }}</td>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['email'] }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        {{-- Henüz hiçbir şey çalışmadıysa --}}
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('admin.coupons.bulk.preview_empty') }}
        </p>
        @endif
    </div>
</x-filament-panels::page>
