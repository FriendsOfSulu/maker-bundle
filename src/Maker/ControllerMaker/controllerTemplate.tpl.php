<?php
/**
 * @var FriendsOfSulu\MakerBundle\Maker\ControllerMaker\ControllerGeneratorSettings $settings
 * @var string $resourceKey
 * @var string $resourceClass
 * @var string $namespace
 * @var string $class_name
 * @var string $use_statements
 */
$resourceClassName = Symfony\Bundle\MakerBundle\Str::getShortClassName($resourceClass);

echo "<?php\n";
?>

namespace <?= $namespace; ?>;

use <?= $resourceClass; ?>;
<?= $use_statements; ?>

class <?= $class_name; ?>
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

    #[Route(
        '/<?= $resourceKey; ?>',
        name: 'app_admin.<?= $resourceKey; ?>.list',
        methods: ['GET'],
    )]
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

    #[Route(
        '/<?= $resourceKey; ?>/{id}',
        name: 'app_admin.<?= $resourceKey; ?>.get',
        methods: ['GET'],
    )]
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

    #[Route(
        '/<?= $resourceKey; ?>',
        name: 'app_admin.<?= $resourceKey; ?>.post',
        methods: ['POST'],
    )]
    public function postAction(Request $request): Response
    {
        $entity = new <?= $resourceClassName; ?>();
        $this->mapDataFromRequest($request, $entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create($entity));
    }
<?php } ?>
<?php if ($settings->shouldHavePutAction) { ?>

    #[Route(
        '/<?= $resourceKey; ?>/{id}',
        name: 'app_admin.<?= $resourceKey; ?>.put',
        methods: ['PUT'],
    )]
    public function putAction(string $id, Request $request): Response
    {
        $entity = $this->entityManager->find(<?= $resourceClassName; ?>::class, $id);
        if ($entity === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $this->mapDataFromRequest($request, $entity);

        $this->entityManager->flush();

        return $this->viewHandler->handle(View::create($entity));
    }
<?php } ?>
<?php if ($settings->shouldHaveDeleteAction) { ?>

    #[Route(
        '/<?= $resourceKey; ?>/{id}',
        name: 'app_admin.<?= $resourceKey; ?>.delete',
        methods: ['DELETE'],
    )]
    public function deleteAction(string $id): Response
    {
        $entity = $this->entityManager->find(<?= $resourceClassName; ?>::class, $id);
        if ($entity === null) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

<?php if ($settings->shouldHaveTrashing) { ?>
        $this->trashManager->store('<?= $resourceKey; ?>', $entity);
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
