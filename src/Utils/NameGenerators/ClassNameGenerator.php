<?php

namespace FriendsOfSulu\MakerBundle\Utils\NameGenerators;

class ClassNameGenerator implements UniqueNameGenerator
{
    public function __construct(
        private UniqueNameGenerator $nameGenerator
    ) {
    }

    public function getUniqueName(string $className): string
    {
        $originalClassName = $this->nameGenerator->getUniqueName($className);

        return \ucfirst(\str_replace('_', '', $originalClassName));
    }
}
