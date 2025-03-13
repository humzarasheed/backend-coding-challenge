<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Models\Book;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage')
                ->label('Manage')
                ->url(fn (Book $book): string => BookResource::getUrl('manage', ['record' => $book]))
                ->icon('heroicon-o-document-text'),
            Actions\DeleteAction::make(),
        ];
    }
}
