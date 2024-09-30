<?php

namespace App\Filament\Widgets;

use App\Models\GameCategory;
use Filament\Widgets\ChartWidget;

class TopCategoriesSelectedChart extends ChartWidget
{
    protected static ?string $heading = 'Top Categories Selected';

    protected function getData(): array
    {
        // Fetch top 5 categories
        $topCategories = GameCategory::select('category_id')
            ->selectRaw('count(*) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('category')
            ->get();

        $labels = $topCategories->pluck('category.title')->toArray();
        $data = $topCategories->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Number of Selections',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}
