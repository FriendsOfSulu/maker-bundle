<?php

namespace FriendsOfSulu\MakerBundle\Enums;

enum Visibility: string
{
    case YES = 'yes';
    case NO = 'no';
    case ALWAYS = 'always';
    case NEVER_ = 'never';

    public function isVisible(): bool
    {
        return \in_array($this, [self::YES, self::ALWAYS], true);
    }

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            self::YES->value => 'Show the property',
            self::NO->value => 'Hide the property',
            self::ALWAYS->value => "Same as yes but the user can't choose to hide the property",
            self::NEVER_->value => "Same as no but the user can't choose to show the property",
        ];
    }
}
