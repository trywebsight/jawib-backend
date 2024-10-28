<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StoreOrderStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::PROCESSING => 'info',
            self::CANCELLED, self::FAILED => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COMPLETED => 'heroicon-o-check-circle',
            self::PROCESSING => 'heroicon-o-clock',
            self::CANCELLED, self::FAILED => 'heroicon-o-x-circle',
        };
    }
}
