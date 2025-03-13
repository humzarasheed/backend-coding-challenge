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
    public bool $isEditMode = false;
    public ?int $editingSectionId = null;

    public function getTitle(): string|Htmlable
    {
        return "Manage Book";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->visible(fn () => auth()->user()->can('update_book')),
        ];
    }

    public function mount($record): void
    {
        abort_unless(auth()->user()->can('update_book'), 403);

        parent::mount($record);
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

        if ($this->isEditMode && $this->editingSectionId) {
            if (!auth()->user()->can('edit_section')) {
                Notification::make()
                    ->title('You do not have permission to edit sections.')
                    ->danger()
                    ->send();
                return;
            }

            $section = Section::find($this->editingSectionId);
            if ($section) {
                $section->update([
                    'title' => $payload['title'],
                    'description' => $payload['description'] ?? null,
                ]);

                Notification::make()
                    ->title('Section updated successfully')
                    ->success()
                    ->send();
            }
        } else {
            if (!auth()->user()->can('create_section')) {
                Notification::make()
                    ->title('You do not have permission to create sections.')
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

            Notification::make()
                ->title($this->parentSectionId ? 'Subsection added successfully' : 'Section added successfully')
                ->success()
                ->send();
        }

        $this->sectionData = [];
        $this->reset('sectionData');
        $this->parentSectionId = null;
        $this->isShowingSectionForm = false;
        $this->isEditMode = false;
        $this->editingSectionId = null;
    }

    public function showSectionForm(): void
    {
        if (!auth()->user()->can('create_section')) {
            Notification::make()
                ->title('You do not have permission to create sections.')
                ->danger()
                ->send();
            return;
        }

        $this->sectionData = [];
        $this->reset('sectionData');
        $this->isEditMode = false;
        $this->editingSectionId = null;
        $this->isShowingSectionForm = true;
    }

    public function editSection(int $sectionId): void
    {
        if (!auth()->user()->can('edit_section')) {
            Notification::make()
                ->title('You do not have permission to edit sections.')
                ->danger()
                ->send();
            return;
        }

        $section = Section::find($sectionId);
        if ($section) {
            $this->sectionData = [
                'title' => $section->title,
                'description' => $section->description,
            ];
            $this->isEditMode = true;
            $this->editingSectionId = $sectionId;
            $this->parentSectionId = $section->parent_id;
            $this->isShowingSectionForm = true;
        }
    }

    public function deleteSection(int $sectionId): void
    {
        if (!auth()->user()->can('delete_section')) {
            Notification::make()
                ->title('You do not have permission to delete sections.')
                ->danger()
                ->send();
            return;
        }

        $section = Section::find($sectionId);
        if ($section) {
            $section->delete();

            Notification::make()
                ->title('Section deleted successfully')
                ->success()
                ->send();
        }
    }

    public function addSubsection(int $sectionId): void
    {
        if (!auth()->user()->can('create_section')) {
            Notification::make()
                ->title('You do not have permission to create sections.')
                ->danger()
                ->send();
            return;
        }

        $this->parentSectionId = $sectionId;
        $this->isShowingSectionForm = true;
        $this->isEditMode = false;
        $this->editingSectionId = null;
        $this->sectionData = [];
        $this->reset('sectionData');
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

        $html .= "<div class='flex space-x-2'>";

        if (auth()->user()->can('create_section')) {
            $html .= "<button type='button' wire:click='addSubsection({$section->id})'
                      class='px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded'>
                      Add Subsection</button>";
        }

        if (auth()->user()->can('edit_section')) {
            $html .= "<button type='button' wire:click='editSection({$section->id})'
                      class='px-3 py-1 text-sm bg-blue-200 hover:bg-blue-300 rounded'>
                      Edit</button>";
        }

        if (auth()->user()->can('delete_section')) {
            $html .= "<button type='button' wire:click='deleteSection({$section->id})'
                      class='px-3 py-1 text-sm bg-red-200 hover:bg-red-300 rounded'
                      onclick=\"return confirm('Are you sure you want to delete this section?')\">
                      Delete</button>";
        }

        $html .= "</div>"; // End of buttons container

        $html .= "</div>"; // End of header
        $html .= "<small class='text-gray-500'>Added By: {$section->user->name}</small>";

        if (!empty($section->description)) {
            $html .= "<div class='mt-1 text-sm text-gray-600'>{$section->description}</div>";
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
                    ->columns(2)
                    ->visible(fn () => auth()->user()->can('update_book')),

                FormSection::make('Sections Management')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('addSection')
                                ->label('Add New Section')
                                ->button()
                                ->color('primary')
                                ->visible(fn () => !$this->isShowingSectionForm && auth()->user()->can('create_section'))
                                ->action('showSectionForm'),
                        ]),

                        FormSection::make(fn () => $this->isEditMode
                            ? 'Edit Section'
                            : ($this->parentSectionId ? 'Add Subsection' : 'Add New Section'))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->default(fn () => $this->sectionData['title'] ?? '')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->sectionData['title'] = $state;
                                    }),
                                Forms\Components\RichEditor::make('description')
                                    ->default(fn () => $this->sectionData['description'] ?? '')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->sectionData['description'] = $state;
                                    }),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('saveSection')
                                        ->label(fn () => $this->isEditMode
                                            ? 'Update Section'
                                            : ($this->parentSectionId ? 'Save Subsection' : 'Save Section'))
                                        ->button()
                                        ->color('success')
                                        ->action('saveSection'),

                                    Forms\Components\Actions\Action::make('cancelEdit')
                                        ->label('Cancel')
                                        ->button()
                                        ->color('secondary')
                                        ->action(function () {
                                            $this->sectionData = [];
                                            $this->reset('sectionData');
                                            $this->parentSectionId = null;
                                            $this->isShowingSectionForm = false;
                                            $this->isEditMode = false;
                                            $this->editingSectionId = null;
                                        }),
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
