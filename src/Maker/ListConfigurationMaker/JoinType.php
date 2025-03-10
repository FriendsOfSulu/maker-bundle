<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

/** @internal */
enum JoinType: string
{
    case LEFT = 'LEFT';
    case RIGHT = 'RIGHT';
    case INNER = 'INNER';

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            self::LEFT->value => 'Left join (all entries from left table with optional entries from the right table)',
            self::RIGHT->value => 'Left join (all entries from right table with optional entries from the left table)',
            self::INNER->value => 'Inner join (only entries that are in both tables',
        ];
    }
}
