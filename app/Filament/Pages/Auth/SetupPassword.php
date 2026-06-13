<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Locked;

class SetupPassword extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;

    #[Locked]
    public string $user;

    public ?array $data = [];

    public function mount(string $user): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $userModel = User::findOrFail($user);

        if ($userModel->password !== null) {
            abort(403, 'Password has already been set. This invitation link is no longer valid.');
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
                    ->revealable()
                    ->required()
                    ->rule(PasswordRule::default())
                    ->same('passwordConfirmation')
                    ->validationAttribute('password'),
                TextInput::make('passwordConfirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->revealable()
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
            'password'     => Hash::make($data['password']),
            'activated_at' => now(),
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Password set successfully.')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.auth.login');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Setup Password';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Setup Password';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
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
        return [$this->getSubmitFormAction()];
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
