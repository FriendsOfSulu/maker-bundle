<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

enum ConditionType: string
{
    case ON = 'ON';
    case WITH = 'WITH';

    /** @return array<string, string> */
    public static function descriptions(): array
    {
        return [
            self::ON->value => 'Use ON condition for the join',
            self::WITH->value => 'Use WITH condition for the join',
        ];
    }
}
