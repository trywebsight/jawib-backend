<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AgeDistributionChart;
use Filament\Pages\Page;
use App\Filament\Widgets\AudienceAnalyticsWidget;
use App\Filament\Widgets\GameAnalyticsWidget;
use App\Filament\Widgets\CustomReportsWidget;
use App\Filament\Widgets\GenderDistributionChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TopCategoriesSelectedChart;
use Illuminate\Contracts\Support\Htmlable;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.reports';

    public static function getNavigationLabel(): string
    {
        return __('reports');
    }
    public function getHeading(): string
    {
        return __('reports');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            AudienceAnalyticsWidget::class,
            TopCategoriesSelectedChart::class,
            AgeDistributionChart::class,
            // GenderDistributionChart::class,
            // GameAnalyticsWidget::class,

        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
        ];
    }
}
