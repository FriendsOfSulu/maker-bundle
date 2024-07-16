<?php

namespace FriendsOfSulu\MakerBundle\Enums;

use MyCLabs\Enum\Enum;

/**
 * @template-extends Enum<string>
 */
class Visibility extends Enum
{
    public const YES = 'yes';
    public const NO = 'no';
    public const ALWAYS = 'always';
    public const NEVER_ = 'never';

    public function isVisible(): bool
    {
        return in_array($this->value, [self::YES, self::ALWAYS], true);
    }

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            self::YES => "Show the property",
            self::NO => "Hide the property",
            self::ALWAYS => "Same as yes but the user can't choose to hide the property",
            self::NEVER_ => "Same as no but the user can't choose to show the property",
        ];
    }
}
