<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\SuluPageMaker;

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

class MakePageTypeCommand extends AbstractMaker
{
    private const ARG_PAGE_KEY = 'pageKey';
    private const OPT_CONTROLLER = 'controller';
    private const OPT_VIEW = 'view';

    public function __construct(
        private string $projectDirectory
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:page-type';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(self::ARG_PAGE_KEY, InputArgument::OPTIONAL, 'Key of the page (needs to be unique)');
        $command->addOption(
            self::OPT_CONTROLLER,
            null,
            InputOption::VALUE_OPTIONAL,
            'Service name of the controller that should be called',
            'app.controller.sulu.default_action',
        );
        $command->addOption(self::OPT_VIEW, null, InputOption::VALUE_OPTIONAL, 'Path where the template should be located');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $pageKey */
        $pageKey = $input->getArgument(self::ARG_PAGE_KEY);
        $viewPath = $input->getOption(self::OPT_VIEW) ?? 'page/'.$pageKey;
        $configPath = $this->projectDirectory.'/config/templates/pages/'.$pageKey.'.xml';

        if (file_exists($configPath) && !$io->confirm("Config path '$configPath' already exists. Overwrite it?")) {
            return;
        }

        // Generate the config
        $generator->generateFile(
            $configPath,
            __DIR__.'/page_config.tpl.php',
            [
                'pageKey' => $input->getArgument(self::ARG_PAGE_KEY),
                'viewPath' => $viewPath,
                'controller' => $input->getOption(self::OPT_CONTROLLER),
                'pageName' => Str::asHumanWords($pageKey),
            ]
        );

        // Generate an example template
        $generator->generateTemplate(
            $viewPath.'.html.twig',
            __DIR__.'/page_template.tpl.php',
            ['configPath' => $configPath],
        );

        $generator->writeChanges();
    }
}
