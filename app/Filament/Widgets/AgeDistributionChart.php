<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class AgeDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Age Demographics';

    protected function getData(): array
    {
        $users = User::selectRaw('TIMESTAMPDIFF(YEAR, bod, CURDATE()) as age')
            ->whereNotNull('bod')
            ->get();

        $ageRanges = [
            '0-9' => 0,
            '10-19' => 0,
            '20-29' => 0,
            '30-39' => 0,
            '40-49' => 0,
            '50-59' => 0,
            '60+' => 0,
        ];

        foreach ($users as $user) {
            $age = $user->age;
            if ($age <= 9) {
                $ageRanges['0-9']++;
            } elseif ($age <= 19) {
                $ageRanges['10-19']++;
            } elseif ($age <= 29) {
                $ageRanges['20-29']++;
            } elseif ($age <= 39) {
                $ageRanges['30-39']++;
            } elseif ($age <= 49) {
                $ageRanges['40-49']++;
            } elseif ($age <= 59) {
                $ageRanges['50-59']++;
            } else {
                $ageRanges['60+']++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Users',
                    'data' => array_values($ageRanges),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => array_keys($ageRanges),
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}
