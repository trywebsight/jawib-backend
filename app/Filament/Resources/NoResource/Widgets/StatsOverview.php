<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Enums\TapStatusEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{

    private function totalEarning()
    {
        $total = Transaction::where('payment_status', TapStatusEnum::CAPTURED)
            ->join('packages', 'transactions.package_id', '=', 'packages.id')
            ->sum('packages.price');

        // Assuming you have a method or a config value to get the currency symbol

        return number_format($total, 2) . ' ' . __('KD');
    }
    private function earningsTrend()
    {
        // Query to get the sum of earnings grouped by day for the last 7 days
        $earnings = Transaction::select(
            DB::raw('DATE(transactions.created_at) as date'),
            DB::raw('SUM(packages.price) as total')
        )
            ->join('packages', 'transactions.package_id', '=', 'packages.id')
            ->where('payment_status', TapStatusEnum::CAPTURED)
            ->groupBy('date')
            ->orderBy('date')
            ->limit(7)
            ->pluck('total', 'date')
            ->toArray();
        // Ensure we have data for each day in the last 7 days, even if no transactions occurred
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
            Stat::make(__('users'), User::count()),
            Stat::make(__('questions'), Question::count()),
            Stat::make(__('categories'), Category::count()),
        ];
    }
}
