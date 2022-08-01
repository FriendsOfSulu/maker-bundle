<?php

namespace Mamazu\SuluMaker\ListConfiguration;

class ListPropertyInfo
{
    public function __construct(
        public string $name,
        public string $visibility,
        public string $searchability,
        public string $translations
    ) {

    }


}
