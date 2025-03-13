<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Widgets\Widget;

class BooksOverview extends Widget
{
    protected static string $view = 'filament.widgets.books-overview';

    protected function getCardData(): array
    {
        return [];
    }

    public static function getColumns(): int|string|array
    {
        return 1;
    }

    public function getViewData(): array
    {
        return [
            'books' => Book::with('author')->get(),
        ];
    }

    public function getManageBookUrl($book): string
    {
        return \App\Filament\Resources\BookResource::getUrl('manage', ['record' => $book]);
    }
}
