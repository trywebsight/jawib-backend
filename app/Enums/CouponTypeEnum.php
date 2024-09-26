<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CouponTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case FIXED = 'fixed';
    case PERCENT = 'percent';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FIXED => 'info',
            self::PERCENT => 'info',
        };
    }

    public function getLabel(): ?string
    {
        return ucfirst($this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FIXED => 'heroicon-s-currency-dollar',
            self::PERCENT => 'heroicon-c-percent-badge',
        };
    }
}
