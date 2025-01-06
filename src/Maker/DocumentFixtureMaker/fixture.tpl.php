<?php

use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;

/**
 * @var string $class_name
 * @var string $namespace
 * @var UseStatementGenerator $use_statements
 */
echo "<?php\n";
?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?> implements DocumentFixtureInterface
{
    final public function load(DocumentManagerInterface $documentManager): void
    {
        // Create your objects here...

        // Don't forget to flush at the end
        $documentManager->flush();
    }

    public function getOrder(): int
    {
        return 10;
    }

}
