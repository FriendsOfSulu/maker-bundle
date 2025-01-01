<?php

/**
 * @var string $namespace
 * @var string $class_name
 * @var string $resource_class
 * @var \Symfony\Bundle\MakerBundle\Util\UseStatementGenerator $use_statements
 */
echo '<?php'; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name; ?> implements PreviewObjectProviderInterface
{
    public function getObject($id, $locale): <?= $resource_class; ?>
    {
    }

    public function getId($object): string
    {
    }

    public function setValues($object, $locale, array $data): void
    {
    }

    public function setContext($object, $locale, array $context)
    {
    }

    public function serialize($object): string
    {
    }

    public function deserialize($serializedObject, $objectClass): <?= $resource_class; ?>
    {
    }

    public function getSecurityContext($id, $locale): ?string
    {
    }
}

