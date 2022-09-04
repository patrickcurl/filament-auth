<?php

declare(strict_types=1);

namespace FilamentAuth\Resources\UserResource\Pages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\EditRecord;
use Phpsa\FilamentAuthentication\Events\UserUpdated;

class EditUser extends EditRecord
{
    public static function getResource() : string
    {
        return Config::get('filament-auth.resources.UserResource');
    }

    protected function mutateFormDataBeforeSave(array $data) : array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave() : void
    {
        Event::dispatch(new UserUpdated($this->record));
    }
}
