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
