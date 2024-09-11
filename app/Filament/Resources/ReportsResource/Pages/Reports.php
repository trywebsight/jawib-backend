<?php

namespace App\Filament\Resources\ReportsResource\Pages;

use App\Filament\Resources\ReportsResource;
use Filament\Resources\Pages\Page;

class Reports extends Page
{
    protected static string $resource = ReportsResource::class;

    protected static string $view = 'filament.resources.reports-resource.pages.reports';
}
