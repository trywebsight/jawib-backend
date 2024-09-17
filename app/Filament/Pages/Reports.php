<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\AudienceAnalyticsWidget;
use App\Filament\Widgets\GameAnalyticsWidget;
use App\Filament\Widgets\CustomReportsWidget;
use App\Filament\Widgets\StatsOverview;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Analytics Reports';

    protected ?string $heading = 'Reports';


    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            AudienceAnalyticsWidget::class,
            GameAnalyticsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
        ];
    }
}
