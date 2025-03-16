<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\WebspaceConfigMaker;

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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/** @internal */
final class MakeWebspaceConfigCommand extends AbstractMaker
{
    private const ARG_WEBSPACE_KEY = 'webspaceKey';
    private const OPT_WEBSPACE_NAME = 'webspaceName';
    private const OPT_ASSUME_DEFAULTS = 'assume-defaults';
    private const ARG_WEBSPACE_DIRECTORY = 'configDir';

    public function __construct(
        private string $projectDirectory,
    ) {
    }

    public static function getCommandDescription(): string
    {
        return 'Generate a default webspace configuration';
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:webspace';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(self::ARG_WEBSPACE_KEY, InputArgument::REQUIRED, 'Key of the webspace configuration');
        $command->addArgument(
            self::ARG_WEBSPACE_DIRECTORY,
            InputArgument::OPTIONAL,
            'Directory for list configurations',
            $this->projectDirectory . '/config/webspaces',
        );
        $command->addOption(
            self::OPT_WEBSPACE_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name of the webspace configuration',
        );
        $command->addOption(
            self::OPT_ASSUME_DEFAULTS,
            '-d',
            InputOption::VALUE_NONE,
            'Assume default values (names will be generated from keys)',
        );
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        /** @var string $webspaceKey */
        $webspaceKey = $input->getArgument(self::ARG_WEBSPACE_KEY);

        if ($input->getOption(self::OPT_WEBSPACE_NAME)) {
            return;
        }

        if ($input->getOption(self::OPT_ASSUME_DEFAULTS)) {
            $webspaceName = Str::asHumanWords($webspaceKey);
        } else {
            $webspaceName = $io->askQuestion(new Question('What should the webspace name be'));
        }
        $input->setOption(self::OPT_WEBSPACE_NAME, $webspaceName);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $webspaceKey = $input->getArgument(self::ARG_WEBSPACE_KEY);
        $webspaceName = $input->getOption(self::OPT_WEBSPACE_NAME);

        /** @var string $configDirectory */
        $configDirectory = $input->getArgument(self::ARG_WEBSPACE_DIRECTORY);
        if (!\file_exists($configDirectory)) {
            throw new FileNotFoundException('Could not find config directory: ' . $configDirectory);
        }

        $io->info('Using config directory: ' . $configDirectory);

        $filePath = $configDirectory . '/' . $webspaceKey . '.xml';
        if (\file_exists($filePath)) {
            if (!$io->confirm("The list configuration under '$filePath' already exists. Do you want to overwrite it?")) {
                return;
            }
            \unlink($filePath);
        }

        $generator->generateFile($filePath, __DIR__ . '/webspace_template.tpl.php', [
            'webspaceKey' => $webspaceKey,
            'webspaceName' => $webspaceName,
        ]);
        $generator->writeChanges();
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
