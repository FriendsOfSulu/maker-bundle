<?php

namespace FriendsOfSulu\MakerBundle\Maker\ControllerMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ResourceKeyExtractor;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\UniqueNameGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

class MakeControllerCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    public const ARG_RESOURCE_CLASS = 'resourceClass';
    public const ARG_CONTROLLER_NAMESPACE = 'controller_namespace';
    public const OPT_FORCE = 'force';
    public const ESCAPE_ROUTEKEY = 'escape_routekey';
    public const OPT_ADD_TRASHING = 'add-trashing';

    public const CONTROLLER_DEPENDENCIES = [
        'FOS\RestBundle\Routing\ClassResourceInterface',
        'FOS\RestBundle\Controller\Annotations\RouteResource',
        'FOS\RestBundle\View\View',
        'FOS\RestBundle\View\ViewHandlerInterface',
    ];

    public function __construct(
        private ResourceKeyExtractor $resourceKeyExtractor,
        private UniqueNameGenerator $nameGenerator
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:controller';
    }

    public function getDescription(): string
    {
        return self::getCommandDescription();
    }

    public static function getCommandDescription(): string
    {
        return 'Create a controller that fetches data from the api';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::OPTIONAL, \sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument(self::ARG_CONTROLLER_NAMESPACE, InputArgument::OPTIONAL, 'Namespace where the controller should be generated to', 'App\\Controller\\Admin')
            ->addOption(self::ESCAPE_ROUTEKEY, null, InputOption::VALUE_NONE, 'If your resource key contains underscores they will be removed')
            ->addOption(self::OPT_ADD_TRASHING, null, InputOption::VALUE_NONE, 'Adding trashing functionality to the controller (see sulu:make:trash)')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $resourceClass = self::getStringArgument($input, self::ARG_RESOURCE_CLASS);
        Assert::classExists($resourceClass);

        $resourceKey = $this->resourceKeyExtractor->getUniqueName($resourceClass);
        $generatedClassName = \sprintf(
            '%s\\%sController',
            self::getStringArgument($input, self::ARG_CONTROLLER_NAMESPACE),
            $this->nameGenerator->getUniqueName($resourceClass)
        );

        $routeResource = $resourceKey;
        if (\str_contains($resourceKey, '_')) {
            if (!$input->getOption(self::ESCAPE_ROUTEKEY)) {
                $io->warning('Your resource key "' . $resourceKey . '" contains an underscore. If this is used as a route key this will generate routes like this: "' . \str_replace('_', '/', $resourceClass) . '". This is normally unwanted behaviour. ');
            }

            if ($input->getOption(self::ESCAPE_ROUTEKEY) || $io->confirm('Should the underscores (_) be removed?')) {
                $routeResource = \str_replace('_', '', $resourceKey);
                $io->info('Removed underscore in route key');
            }
        }

        $settings = $this->askMethodsToBeGenerated($io);
        $settings->shouldHaveTrashing = true === $input->getOption(self::OPT_ADD_TRASHING);
        $useStatements = self::CONTROLLER_DEPENDENCIES;
        if ($settings->shouldHaveGetListAction) {
            $useStatements =
                \array_merge(
                    $useStatements,
                    [
                        'Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface',
                        'Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface',
                        'Sulu\Component\Rest\ListBuilder\PaginatedRepresentation',
                        'Sulu\Component\Rest\RestHelperInterface',
                        'Symfony\Component\HttpFoundation\Response',
                    ]
                );
        }

        if ($settings->shouldHaveTrashing) {
            $useStatements[] = 'Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface';
        }
        if ($settings->needsEntityManager()) {
            $useStatements[] = 'Doctrine\ORM\EntityManagerInterface';
        }

        if ($settings->hasUpdateActions()) {
            $useStatements[] = 'Symfony\Component\HttpFoundation\Request';

            $io->info('Please note that if you have update actions like putAction or postAction that you need to implement the mapDataFromRequest on the generated class.');
        }

        $generator->generateClass(
            $generatedClassName,
            __DIR__ . '/controllerTemplate.tpl.php',
            [
                'use_statements' => new UseStatementGenerator($useStatements),
                'resourceKey' => $resourceKey,
                'route_resource_key' => $resourceKey,
                'resourceClass' => $resourceClass,
                'settings' => $settings,
            ]
        );

        $generator->writeChanges();

        $io->info([
            'Next steps: Add the controller to the route routes in the `config/routes_admin.yaml`',
            <<<YAML
app_{$resourceKey}_api:
    type: rest
    prefix: /admin/api
    resource: $generatedClassName
    name_prefix: app.
YAML,
            'Registering the controller in the admin panel under `config/sulu_admin.yaml`:',
            <<<YAML
sulu_admin:
    resources:
        {$resourceKey}:
            routes:
                list: 'app.get_{$resourceKey}s'
                detail: 'app.get_{$resourceKey}'
YAML,
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        foreach (self::CONTROLLER_DEPENDENCIES as $class) {
            $dependencies->addClassDependency($class, 'friendsofsymfony/rest-bundle');
        }
    }

    private function askMethodsToBeGenerated(ConsoleStyle $io): ControllerGeneratorSettings
    {
        $settings = new ControllerGeneratorSettings();
        $settings->shouldHaveGetListAction = $io->confirm('Should the cgetAction be generated (list view)');
        $settings->shouldHaveGetAction = $io->confirm('Should the getAction be generated (single item)');
        $settings->shouldHaveDeleteAction = $io->confirm('Should a deleteAction be generated');

        // Settings for the update actions
        $settings->shouldHavePostAction = $io->confirm('Should it have a postAction (create)');
        $settings->shouldHavePutAction = $io->confirm('Should it have a putAction (update action)');

        return $settings;
    }
}
