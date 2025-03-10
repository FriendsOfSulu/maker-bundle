<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\TashHandlerMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
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

/** @internal */
final class MakeTrashHandlerCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    private const ARG_RESOURCE_CLASS = 'resourceClass';
    private const OPT_NO_RESTORE = 'no-restore';

    public function __construct(private DoctrineHelper $doctrineHelper)
    {
    }

    public static function getCommandDescription(): string
    {
        return 'Create a trash handler for trashing and restoring a database entity';
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:trash';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(self::ARG_RESOURCE_CLASS, InputArgument::REQUIRED, 'The class name of the resource to trash');
        $command->addOption(self::OPT_NO_RESTORE, null, InputOption::VALUE_NONE, 'Do not add restore functionality');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactiveEntityArgument($input, self::ARG_RESOURCE_CLASS, $this->doctrineHelper);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $resourceClass */
        $resourceClass = $input->getArgument(self::ARG_RESOURCE_CLASS);

        $className = $generator->createClassNameDetails(
            Str::getShortClassName($resourceClass),
            namespacePrefix: 'Trash\\',
            suffix: 'TrashItemHandler'
        );

        $settings = new TashHandlerGeneratorSettings(
            Str::getShortClassName($resourceClass),
            !$input->getOption(self::OPT_NO_RESTORE),
        );

        $useStatements = new UseStatementGenerator([
            'Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface',
            'Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface',
            'Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface',
            $resourceClass,
        ]);

        if ($settings->shouldHaveRestore) {
            $useStatements->addUseStatement([
                'Doctrine\ORM\EntityManagerInterface',
                'Doctrine\ORM\Id\AssignedGenerator',
                'Doctrine\ORM\Mapping\ClassMetadata',
                'Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface',
            ]);
        }

        $generator->generateClass(
            $className->getFullName(),
            __DIR__ . '/trash_handler_template.tpl.php',
            [
                'useStatements' => $useStatements,
                'settings' => $settings,
            ],
        );
        $generator->writeChanges();

        $io->success(\sprintf('The "%s" trash handler class was created successfully.', $className->getShortName()));
        $io->text(<<<EOT
            Next steps:
            * Implement the "store" methods on that class.
            * Check the the security context (see comment in class)
            * Register the trash handler
            * Add the trash handler to the resource controller's delete method
            EOT);
    }
}
