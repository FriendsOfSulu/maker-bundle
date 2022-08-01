<?php

namespace Mamazu\SuluMaker\ListConfiguration;

class ListPropertyInfo
{
    public function __construct(
        public string $name,
        public bool $visibility,
        public bool $searchability,
        public string $translations,
        public ?string $type = null
    ) {

    }


}
