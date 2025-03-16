<?php

namespace FriendsOfSulu\MakerBundle\Maker\ControllerMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ResourceKeyExtractor;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
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

/** @internal */
final class MakeControllerCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    private const ARG_RESOURCE_CLASS = 'resourceClass';
    private const OPT_ESCAPE_ROUTEKEY = 'escape-routekey';
    private const OPT_ADD_TRASHING = 'add-trashing';
    private const OPT_ASSUME_DEFAULTS = 'assume-defaults';

    private ControllerGeneratorSettings $settings;

    public const CONTROLLER_DEPENDENCIES = [
        'Symfony\Component\Routing\Attribute\Route',
        'FOS\RestBundle\View\View',
        'FOS\RestBundle\View\ViewHandlerInterface',
    ];

    public function __construct(
        private string $projectDirectory,
        private ResourceKeyExtractor $resourceKeyExtractor,
        private DoctrineHelper $doctrineHelper,
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
        return 'Create a controller that provides the API the admin interface uses';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                self::ARG_RESOURCE_CLASS,
                InputArgument::OPTIONAL,
                \sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())),
            )
            ->addOption(
                self::OPT_ESCAPE_ROUTEKEY,
                null,
                InputOption::VALUE_NONE,
                'If your resource key contains underscores they will be removed',
            )
            ->addOption(
                self::OPT_ADD_TRASHING,
                null,
                InputOption::VALUE_NONE,
                'Adding trashing functionality to the controller (see sulu:make:trash)',
            )
            ->addOption(
                self::OPT_ASSUME_DEFAULTS,
                '-d',
                InputOption::VALUE_NONE,
                'Assume default values',
            )
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactiveEntityArgument($input, self::ARG_RESOURCE_CLASS, $this->doctrineHelper);

        $this->settings = $this->askMethodsToBeGenerated($io, true === $input->getOption(self::OPT_ASSUME_DEFAULTS));
        $this->settings->shouldHaveTrashing = true === $input->getOption(self::OPT_ADD_TRASHING);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $resourceClass = self::getStringArgument($input, self::ARG_RESOURCE_CLASS);
        Assert::classExists($resourceClass);

        $resourceKey = $this->resourceKeyExtractor->getUniqueName($resourceClass);

        $generatedClassName = $generator->createClassNameDetails(
            Str::getShortClassName($resourceClass),
            namespacePrefix: 'Controller\\Admin\\',
            suffix: 'Controller'
        );

        $routeResource = $resourceKey;
        if (\str_contains($resourceKey, '_')) {
            $io->warning('Your resource key "' . $resourceKey . '" contains an underscore. If this is used as a route key this will generate routes like this: "' . \str_replace('_', '/', $resourceClass) . '". This is normally unwanted behaviour. ');
            if ($io->confirm('Should the underscores (_) be removed?', false)) {
                $routeResource = \str_replace('_', '', $resourceKey);
                $io->info('Removed underscore in route key');
            }
        }

        $useStatements = self::CONTROLLER_DEPENDENCIES;
        if ($this->settings->shouldHaveGetListAction) {
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

        if ($this->settings->shouldHaveTrashing) {
            $useStatements[] = 'Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface';
        }
        if ($this->settings->needsEntityManager()) {
            $useStatements[] = 'Doctrine\ORM\EntityManagerInterface';
        }

        if ($this->settings->hasUpdateActions()) {
            $useStatements[] = 'Symfony\Component\HttpFoundation\Request';

            $io->note('You need to implement the "mapDataFromRequest" on the generated class.');
        }

        $generator->generateClass(
            $generatedClassName->getFullName(),
            __DIR__ . '/controllerTemplate.tpl.php',
            [
                'use_statements' => new UseStatementGenerator($useStatements),
                'resourceKey' => $resourceKey,
                'route_resource_key' => $resourceKey,
                'resourceClass' => $resourceClass,
                'settings' => $this->settings,
            ]
        );

        $generator->writeChanges();

        $controllerClassName = $generatedClassName->getFullName();

        $this->suggestAddingRouting($io);

        $io->info([
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

    private function askMethodsToBeGenerated(ConsoleStyle $io, bool $assumeDefaults): ControllerGeneratorSettings
    {
        $settings = new ControllerGeneratorSettings();
        if ($assumeDefaults) {
            return $settings;
        }

        $settings->shouldHaveGetListAction = $io->confirm('Should the cgetAction be generated (list view)');
        $settings->shouldHaveGetAction = $io->confirm('Should the getAction be generated (single item)');
        $settings->shouldHaveDeleteAction = $io->confirm('Should a deleteAction be generated');

        // Settings for the update actions
        $settings->shouldHavePostAction = $io->confirm('Should it have a postAction (create)');
        $settings->shouldHavePutAction = $io->confirm('Should it have a putAction (update action)');

        return $settings;
    }

    private function suggestAddingRouting(ConsoleStyle $io): void
    {
        // Try to see if the thing is already set up and don't suggest it.
        $routesContent = \file_get_contents(\implode(
            \DIRECTORY_SEPARATOR,
            [$this->projectDirectory, 'config', 'routes', 'sulu_admin.yaml'],
        ));
        if (\is_string($routesContent) && \str_contains($routesContent, 'admin_controllers:')) {
            return;
        }

        $io->note(
            <<<YAML
Next steps: Add the controller to the route routes in the `config/routes_admin.yaml`

admin_controllers:
    resource:
        path: ../src/Controller/Admin
        namespace: App\Controller\Admin
    prefix: /admin/api
    type: attribute
YAML);
    }
}
