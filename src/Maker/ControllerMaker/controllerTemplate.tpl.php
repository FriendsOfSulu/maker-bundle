<?php
/**
 * @var FriendsOfSulu\MakerBundle\Maker\ControllerMaker\ControllerGeneratorSettings $settings
 * @var string $resourceKey
 * @var string $resourceClass
 * @var string $namespace
 * @var string $class_name
 * @var string $use_statements
 */
$a = \explode('\\', $resourceClass);
$resourceClassName = \end($a);

echo "<?php\n";
?>

namespace <?= $namespace; ?>;

use <?= $resourceClass; ?>;
<?= $use_statements; ?>

/**
 * @RouteResource("<?= $resourceKey; ?>")
 */
class <?= $class_name; ?> implements ClassResourceInterface
{

    public function __construct(
<?php if ($settings->shouldHaveGetListAction) { ?>
        private ViewHandlerInterface $viewHandler,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private RestHelperInterface $restHelper,
<?php } ?>
<?php if ($settings->needsEntityManager()) {?>
        private EntityManagerInterface $entityManager,
<?php } ?>
<?php if ($settings->shouldHaveTrashing) {?>
        private StoreTrashItemHandlerInterface $trashItemHandler,
<?php } ?>
    ) {
    }
<?php if ($settings->shouldHaveGetListAction) { ?>

    public function cgetAction(): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(<?= $resourceClassName; ?>::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(<?= $resourceClassName; ?>::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            <?= $resourceClassName; ?>::RESOURCE_KEY,
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->viewHandler->handle(View::create($listRepresentation));
    }
<?php } ?>
<?php if ($settings->shouldHaveGetAction) { ?>

    public function getAction(string $id): Response
    {
        $entity = $this->entityManager->find(<?= $resourceClassName; ?>::class, $id);
        if ($entity === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->viewHandler->handle(View::create($entity));
    }
<?php } ?>
<?php if ($settings->shouldHavePostAction) { ?>

    public function postAction(Request $request): Response
    {
        $productFilterConfiguration = new <?= $resourceClassName; ?>();
        $this->mapDataFromRequest($request, $productFilterConfiguration);

        $this->entityManager->persist($productFilterConfiguration);
        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create($productFilterConfiguration));
    }
<?php } ?>
<?php if ($settings->shouldHavePutAction) { ?>

    public function putAction(string $id, Request $request): Response
    {
        $productConfiguration = $this->entityManager->find(<?= $resourceClassName; ?>::class, $id);
        $this->mapDataFromRequest($request, $productConfiguration);

        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create($productConfiguration));
    }
<?php } ?>
<?php if ($settings->shouldHaveDeleteAction) { ?>

    public function deleteAction(string $id): Response
    {
        $entity = $this->entityManager->find(<?= $resourceClassName; ?>::class, $id);

<?php if ($settings->shouldHaveTrashing) { ?>
        $this->trashManager->store('<?= $resourceKey; ?>', $listingTile);
<?php } ?>

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create(null, Response::HTTP_NO_CONTENT));
    }
<?php } ?>
<?php if ($settings->shouldHavePostAction || $settings->shouldHavePutAction) { ?>

    public function mapDataFromRequest(Request $request, <?= $resourceClassName; ?> $entity): void
    {
        throw new \BadMethodCallException('There was no mapping function defined that can map a request to a <?= $resourceClass; ?> object. Implement '. self::class. '::mapDataFromRequest to remove the error');
    }
<?php } ?>

    public function getSecurityContext(): string
    {
        return 'sulu.app.<?= $resourceKey; ?>';
    }
}
