<?php

use Mamazu\SuluMaker\ListConfiguration\MakeListConfigurationCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function(ContainerConfigurator $configurator) {
    // default configuration for services in *this* file
    $services = $configurator->services()
        ->defaults()
            ->autowire()      // Automatically injects dependencies in your services.
            ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('Mamazu\\SuluMaker\\', __DIR__.'/../../*')
             ->exclude('../../src/{DependencyInjection,Entity,Tests,Kernel.php}');

    $services->set(MakeListConfigurationCommand::class)
            ->arg('$projectDirectory', '%kernel.project_dir%')
        ;
};
