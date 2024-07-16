<?php

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

use FriendsOfSulu\MakerBundle\Utils\NameGenerators\UniqueNameGenerator;
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
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;

class MakeListConfigurationCommand extends AbstractMaker
{
    const ARG_RESOURCE_CLASS = 'resourceClass';

    public function __construct(
        private string $projectDirectory,
        private ListPropertyInfoProvider $propertyInfoProvider,
        private UniqueNameGenerator $nameGenerator
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
        ;
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
        $io->writeln('Generating stuff for '. $className);

        $this->propertyInfoProvider->setIo($io);
        $properties = $this->propertyInfoProvider->provide((new ReflectionClass($className))->getProperties());

        $generator->generateFile($filePath, __DIR__.'/list_template.tpl.php', [
            'entityClass' => $className,
            'listKey' => $resourceKey,
            'properties' => $properties
        ]);
        $generator->writeChanges();

        $io->success('Success');
        $io->success('');
        $io->success('Generated file can be found under: '. $filePath);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
