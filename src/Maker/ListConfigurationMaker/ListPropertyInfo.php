<?php

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

use FriendsOfSulu\MakerBundle\Enums\Visibility;

/** @internal */
class ListPropertyInfo
{
    public function __construct(
        public string $name,
        public Visibility $visibility,
        public bool $searchability,
        public string $translations,
        public ?string $type = null,
    ) {
    }
}
