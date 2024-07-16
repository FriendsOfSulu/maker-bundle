<?php

use FriendsOfSulu\MakerBundle\Maker\AdminConfigurationMaker\MakeAdminConfigurationCommand;
use FriendsOfSulu\MakerBundle\Maker\ControllerMaker\MakeControllerCommand;
use FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker\MakeListConfigurationCommand;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ClassNameGenerator;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ResourceKeyExtractor;
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
    $services->load('FriendsOfSulu\\MakerBundle\\', __DIR__.'/../../*')
             ->exclude('../../src/{DependencyInjection,Entity,Tests,Kernel.php}');

    $services
        ->set(MakeListConfigurationCommand::class)
        ->arg('$projectDirectory', '%kernel.project_dir%')
        ->arg('$nameGenerator', service(ResourceKeyExtractor::class))
    ;

    $services
        ->set(MakeAdminConfigurationCommand::class)
        ->arg('$nameGenerator', service(ClassNameGenerator::class))
    ;

    $services
        ->set(MakeControllerCommand::class)
        ->arg('$nameGenerator', service(ClassNameGenerator::class))
    ;

    $services
        ->set(ClassNameGenerator::class)
        ->args([
            service(ResourceKeyExtractor::class)
        ]);
};
