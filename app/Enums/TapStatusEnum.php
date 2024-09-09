<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TapStatusEnum extends Enum
{
    const INITIATED = 'INITIATED';
    const CAPTURED = 'CAPTURED';
    const NOT_CAPTURED = 'NOT CAPTURED';
    const DECLINED = 'DECLINED';
    const CANCELLED = 'CANCELLED';
}
