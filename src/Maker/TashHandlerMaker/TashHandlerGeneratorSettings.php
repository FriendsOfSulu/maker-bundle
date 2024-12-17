<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\TashHandlerMaker;

class TashHandlerGeneratorSettings
{
    public function __construct(
        public string $resourceClassToTrash,
        public bool $shouldHaveRestore,
    ) {
    }
}
