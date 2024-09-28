<?php

namespace App\Filament\Widgets;

use App\Enums\TapPaymentStatusEnum;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;

class AudienceAnalyticsWidget extends BaseWidget
{
    private function totalEarning()
    {
        $total = Purchase::where('payment_status', TapPaymentStatusEnum::CAPTURED->value)
            ->join('packages', 'purchases.package_id', '=', 'packages.id')
            ->sum('packages.price');

        // Assuming you have a method or a config value to get the currency symbol

        return number_format($total, 2) . ' ' . __('KD');
    }
    private function earningsTrend()
    {
        // Query to get the sum of earnings grouped by day for the last 7 days
        $earnings = Purchase::select(
            DB::raw('DATE(purchases.created_at) as date'),
            DB::raw('SUM(packages.price) as total')
        )
            ->join('packages', 'purchases.package_id', '=', 'packages.id')
            ->where('payment_status', TapPaymentStatusEnum::CAPTURED->value)
            ->groupBy('date')
            ->orderBy('date')
            ->limit(7)
            ->pluck('total', 'date')
            ->toArray();
        // Ensure we have data for each day in the last 7 days, even if no purchases occurred
        $dates = collect(range(0, 6))->map(function ($daysAgo) {
            return Carbon::today()->subDays($daysAgo)->format('Y-m-d');
        })->reverse();

        $earningsTrend = $dates->map(function ($date) use ($earnings) {
            return $earnings[$date] ?? 0; // Return 0 if no earnings on that date
        });

        return $earningsTrend->toArray();
    }
    protected function getStats(): array
    {
        return [
            Stat::make(__(''), $this->totalEarning())
                ->description(__('Total Earnings'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->chart($this->earningsTrend()) // Example data for the chart, replace with real data if needed
                ->color('success'),
            Stat::make('Top User by Purchase', 'John Doe')
                ->description('$5,678 total spent')
                ->descriptionIcon('heroicon-m-currency-dollar'),
        ];
    }
}
