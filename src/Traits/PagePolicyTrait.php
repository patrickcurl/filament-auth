<?php

declare(strict_types=1);

namespace FilamentAuth\Traits;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;

trait PagePolicyTrait
{
    protected static bool $shouldRegisterNavigation;

    public function mount() : void
    {
        \abort_unless(static::canView(), 403);
    }

    protected static function canView() : bool
    {
        if (static::getPolicy() === null) {
            return true;
        }

        return static::getPolicy()->viewAny(Filament::auth()->user());
    }

    protected static function shouldRegisterNavigation() : bool
    {
        return static::canView() && !empty(static::$shouldRegisterNavigation);
    }

    protected static function getPolicy()
    {
        return Gate::getPolicyFor(static::class);
    }
}
