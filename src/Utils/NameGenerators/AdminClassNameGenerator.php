<?php

namespace Mamazu\SuluMaker\Utils\NameGenerators;

class AdminClassNameGenerator implements UniqueNameGenerator
{
    public function __construct(
        private UniqueNameGenerator $nameGenerator
    ) {}

    public function getUniqueName(string $className): string
    {
        $originalClassName =$this->nameGenerator->getUniqueName($className);
        return ucfirst(str_replace('_', '', $originalClassName));
    }
}
