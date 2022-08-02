<?php

namespace Mamazu\SuluMaker\Utils;

interface UniqueNameGenerator
{
    public function getUniqueName(string $className): string;
}
