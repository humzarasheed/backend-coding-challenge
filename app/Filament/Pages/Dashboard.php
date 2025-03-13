<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BooksOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function getWidgets(): array
    {
        return [
            BooksOverview::class => [
                'class' => 'w-full', // Ensure full width is applied
            ],
        ];
    }
}
