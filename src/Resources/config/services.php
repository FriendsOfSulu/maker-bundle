<?php

use Mamazu\SuluMaker\AdminConfiguration\MakeAdminConfigurationCommand;
use Mamazu\SuluMaker\ListConfiguration\MakeListConfigurationCommand;
use Mamazu\SuluMaker\Utils\NameGenerators\AdminClassNameGenerator;
use Mamazu\SuluMaker\Utils\NameGenerators\ResourceKeyExtractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
             ->arg('$nameGenerator', service(ResourceKeyExtractor::class))
        ;

    $services->set(MakeAdminConfigurationCommand::class)
             ->arg('$nameGenerator', service(AdminClassNameGenerator::class))
         ;

    $services->set(AdminClassNameGenerator::class)
             ->args([
                 service(ResourceKeyExtractor::class)
             ]);
};
