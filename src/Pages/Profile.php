<?php

declare(strict_types=1);

namespace FilamentAuth\Pages;

use Filament\Pages\Page;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Password;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Database\Eloquent\Model;

/**
 * @TODO - fix translations
 * @property \Filament\Forms\ComponentContainer $form
 */
class Profile extends Page
{
    use InteractsWithForms;
    protected static string $view                   = 'filament-auth::filament.pages.profile';
    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, string>
     */
    public array $formData;

    protected static function shouldRegisterNavigation() : bool
    {
        return false;
    }

    protected function getFormStatePath() : string
    {
        return 'formData';
    }

    protected function getFormModel() : Model
    {
        return Filament::auth()->user();
    }

    public function mount() : void
    {
        $this->form->fill([
            // @phpstan-ignore-next-line
            'name' => $this->getFormModel()->name,
            // @phpstan-ignore-next-line
            'email' => $this->getFormModel()->email,
        ]);
    }

    public function submit() : void
    {
        $data = $this->form->getState();

        $state = \array_filter([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['new_password'] ? Hash::make($data['new_password']) : null,
        ]);

        $this->getFormModel()->update($state);

        if ($data['new_password']) {
            // @phpstan-ignore-next-line
            Filament::auth()->login($this->getFormModel(), (bool) $this->getFormModel()->getRememberToken());
        }

        $this->notify('success', (string) (\__('filament::resources/pages/edit-record.messages.saved')));
    }

    public function getCancelButtonUrlProperty() : string
    {
        return static::getUrl();
    }

    protected function getBreadcrumbs() : array
    {
        return [
            \url()->current() => 'Profile',
        ];
    }

    protected function getFormSchema() : array
    {
        return [
            Section::make('General')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email Address')
                        ->required(),
                ]),
            Section::make('Update Password')
                ->columns(2)
                ->schema([
                    TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->rules(['required_with:new_password'])
                        ->currentPassword()
                        ->autocomplete('off')
                        ->columnSpan(1),
                    Grid::make()
                        ->schema([
                            TextInput::make('new_password')
                                ->label('New Password')
                                ->rules(['confirmed', Password::defaults()])
                                ->autocomplete('new-password'),
                            TextInput::make('new_password_confirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->rules([
                                    'required_with:new_password',
                                ])
                                ->autocomplete('new-password'),
                        ]),
                ]),
        ];
    }
}
