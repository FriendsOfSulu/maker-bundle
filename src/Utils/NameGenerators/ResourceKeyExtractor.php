<?php

namespace FriendsOfSulu\MakerBundle\Utils\NameGenerators;

use Webmozart\Assert\Assert;

class ResourceKeyExtractor implements UniqueNameGenerator
{
    private const RESOURCE_KEY_CONSTANT = 'RESOURCE_KEY';

    /** @param class-string $className */
    public function getUniqueName(string $className): string
    {
        $reflection = new \ReflectionClass($className);

        $resourceKey = null;
        if ($reflection->hasConstant(self::RESOURCE_KEY_CONSTANT)) {
            $resourceKey = $reflection->getConstant(self::RESOURCE_KEY_CONSTANT);
        }

        if ($reflection->hasProperty(self::RESOURCE_KEY_CONSTANT)) {
            $resourceKey = $reflection->getProperty(self::RESOURCE_KEY_CONSTANT)->getValue();
        }

        Assert::notNull(
            $resourceKey,
            'Could not find resource key. It has to be a constant or a property on the class: ' . $className,
        );
        Assert::string(
            $resourceKey,
            'Resource key must be a "string" but got "' . \get_debug_type($resourceKey) . '" given',
        );

        return $resourceKey;
    }
}
