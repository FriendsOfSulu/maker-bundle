<?php

namespace FriendsOfSulu\MakerBundle\Property;

final class PropertyToSuluTypeGuesser implements PropertyToSuluTypeGuesserInterface
{
    public function getPossibleTypes(string $doctrineType): array
    {
        if ('bool' === $doctrineType) {
            return ['checkbox' => 'Renders a checkbox'];
        }

        if (\in_array($doctrineType, ['text', 'string'], true)) {
            return [null => 'Renders a text field'];
        }

        if (\in_array($doctrineType, ['float', 'int'], true)) {
            return ['number' => 'Renders a number'];
        }

        if (\in_array($doctrineType, ['datetime', 'datetime_immutable'], true)) {
            return [
                'datetime' => 'Renders a datetime selector',
                'date' => 'Renders a date selector',
                'time' => 'Renders a time selector',
            ];
        }

        return [];
    }
}
