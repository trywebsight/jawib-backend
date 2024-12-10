<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NotificationRecipientsTypesEnum: string implements HasColor, HasIcon, HasLabel
{
    case ALL = 'all';
    case SELECTED = 'selected';
    // case GUESTS = 'guests';
    // case REGISTERED = 'registered';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ALL  => 'success',
            self::SELECTED => 'success',
            // self::GUESTS  => 'success',
            // self::REGISTERED => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ALL => ucfirst(__('all')),
            self::SELECTED => ucfirst(__('selected users')),
            // self::GUESTS  => ucfirst(__('guests')),
            // self::REGISTERED => ucfirst(__('registered')),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ALL => 'heroicon-m-users',
            self::SELECTED => 'heroicon-m-users',
            // self::GUESTS  => 'heroicon-m-users',
            // self::REGISTERED  => 'heroicon-m-users',
        };
    }
}
