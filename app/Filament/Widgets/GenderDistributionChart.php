<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class GenderDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Gender Demographics';

    protected function getData(): array
    {
        $genders = User::whereNotNull('gender')
            ->select('gender')
            ->selectRaw('count(*) as total')
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        return [
            'datasets' => [
                [
                    'data' => array_values($genders),
                    'backgroundColor' => ['#6366f1', '#ef4444', '#10b981'], // Customize colors as needed
                ],
            ],
            'labels' => array_keys($genders),
        ];
    }


    protected function getType(): string
    {
        return 'doughnut';
    }
}
