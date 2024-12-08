<?php

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

use FriendsOfSulu\MakerBundle\Utils\NameGenerators\UniqueNameGenerator;
use ReflectionClass;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;

class MakeListConfigurationCommand extends AbstractMaker
{
    public const ARG_RESOURCE_CLASS = 'resourceClass';
    public const OPT_ASSUME_DEFAULTS = 'assume-defaults';

    public function __construct(
        private string $projectDirectory,
        private DoctrineHelper $doctrineHelper,
        private ListPropertyInfoProvider $propertyInfoProvider,
        private UniqueNameGenerator $nameGenerator
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:list';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a list view configuration for your entity';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(
                self::ARG_RESOURCE_CLASS,
                InputArgument::OPTIONAL,
                sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())),
            )
        ;
        $command
            ->addOption(
                self::OPT_ASSUME_DEFAULTS,
                'd',
                InputOption::VALUE_NONE,
                'Assuming all visible fields are searchable and use default translations.',
            );

        $inputConfig->setArgumentAsNonInteractive(self::ARG_RESOURCE_CLASS);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $entityQuestion = new Question("What entity do you want to generate the list view for");
        $entityQuestion->setValidator(Validator::notBlank(...));
        $entityQuestion->setAutocompleterValues($this->doctrineHelper->getEntitiesForAutocomplete());

        $className = $this->doctrineHelper->getEntityNamespace().'\\'.$io->askQuestion($entityQuestion);
        $input->setArgument(self::ARG_RESOURCE_CLASS, $className);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        // TODO: Better way of finding the config directory
        $configDirectory = $this->projectDirectory.'/config/lists';
        if (!file_exists($configDirectory)) {
            throw new FileNotFoundException('Could not find config directory: ' . $configDirectory);
        }

        /** @var string $className */
        $className = $input->getArgument(self::ARG_RESOURCE_CLASS);
        Assert::classExists($className, 'Class does not exist. Please provide an existing entity');

        $resourceKey = $this->nameGenerator->getUniqueName($className);
        $filePath = $configDirectory.'/'.$resourceKey.'.xml';
        if (file_exists($filePath)) {
            if (!$io->confirm("The list configuration under '$filePath' already exists. Do you want to overwrite it?")) {
                return;
            }
            unlink($filePath);
        }

        $io->writeln('Generating list configuration for '. $className);

        /** @var bool $assumeDefaults */
        $assumeDefaults = $input->getOption(self::OPT_ASSUME_DEFAULTS);

        $this->propertyInfoProvider->setIo($io);
        $properties = $this->propertyInfoProvider->provide(new ReflectionClass($className), $assumeDefaults);

        $generator->generateFile($filePath, __DIR__.'/list_template.tpl.php', [
            'entityClass' => $className,
            'listKey' => $resourceKey,
            'properties' => $properties
        ]);
        $generator->writeChanges();

        $io->success('Success');
        $io->success('');
        foreach ($generator->getGeneratedFiles() as $file) {
            $io->success('Generated: '. $filePath);
        }
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
