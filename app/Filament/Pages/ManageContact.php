<?php

namespace App\Filament\Pages;

use App\Models\Contact;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageContact extends Page
{
    protected string $view = 'filament.pages.manage-contact';

    protected static ?string $title = 'Contact';

    protected static ?string $navigationLabel = 'Contact';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Public';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return auth()->user()->can('view Contact') || auth()->user()->can('manage Contact');
    }

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->disabled(! auth()->user()->can('manage Contact'))
            ->components([
                Form::make([
                    TextInput::make('company_name')
                        ->label('Company Name')
                        ->maxLength(50),

                    TextInput::make('telephone')
                        ->label('Telephone')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(50),

                    Textarea::make('address')
                        ->label('Address')
                        ->rows(3),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s'])
                                ->visible(fn () => auth()->user()->can('manage Contact')),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage Contact'), 403, 'You do not have permission to modify Contact.');

        $data = $this->form->getState();

        $record = $this->getRecord();
        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title('Saved successfully')
            ->send();
    }

    public function getRecord(): Contact
    {
        return Contact::firstOrCreate();
    }
}
