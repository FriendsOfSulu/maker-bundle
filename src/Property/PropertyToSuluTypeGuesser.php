<?php

namespace Mamazu\SuluMaker\Property;

use DateTimeInterface;
use ReflectionProperty;
use ReflectionNamedType;

class PropertyToSuluTypeGuesser
{
    /** @return array<string, string> */
    public function getPossibleTypes(ReflectionProperty $property): array
    {
        /** @var ReflectionNamedType|null $propertyType */
        $propertyType =$property->getType();
        $typeName = $propertyType?->getName();
        if ($typeName === 'bool') {
            return ['checkbox' => 'Renders a checkbox'];
        }

        if ($typeName === 'string') {
            return [null => 'Renders a text field'];
        }

        if (in_array($typeName, ['float', 'int'])) {
            return ['number' => 'Renders a number'];
        }

        if (is_a($typeName, DateTimeInterface::class, true)) {
            return [
                'datetime' => "Renders a datetime selector",
                'date' => "Renders a date selector",
                'time' => "Renders a time selector",
            ];
        }

        return [ ];
    }
}
