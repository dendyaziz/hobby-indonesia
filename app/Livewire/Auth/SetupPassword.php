<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SetupPassword extends SimplePage
{
    public string $user;

    public ?array $data = [];

    public function mount(string $user): void
    {
        if (! request()->hasValidSignature()) {
            abort(401, 'Invalid or expired signature.');
        }

        $this->user = $user;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->rule(PasswordRule::default())
                    ->same('passwordConfirmation')
                    ->validationAttribute('password'),
                TextInput::make('passwordConfirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required()
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $userModel = User::findOrFail($this->user);
        $userModel->update([
            'password' => Hash::make($data['password']),
            'activated_at' => now(),
        ]);

        Notification::make()
            ->title('Password set successfully')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.auth.login');
    }

    public function getHeading(): string
    {
        return 'Setup Password';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('submit')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
        ];
    }

    protected function getSubmitFormAction(): Action
    {
        return Action::make('submit')
            ->label('Set Password')
            ->submit('submit');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
