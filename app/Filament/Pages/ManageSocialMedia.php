<?php

namespace App\Filament\Pages;

use App\Models\SocialMedia;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

/**
 * @property-read Schema $form
 */
class ManageSocialMedia extends Page
{
    protected string $view = 'filament.pages.manage-social-media';

    protected static ?string $title = 'Social Media';

    protected static ?string $navigationLabel = 'Social Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?int $navigationSort = 10;

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
            ->components([
                Form::make([
                    TextInput::make('facebook')
                        ->label('Facebook')
                        ->maxLength(100)
                        ->helperText('Input account username or URL')
                        ->rules([
                            fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                $value = trim($value);
                                if (empty($value)) return;
                                if (filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, 'facebook.com') || str_contains($value, 'fb.com')) {
                                    if (!preg_match('/^(https?:\/\/)?(www\.)?(facebook\.com|fb\.com)\/[a-zA-Z0-9.%\/\-\?=&_]+$/i', $value)) {
                                        $fail('The Facebook URL is not valid.');
                                    }
                                } else {
                                    if (!preg_match('/^[a-zA-Z0-9.]{5,50}$/', $value)) {
                                        $fail('The Facebook username must be at least 5 alphanumeric characters or dots.');
                                    }
                                }
                            }
                        ]),

                    TextInput::make('instagram')
                        ->label('Instagram')
                        ->maxLength(100)
                        ->helperText('Input account @username or URL')
                        ->rules([
                            fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                $value = trim($value);
                                if (empty($value)) return;
                                if (filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, 'instagram.com')) {
                                    if (!preg_match('/^(https?:\/\/)?(www\.)?instagram\.com\/[a-zA-Z0-9._%\-\?=&_]+\/?$/i', $value)) {
                                        $fail('The Instagram URL is not valid.');
                                    }
                                } else {
                                    $username = ltrim($value, '@');
                                    if (!preg_match('/^[a-zA-Z0-9._]{1,30}$/', $username) || str_ends_with($username, '.')) {
                                        $fail('The Instagram username must contain only letters, numbers, periods, and underscores, up to 30 characters, and cannot end with a period.');
                                    }
                                }
                            }
                        ]),

                    TextInput::make('youtube')
                        ->label('YouTube')
                        ->maxLength(100)
                        ->helperText('Input channel ID or URL')
                        ->rules([
                            fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                $value = trim($value);
                                if (empty($value)) return;
                                if (filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, 'youtube.com') || str_contains($value, 'youtu.be')) {
                                    if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/[a-zA-Z0-9.@%\/\-\?=&_]+$/i', $value)) {
                                        $fail('The YouTube URL is not valid.');
                                    }
                                } else {
                                    if (str_starts_with($value, '@')) {
                                        if (!preg_match('/^@[a-zA-Z0-9._\-]{3,30}$/', $value)) {
                                            $fail('The YouTube handle must start with @ and be 3 to 30 characters long.');
                                        }
                                    } else {
                                        if (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $value)) {
                                            $fail('The YouTube channel ID or username is not valid.');
                                        }
                                    }
                                }
                            }
                        ]),

                    TextInput::make('x')
                        ->label('X (formerly Twitter)')
                        ->maxLength(100)
                        ->helperText('Input account @username or URL')
                        ->rules([
                            fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                $value = trim($value);
                                if (empty($value)) return;
                                if (filter_var($value, FILTER_VALIDATE_URL) || str_contains($value, 'twitter.com') || str_contains($value, 'x.com')) {
                                    if (!preg_match('/^(https?:\/\/)?(www\.)?(twitter\.com|x\.com)\/[a-zA-Z0-9_]{1,15}\/?$/i', $value)) {
                                        $fail('The X URL is not valid.');
                                    }
                                } else {
                                    $username = ltrim($value, '@');
                                    if (!preg_match('/^[a-zA-Z0-9_]{1,15}$/', $username)) {
                                        $fail('The X username must contain only letters, numbers, and underscores, up to 15 characters.');
                                    }
                                }
                            }
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
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

    public function getRecord(): SocialMedia
    {
        return SocialMedia::firstOrCreate();
    }
}
