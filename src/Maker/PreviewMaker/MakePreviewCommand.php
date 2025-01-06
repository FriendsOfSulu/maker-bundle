<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\PreviewMaker;

use FriendsOfSulu\MakerBundle\Utils\ConsoleHelperTrait;
use FriendsOfSulu\MakerBundle\Utils\NameGenerators\ResourceKeyExtractor;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

/** @internal */
final class MakePreviewCommand extends AbstractMaker
{
    use ConsoleHelperTrait;

    private const ARG_RESOURCE_CLASS = 'resourceClass';

    public function __construct(
        private ResourceKeyExtractor $resourceKeyExtractor,
        private DoctrineHelper $doctrineHelper,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:sulu:preview';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(self::ARG_RESOURCE_CLASS, InputOption::VALUE_REQUIRED, 'The resource class to be previewed');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactiveEntityArgument($input, self::ARG_RESOURCE_CLASS, $this->doctrineHelper);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $resourceClass */
        $resourceClass = $input->getArgument(self::ARG_RESOURCE_CLASS);
        Assert::classExists($resourceClass);
        $resourceClassName = Str::getShortClassName($resourceClass);

        $classNameDetails = $generator->createClassNameDetails(
            name: $resourceClassName,
            namespacePrefix: 'PreviewProvider\\',
            suffix: 'PreviewProvider'
        );
        $resourceKey = $this->resourceKeyExtractor->getUniqueName($resourceClass);

        if (\is_a($resourceClass, '\Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface', true)) {
            $io->info([<<<EOT
                There is already a class for that. You just need to register a service like this:

                app.{$resourceKey}_object_provider:
                    class: 'Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider'
                    arguments:
                        \$contentRichEntityClass: '$resourceClass'
                    tags:
                        - {name: 'sulu_preview.object_provider', 'provider-key': '$resourceKey'}
                EOT,
            ]);

            return;
        }

        $useStatements = new UseStatementGenerator([
            'Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface',
            $resourceClass,
        ]);

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/preview_provider_template.tpl.php',
            [
                'use_statements' => $useStatements,
                'resource_class' => $resourceClassName,
            ]
        );

        $templateName = 'admin_preview/' . Str::asTwigVariable($resourceClassName);
        $generator->generateTemplate(
            $templateName . '.html.twig',
            __DIR__ . '/preview_template.tpl.php',
            [
                'resource_key' => $resourceKey,
            ]
        );
        $generator->writeChanges();

        $io->info(<<<TEXT
            Next steps:
            * Add the generated view to your XML template configuration under: config/template/$resourceKey
            Like this:
            <template>
                ...
                <view>$templateName</view>
                ...
            </template>

            * Fill the template with your preview content
            * In the {$resourceKey}Admin class use the `createPreviewFormBuilder` method instead of `createFormViewBuilder`
            TEXT);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
