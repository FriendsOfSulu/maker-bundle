<?php

namespace FriendsOfSulu\MakerBundle\Maker\AdminConfigurationMaker;

/** @internal */
class AdminGeneratorSettings
{
    public bool $shouldAddMenuItem = true;

    public bool $shouldHaveEditForm = true;

    public bool $shouldHaveAddForm = true;

    public bool $shouldHaveReferences = true;

    public string $slug;

    public string $formKey;

    public string $listKey;

    public function __construct(
        public string $resourceKey
    ) {
        $this->slug = '/' . $resourceKey;
        $this->formKey = $resourceKey;
        $this->listKey = $resourceKey;
    }

    /** @var array<string> */
    public array $listToolbarActions = [
        'add', 'delete', 'export',
    ];

    /** @var array<string> */
    public array $formToolbarActions = [
        'save', 'delete',
    ];

    /** @var array<string> */
    public array $permissionTypes = [];
}
