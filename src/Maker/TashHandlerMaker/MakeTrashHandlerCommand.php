<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\TashHandlerMaker;

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

class MakeTrashHandlerCommand extends AbstractMaker
{
    private const ARG_CLASS_NAME = 'class-name';
    private const OPT_NAMESPACE = 'namespace';
    private const OPT_NO_RESTORE = 'no-restore';

    public static function getCommandName(): string
    {
        return 'make:sulu:trash';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(self::ARG_CLASS_NAME, InputArgument::REQUIRED, 'The class name of the resource to trash');
        $command->addOption(self::OPT_NAMESPACE, null, InputOption::VALUE_REQUIRED, 'Namespace to generate the class to', 'App');
        $command->addOption(self::OPT_NO_RESTORE, null, InputOption::VALUE_NONE, 'Do not add restore functionality');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $resourceClass */
        $resourceClass = $input->getArgument(self::ARG_CLASS_NAME);
        /** @var string $namespace */
        $namespace = $input->getOption(self::OPT_NAMESPACE);

        $className = Str::getShortClassName($resourceClass).'TrashItemHandler';
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
            $namespace.'\\'.$className,
            __DIR__.'/trash_handler_template.tpl.php',
            [
                'namespace' => $namespace,
                'useStatements' => $useStatements,
                'className' => $className,
                'settings' => $settings,
            ],
        );
        $generator->writeChanges();

        $io->success(sprintf('The "%s" trash handler class was created successfully.', $className));
        $io->text(<<<EOT
            Next steps:
            * Implement the "store" methods on that class.
            * Check the the security context (see comment in class)
            * Register the trash handler
            * Add the trash handler to the resource controller's delete method
            EOT);
    }
}
