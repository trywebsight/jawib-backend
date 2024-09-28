<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TapPaymentStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case INITIATED = 'INITIATED';
    case CAPTURED = 'CAPTURED';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case CANCELLED = 'CANCELLED';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CAPTURED => 'success',
            self::INITIATED => 'info',
            self::CANCELLED, self::FAILED, self::DECLINED => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CAPTURED => 'heroicon-o-check-circle',
            self::INITIATED => 'heroicon-o-clock',
            self::CANCELLED, self::FAILED, self::DECLINED => 'heroicon-o-x-circle',
        };
    }
}
