<?php

namespace Mamazu\SuluMaker\Utils\NameGenerators;

interface UniqueNameGenerator
{
    public function getUniqueName(string $className): string;
}
