<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Models\Section;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Section as FormSection;

class ManageBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    public ?array $sectionData = [];
    public ?int $parentSectionId = null;
    public bool $isShowingSectionForm = false;

    public function getTitle(): string|Htmlable
    {
        return "Manage Book";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function saveSection(): void
    {
        $payload = $this->sectionData;

        if (empty($payload['title']) || empty($payload['description'])) {
            Notification::make()
                ->title('Title or Description is missing.')
                ->danger()
                ->send();
            return;
        }

        Section::create([
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'book_id' => $this->record->id,
            'user_id' => Auth::id(),
            'parent_id' => $this->parentSectionId,
        ]);

        $this->sectionData = [];
        $this->reset('sectionData');
        $this->parentSectionId = null;
        $this->isShowingSectionForm = false;

        Notification::make()
            ->title($this->parentSectionId ? 'Subsection added successfully' : 'Section added successfully')
            ->success()
            ->send();
    }

    public function showSectionForm(): void
    {
        $this->sectionData = [];
        $this->reset('sectionData');

        $this->isShowingSectionForm = true;
    }

    public function addSubsection(int $sectionId): void
    {
        $this->parentSectionId = $sectionId;
        $this->isShowingSectionForm = true;
    }

    public function getSections()
    {
        return Section::where('book_id', $this->record->id)
            ->whereNull('parent_id')
            ->with('subsections.subsections')
            ->get();
    }

    protected function renderSection($section, int $level = 0): string
    {
        $indent = str_repeat('', $level);

        $html = "<div class='p-3 border rounded-lg mb-3'>";
        $html .= "<div class='flex justify-between items-center'>";
        $html .= "<h3 class='text-lg font-medium'>{$indent}{$section->title}</h3>";
        $html .= "<button type='button' wire:click='addSubsection({$section->id})'
                  class='px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded'>
                  Add Subsection</button>";
        $html .= "</div>";
        $html .= "<small class='text-gray-500'>Added By: {$section->user->name}</small>";

        if (!empty($section->description)) {
            $html .= "<p class='mt-1 text-sm text-gray-600'>{$section->description}</p>";
        }

        // render subsections
        if ($section->subsections && $section->subsections->isNotEmpty()) {
            $html .= "<div class='mt-3 pl-4 pt-2 border-l'>";

            foreach ($section->subsections as $subsection) {
                $html .= $this->renderSection($subsection, $level + 1);
            }

            $html .= "</div>";
        }

        $html .= "</div>";

        return $html;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('Book Details')
                    ->schema(BookResource::getFormSchema())
                    ->columns(2),

                FormSection::make('Sections Management')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('addSection')
                                ->label('Add New Section')
                                ->button()
                                ->color('primary')
                                ->visible(fn () => !$this->isShowingSectionForm)
                                ->action('showSectionForm'),
                        ]),

                        FormSection::make(fn () => $this->parentSectionId ? 'Add Subsection' : 'Add New Section')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->sectionData['title'] = $state;
                                    }),
                                Forms\Components\RichEditor::make('description')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->sectionData['description'] = $state;
                                    }),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('saveSection')
                                        ->label(fn () => $this->parentSectionId ? 'Save Subsection' : 'Save Section')
                                        ->button()
                                        ->color('success')
                                        ->action('saveSection'),
                                ]),
                            ])
                            ->visible(fn () => $this->isShowingSectionForm),

                        Forms\Components\Placeholder::make('sectionsPlaceholder')
                            ->label('Existing Sections')
                            ->content(function () {
                                $sections = $this->getSections();

                                if ($sections->isEmpty()) {
                                    return 'No sections added yet.';
                                }

                                $html = '<div class="space-y-2">';

                                foreach ($sections as $section) {
                                    $html .= $this->renderSection($section);
                                }

                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->collapsible(),
            ]);
    }
}
