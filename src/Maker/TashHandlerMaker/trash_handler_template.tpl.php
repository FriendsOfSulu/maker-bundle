<?php

use FriendsOfSulu\MakerBundle\Maker\TashHandlerMaker\TashHandlerGeneratorSettings;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;

/**
 * @var string $namespace
 * @var string $class_name
 * @var TashHandlerGeneratorSettings $settings
 * @var UseStatementGenerator $useStatements
 */
echo '<?php';
?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?= $useStatements; ?>

class <?= $class_name; ?> implements StoreTrashItemHandlerInterface
<?php if ($settings->shouldHaveRestore) { ?>, RestoreTrashItemHandlerInterface <?php } ?>
{
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
<?php if ($settings->shouldHaveRestore) { ?>
        private readonly EntityManagerInterface $entityManager,
<?php } ?>
    ) {
    }

    public function store(object $resourceToTrash, array $options = []): TrashItemInterface
    {
        $restoreData = [];
        $id = (string) $resourceToTrash->getId();
        $title = 'Deleted <?= $settings->resourceClassToTrash; ?> with id '. $id;

        dd('Implement trashing logic here.');

        return $this->trashItemRepository->create(
            resourceKey: $this->getResourceKey(),
            resourceId: $id,
            resourceTitle: $title,
            restoreData: $restoreData,
            restoreType: null,
            restoreOptions: $options,
            resourceSecurityContext: null, // This should be something like <?= $settings->resourceClassToTrash; ?>Admin::SECURITY_CONTEXT,
            resourceSecurityObjectType: null,
            resourceSecurityObjectId: null,
        );
    }

<?php if ($settings->shouldHaveRestore) { ?>
    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        // Disable id generation for this entity, because we want to set the Id manually.
        $metadata = $this->entityManager->getClassMetaData(<?= $settings->resourceClassToTrash; ?>::class);
        $metadata->setIdGenerator(new AssignedGenerator());
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        /** @var array<string, mixed> $data */
        $data = $trashItem->getRestoreData();

        dd('Implement restore logic here.');
        /**
            Example:

            $resourceToRestore = new <?= $settings->resourceClassToTrash; ?>();
            $resourceToRestore->id = $trashItem->getResourceId();
            $this->entityManager->persist($resourceToRestore);

            return $resourceToRestore;
        */
    }
<?php } ?>

    public static function getResourceKey(): string
    {
        return <?= $settings->resourceClassToTrash; ?>::RESOURCE_KEY;
    }
}

