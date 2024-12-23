<?php

namespace FriendsOfSulu\MakerBundle\Maker\AdminConfigurationMaker;

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
use Symfony\Component\Console\Question\ChoiceQuestion;

class MakeAdminConfigurationCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    public const ARG_RESOURCE_CLASS = 'resourceClass';
    public const OPT_FORCE = 'force';
    public const OPT_PERMISSIONS = 'permissions';
    public const OPT_ASSUME_DEFAULTS = 'assume-defaults';

    private AdminGeneratorSettings $settings;

    public const ADMIN_DEPENDENCIES = [
        'Sulu\Bundle\AdminBundle\Admin\Admin',
        'Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem',
        'Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection',
        'Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction',
        'Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface',
        'Sulu\Bundle\AdminBundle\Admin\View\ViewCollection',
    ];

    public function __construct(
        private ResourceKeyExtractor $resourceKeyExtractor,
        private DoctrineHelper $doctrineHelper,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:admin';
    }

    public function getDescription(): string
    {
        return self::getCommandDescription();
    }

    public static function getCommandDescription(): string
    {
        return 'Create a list view configuration for your entity';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::OPTIONAL, \sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption(self::OPT_PERMISSIONS, null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of permissions that should be configurable')
            ->addOption(self::OPT_FORCE, '-f', InputOption::VALUE_NONE, 'Force the creation of a new file even if the old one is already there')
            ->addOption(self::OPT_ASSUME_DEFAULTS, '-d', InputOption::VALUE_NONE, 'Assume default values and ask less questions')
        ;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactiveEntityArgument($input, self::ARG_RESOURCE_CLASS, $this->doctrineHelper);

        /** @var class-string $resourceClassName */
        $resourceClassName = $input->getArgument(self::ARG_RESOURCE_CLASS);
        $resourceKey = $this->resourceKeyExtractor->getUniqueName($resourceClassName);

        $this->settings = $this->askMethodsToBeGenerated(
            $io,
            assumeDefaults: true === $input->getOption(self::OPT_ASSUME_DEFAULTS),
            resourceKey: $resourceKey
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var class-string $resourceClassName */
        $resourceClassName = $input->getArgument(self::ARG_RESOURCE_CLASS);

        $className = $generator->createClassNameDetails(
            Str::getShortClassName($resourceClassName),
            namespacePrefix: 'Admin\\',
            suffix: 'Admin'
        );

        $useStatements = new UseStatementGenerator(
            \array_merge(
                self::ADMIN_DEPENDENCIES,
                [
                    'Sulu\Component\Security\Authorization\PermissionTypes',
                    'Sulu\Component\Security\Authorization\SecurityCheckerInterface',
                ]
            )
        );

        if ($this->settings->shouldHaveReferences) {
            $useStatements->addUseStatement('Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\Admin\View\ReferenceViewBuilderFactoryInterface');
        }

        if (\str_contains($this->settings->slug, '_')) {
            $io->warning('Your slug contains an _ this could cause problems when generating a controller for this class. It is recommended to not use underscores in the slug.');
        }

        /** @var class-string $permissionTypeClass */
        $permissionTypeClass = 'Sulu\Component\Security\Authorization\PermissionTypes';

        /** @var array<string> $availablePermissions */
        $availablePermissions = \array_keys((new \ReflectionClass($permissionTypeClass))->getConstants());

        /** @var array<string> $currentOptionvalue */
        $currentOptionvalue = $input->getOption(self::OPT_PERMISSIONS);
        if ($input->isInteractive() && !$currentOptionvalue) {
            // Get available PermissionTypes from Sulu class
            $choiceQuestion = new ChoiceQuestion(
                'Which permissions should be configurable in the admin panel? (Multiple selections are allowed: comma separated)',
                $availablePermissions
            );
            $choiceQuestion->setMultiselect(true);

            /** @var array<string> $answer */
            $answer = $io->askQuestion($choiceQuestion);

            $this->settings->permissionTypes = $answer;
        } else {
            $this->settings->permissionTypes = $currentOptionvalue ?: $availablePermissions;
        }

        $generator->generateClass(
            $className->getFullName(),
            __DIR__ . '/configurationTemplate.tpl.php',
            [
                'use_statements' => $useStatements,
                'resourceKey' => $this->settings->resourceKey,
                'settings' => $this->settings,
            ]
        );

        $generator->writeChanges();
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        foreach (self::ADMIN_DEPENDENCIES as $class) {
            $dependencies->addClassDependency($class, 'sulu/sulu-admin');
        }
    }

    private function askMethodsToBeGenerated(ConsoleStyle $io, bool $assumeDefaults, string $resourceKey): AdminGeneratorSettings
    {
        $settings = new AdminGeneratorSettings($resourceKey);
        if ($assumeDefaults) {
            return $settings;
        }

        $settings->shouldAddMenuItem = $io->confirm('Do you want to have a menu entry?');
        $settings->shouldHaveEditForm = $io->confirm('Do you want to have an edit form?');
        $settings->shouldHaveReferences = $io->confirm('Do you want to have an a references tab?');

        $slug = $this->askString($io, 'Enter the API slug', '/' . $settings->resourceKey);
        $this->settings->slug = '/' . \ltrim($slug, '/');

        $settings->formKey = $this->askString($io, 'Form Key', $resourceKey);
        $settings->listKey = $this->askString($io, 'List Key', $resourceKey);

        return $settings;
    }
}
