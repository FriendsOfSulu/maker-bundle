<?php

namespace Mamazu\SuluMaker\Objects;

class ListConfiguration
{
    public function __construct(
        public string $name,
        public string $visibility,
        public string $searchability,
        public string $translations
    ) {

    }


}
