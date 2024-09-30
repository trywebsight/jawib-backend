<?php

namespace App\Filament\Widgets;

use App\Enums\TapPaymentStatusEnum;
use App\Models\Game;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\User;

class AudienceAnalyticsWidget extends BaseWidget
{
    private function totalEarning()
    {
        $total = Order::where('payment_status', TapPaymentStatusEnum::CAPTURED->value)
            ->sum('total');

        return number_format($total, 2) . ' ' . strtoupper(__('KD'));
    }
    private function earningsTrend()
    {
        // Query to get the sum of earnings grouped by day for the last 7 days
        $earnings = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total')
        )
            ->where('payment_status', TapPaymentStatusEnum::CAPTURED->value)
            ->where('created_at', '>=', Carbon::today()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
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

    private function topUserByPurchase()
    {
        $topUserData = Order::where('payment_status', TapPaymentStatusEnum::CAPTURED->value)
            ->select('user_id', DB::raw('SUM(total) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->first();

        if ($topUserData) {
            $user = User::find($topUserData->user_id);
            if ($user) {
                return [
                    'name' => $user->name,
                    'total_spent' => $topUserData->total_spent,
                ];
            }
        }

        return null;
    }


    protected function getStats(): array
    {
        $topUser = $this->topUserByPurchase();

        if ($topUser) {
            $topUserName = $topUser['name'];
            $totalSpent = number_format($topUser['total_spent'], 2) . ' ' . __('KD');
        } else {
            $topUserName = __('No Data');
            $totalSpent = '';
        }

        return [
            Stat::make(__('Total Earnings'), $this->totalEarning())
                ->description(__('Total Earnings'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->chart($this->earningsTrend())
                ->color('success'),

            Stat::make(__('Top User by Purchase'), $topUserName)
                ->description($totalSpent . ' ' . __('total spent'))
                ->descriptionIcon('heroicon-m-currency-dollar'),

            Stat::make(__(''), Game::count())
                ->description(__('Number of games played'))
                // ->descriptionIcon('heroicon-o-currency-dollar')
                // ->chart($this->earningsTrend()) // Example data for the chart, replace with real data if needed
                ->color('success'),
        ];
    }
}
