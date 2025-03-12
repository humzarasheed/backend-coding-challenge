<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        $roles = Role::take(2)->pluck('name', 'id');

        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                Radio::make('role')
                ->label('Select Role')
                ->options(
                    collect($roles)->mapWithKeys(function ($label, $value) {
                        return [$value => ucwords($label)];
                    })
                )
                ->required()
                ->inline()
            ]);
    }

    public function saved()
    {
        $user = $this->getUser();

        $roleId = $this->form->getState()['role'];
        $role = Role::find($roleId);

        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }
}
