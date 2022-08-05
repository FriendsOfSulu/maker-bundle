<?php

namespace Mamazu\SuluMaker\AdminConfiguration;

class AdminGeneratorSettings
{
    public bool $shouldAddMenuItem = false;

    public bool $shouldHaveEditForm = true;

    public bool $shouldHaveAddForm = true;

    public string $slug;

    public string $formKey;

    public string $listKey;

    /** @var array<string> */
    public array $listToolbarActions = [
        'add', 'delete', 'export'
    ];

    /** @var array<string> */
    public array $formToolbarActions = [
        'save', 'delete'
    ];

    /** @var array<string> */
    public array $permissionTypes = [];
}
