<?php

namespace FriendsOfSulu\MakerBundle\Utils\NameGenerators;

interface UniqueNameGenerator
{
    public function getUniqueName(string $className): string;
}
