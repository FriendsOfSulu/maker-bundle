<?php

namespace Mamazu\SuluMaker\AdminConfiguration;

use Mamazu\SuluMaker\Utils\NameGenerators\ResourceKeyExtractor;
use Mamazu\SuluMaker\Utils\NameGenerators\UniqueNameGenerator;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

class MakeAdminConfigurationCommand extends AbstractMaker
{
    public const ARG_RESOURCE_CLASS = 'resourceClass';
    public const OPT_FORCE = 'force';
    public const OPT_PERMISSIONS = 'permissions';

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
    ) {}

    public static function getCommandName(): string
    {
        return 'make:sulu:admin';
    }

    public function getDescription(): string
    {
        return self::getCommandDescription();
    }

    public static function getCommandDescription(): string{
        return 'Create a list view configuration for your entity';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::OPTIONAL, sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption(self::OPT_PERMISSIONS, null, InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'List of permissions that should be configurable')
            ->addOption(self::OPT_FORCE, '-f', InputOption::VALUE_NONE, 'Force the creation of a new file even if the old one is already there')
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
            array_merge(self::ADMIN_DEPENDENCIES,
            [
                "Sulu\Component\Security\Authorization\PermissionTypes",
                "Sulu\Component\Security\Authorization\SecurityCheckerInterface",
            ])
        );

        $settings = new AdminGeneratorSettings();

        // Todo: Make an advanced mode where you can configure those
        $settings->formKey = $resourceKey;
        $settings->listKey = $resourceKey;

        $settings->shouldAddMenuItem = $io->confirm('Do you want to have a menu entry?');
        $settings->shouldHaveEditForm = $io->confirm('Do you want to have an edit form?');

        /** @var string $slug */
        $slug =$io->ask('Enter the API slug', '/'.$resourceKey);
        $settings->slug = '/'.ltrim($slug, '/');

        if (str_contains($settings->slug, '_')) {
            $io->warning('Your slug contains an _ this could cause problems when generating a controller for this class. It is recommended to not use underscores in the slug.');
        }

        /** @var array<string> $availablePermissions */
        $availablePermissions =
            array_keys(
                (new ReflectionClass("Sulu\Component\Security\Authorization\PermissionTypes"))
                    ->getConstants()
            );

        /** @var array<string> $currentOptionvalue */
        $currentOptionvalue = $input->getOption(self::OPT_PERMISSIONS);
        if ($input->isInteractive() && !$currentOptionvalue) {

            // Get available PermissionTypes from Sulu class

            $choiceQuestion = new ChoiceQuestion(
                'Which permissions should be configurable in the admin panel?',
                $availablePermissions
            );
            $choiceQuestion->setMultiselect(true);

            /** @var array $answer */
            $answer =$io->askQuestion($choiceQuestion);

            $settings->permissionTypes = $answer;
        } else {
            $settings->permissionTypes = $currentOptionvalue ?: $availablePermissions;
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
