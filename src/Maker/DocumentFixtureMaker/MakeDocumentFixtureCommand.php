<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\DocumentFixtureMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeDocumentFixtureCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    private const ARG_FIXTURE_CLASS = 'fixture-class';

    public static function getCommandName(): string
    {
        return 'make:sulu:fixture';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Sulu Document fixture class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(
            self::ARG_FIXTURE_CLASS,
            InputArgument::OPTIONAL,
            'The class name of the fixture to create',
        );
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $io->info('This command is for generating sulu document fixtures. If you just want to generate doctrine fixtures use bin/console make:fixtures instead.');

        $fixtureClass = $generator->createClassNameDetails(
            $this->getStringArgument($input, self::ARG_FIXTURE_CLASS),
            'DataFixtures\\SuluDocument\\'
        );

        $useStatements = new UseStatementGenerator([
            'Sulu\Bundle\DocumentManagerBundle\DataFixtures\DocumentFixtureInterface',
            'Sulu\Component\DocumentManager\DocumentManagerInterface',
        ]);

        $generator->generateClass(
            $fixtureClass->getFullName(),
            __DIR__ . '/fixture.tpl.php',
            [
                'use_statements' => $useStatements,
            ]
        );

        $generator->writeChanges();
    }
}
