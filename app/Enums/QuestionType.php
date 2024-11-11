<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum QuestionType: string implements HasColor, HasIcon, HasLabel
{
    case TEXT  = 'text';
    case IMAGE = 'image';
    case AUDIO = 'video';
    case VIDEO = 'audio';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TEXT  => 'danger',
            self::IMAGE => 'success',
            self::VIDEO => 'info',
            self::AUDIO => 'warning',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TEXT  => ucfirst(__('text')),
            self::IMAGE => ucfirst(__('image')),
            self::VIDEO => ucfirst(__('video')),
            self::AUDIO => ucfirst(__('audio')),
        };
        // return ucfirst($this->value);
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::TEXT  => 'heroicon-c-document-text',
            self::IMAGE => 'heroicon-c-photo',
            self::VIDEO => 'heroicon-c-video-camera',
            self::AUDIO => 'heroicon-c-speaker-wave',
        };
    }
}
