# Sulu Maker Bundle

This package adds makers for Sulu Configurations (eg. list or form XML).

## How to install
This package is currently not available on composer so the installation is a little harder:

Add this to the `composer.json` file
```json
"repositories":[
    {
        "type": "vcs",
        "url": "git@github.com:mamazu/sulu-maker.git"
    }
]
```

And then run
```bash
composer require --dev mamazu/sulu-maker
```

## How to use
This plugin needs the configuration directory of Sulu to be under `%kernel.proejct_dir%/config/` which for a standard Sulu installation is the case.

### Example Usage
Create an entity (either manually or with the symfony maker bundle included here).
```php
<?php

declare(strict_types=1);

namespace App\Entity;

class Changelog
{
    public static $RESOURCE_KEY = 'changelog';

    public ?int $id = null;
    public string $name = '';
    public string $description = '';
}
```

Then you can create a list configuration for this entity `bin/console make:sulu:list App\\Entity\\Changelog`. This will ask for every property if it should be visible and if it is should be searchable. Looking like this:
!(Maker Bundle)[img/maker_bundle.png]

Most of the values have defaults so just hitting enter on most of them works.
