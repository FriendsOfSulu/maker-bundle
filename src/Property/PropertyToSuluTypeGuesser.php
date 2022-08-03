<?php

namespace Mamazu\SuluMaker\Property;

use DateTimeInterface;
use ReflectionProperty;

class PropertyToSuluTypeGuesser
{
    /** @return array<string, string> */
    public function getPossibleTypes(ReflectionProperty $property): array
    {
        $propertyType =$property->getType()->getName();
        if ($propertyType === 'bool') {
            return ['checkbox' => 'Renders a checkbox'];
        }

        if ($propertyType === 'string') {
            return [null => 'Renders a text field'];
        }

        if (in_array($propertyType, ['float', 'int'])) {
            return ['number' => 'Renders a number'];
        }

        if (is_a($propertyType, DateTimeInterface::class, true)) {
            return [
                'datetime' => "Renders a datetime selector",
                'date' => "Renders a date selector",
                'time' => "Renders a time selector",
            ];
        }

        return [ ];
    }
}
