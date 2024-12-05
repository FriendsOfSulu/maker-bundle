<?php

namespace FriendsOfSulu\MakerBundle\Maker\AdminConfigurationMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ResourceKeyExtractor;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\UniqueNameGenerator;
use ReflectionClass;
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
use Symfony\Component\Console\Question\ChoiceQuestion;
use Webmozart\Assert\Assert;

class MakeAdminConfigurationCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    public const ARG_RESOURCE_CLASS = 'resourceClass';
    public const OPT_FORCE = 'force';
    public const OPT_PERMISSIONS = 'permissions';
    public const OPT_ADVANCED = 'advanced';

    public const ADMIN_DEPENDENCIES = [
        "Sulu\Bundle\AdminBundle\Admin\Admin",
        "Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem",
        "Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection",
        "Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction",
        'Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface',
        "Sulu\Bundle\AdminBundle\Admin\View\ViewCollection",
    ];

    public function __construct(
        private ResourceKeyExtractor $resourceKeyExtractor,
        private UniqueNameGenerator $nameGenerator
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
            ->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::OPTIONAL, sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption(self::OPT_PERMISSIONS, null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of permissions that should be configurable')
            ->addOption(self::OPT_FORCE, '-f', InputOption::VALUE_NONE, 'Force the creation of a new file even if the old one is already there')
            ->addOption(self::OPT_ADVANCED, '-a', InputOption::VALUE_NONE, 'Show all the options. This only works for interactive prompts though')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $className */
        $className = $input->getArgument(self::ARG_RESOURCE_CLASS);
        Assert::classExists($className);
        $resourceKey = $this->resourceKeyExtractor->getUniqueName($className);
        $generatedClassName = 'App\\Admin\\'.$this->nameGenerator->getUniqueName($className).'Admin';

        $useStatements = new UseStatementGenerator(
            array_merge(
                self::ADMIN_DEPENDENCIES,
                [
                "Sulu\Component\Security\Authorization\PermissionTypes",
                "Sulu\Component\Security\Authorization\SecurityCheckerInterface",
            ]
            )
        );

        $settings = new AdminGeneratorSettings();

        $settings->shouldAddMenuItem = $io->confirm('Do you want to have a menu entry?');
        $settings->shouldHaveEditForm = $io->confirm('Do you want to have an edit form?');
        $settings->shouldHaveReferences = $io->confirm('Do you want to have an a references tab?');

        if ($settings->shouldHaveReferences) {
            $useStatements->addUseStatement('Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\Admin\View\ReferenceViewBuilderFactoryInterface');
        }

        $slug = $this->askString($io, 'Enter the API slug', '/'.$resourceKey);
        $settings->slug = '/'.ltrim($slug, '/');

        if (str_contains($settings->slug, '_')) {
            $io->warning('Your slug contains an _ this could cause problems when generating a controller for this class. It is recommended to not use underscores in the slug.');
        }

        /** @var class-string $permissionTypeClass */
        $permissionTypeClass = "Sulu\Component\Security\Authorization\PermissionTypes";

        /** @var array<string> $availablePermissions */
        $availablePermissions = array_keys((new ReflectionClass($permissionTypeClass))->getConstants());

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

            $settings->permissionTypes = $answer;
        } else {
            $settings->permissionTypes = $currentOptionvalue ?: $availablePermissions;
        }

        if ($input->hasOption(self::OPT_ADVANCED) && $input->isInteractive()) {
            $settings->formKey = $this->askString($io, 'Form Key', $resourceKey);
            $settings->listKey = $this->askString($io, 'List Key', $resourceKey);
        } else {
            $settings->formKey = $resourceKey;
            $settings->listKey = $resourceKey;
        }

        $generator->generateClass(
            $generatedClassName,
            __DIR__.'/configurationTemplate.tpl.php',
            [
                'use_statements' => $useStatements,
                'resourceKey' => $resourceKey,
                'settings' => $settings,
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
}
