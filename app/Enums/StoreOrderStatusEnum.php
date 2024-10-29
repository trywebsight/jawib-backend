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
    // case FAILED = 'FAILED';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::PENDING => 'info',
            self::PROCESSING => 'warning',
            self::CANCELLED => 'danger',
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
            self::PROCESSING, self::PENDING => 'heroicon-o-clock',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }
}
