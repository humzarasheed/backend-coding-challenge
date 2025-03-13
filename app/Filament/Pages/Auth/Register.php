<?php

namespace App\Filament\Pages\Auth;

use App\Enums\RoleEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Spatie\Permission\Models\Role;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

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

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();
        $roleId = $data['role'];
        unset($data['role']);

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        $role = Role::find($roleId);
        if ($role) {
            $user->assignRole($role);

            if ($role->name == RoleEnum::AUTHOR->value) {
                $allPermissions = Permission::all();
                $user->syncPermissions($allPermissions);
            }
        }

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

}
