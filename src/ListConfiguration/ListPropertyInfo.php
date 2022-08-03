<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use Mamazu\SuluMaker\Enums\Visibility;

class ListPropertyInfo
{
    public function __construct(
        public string $name,
        public Visibility $visibility,
        public bool $searchability,
        public string $translations,
        public ?string $type = null
    ) {

    }


}
