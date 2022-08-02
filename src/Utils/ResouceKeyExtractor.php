<?php

namespace Mamazu\SuluMaker\Utils;

use ReflectionClass;
use Webmozart\Assert\Assert;

class ResouceKeyExtractor implements UniqueNameGenerator
{
    /** @param class-string $className */
    public function getUniqueName(string $className): string
    {
        $reflection = new ReflectionClass($className);

        $resourceKey = $reflection->getProperty('RESOURCE_KEY')->getValue();
        Assert::string($resourceKey, 'Resource key must be a "string" but got "'. get_debug_type($resourceKey). '" given');

        return $resourceKey;
    }
}
