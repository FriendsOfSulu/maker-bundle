<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use ReflectionClass;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;

class MakeListConfigurationCommand extends AbstractMaker
{
    const ARG_RESOURCE_CLASS = 'resourceClass';
    const OPT_FORCE = 'force';

    public function __construct(
        private string $projectDirectory,
        private XmlGenerator $xmlListGenerator,
        private ListPropertyInfoProvider $propertyInfoProvider
    ) {}

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
            ->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::OPTIONAL, sprintf('Class that you want to generate the list view for (eg. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption(self::OPT_FORCE, '-f', InputOption::VALUE_NONE, 'Force the creation of a new file even if the old one is already there');
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
        $reflection = new ReflectionClass($className);

        $resourceKey = $reflection->getProperty('RESOURCE_KEY')->getValue();
        Assert::string($resourceKey, 'Resource key must be a "string" but got "'. get_debug_type($resourceKey). '" given');

        $filePath = $configDirectory.'/'.$resourceKey.'.xml';
        if (file_exists($filePath) && !$input->getOption('force')) {
            $io->error([
                'File already exists: '. $filePath,
                '',
                'If you want to overwrite this file run the command with the --'.self::OPT_FORCE.' option',
            ]);
            return;
        }

        $io->writeln('Generating stuff for '. $className);

        $this->propertyInfoProvider->setIo($io);
        $properties = $this->propertyInfoProvider->provide($reflection->getProperties());

        $xml = $this->xmlListGenerator->generate($resourceKey, $className, $properties);
        file_put_contents($filePath, $xml);

        $io->success('Success');
        $io->success('');
        $io->success('Generated file can be found under: '. $filePath);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
