<?php

namespace Mamazu\SuluMaker\ListConfiguration;

class ConfigurationPDO
{
    public function __construct(
        public string $name,
        public string $visibility,
        public string $searchability,
        public string $translations
    ) {

    }


}
