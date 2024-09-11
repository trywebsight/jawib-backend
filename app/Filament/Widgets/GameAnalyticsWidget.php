<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class GameAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Game Analytics';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'played times',
                    'data' => [2, 10, 21, 7, 16],
                ],
            ],
            'labels' => ['أعلام', 'من اللاعب', 'فن عربي', 'فن اجنبي'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
