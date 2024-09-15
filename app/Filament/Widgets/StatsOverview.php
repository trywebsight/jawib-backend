<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class StatsOverview extends BaseWidget
{

    protected function getStats(): array
    {
        return [
            Stat::make(__('users'), User::count()),
            Stat::make(__('questions'), Question::count()),
            Stat::make(__('categories'), Category::count()),
        ];
    }
}
