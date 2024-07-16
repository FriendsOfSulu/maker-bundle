<?php
/** @var FriendsOfSulu\MakerBundle\Maker\AdminConfigurationMaker\AdminGeneratorSettings $settings */
/** @var string $resourceKey */
/** @var string $namespace */
/** @var string $class_name */
/** @var string $use_statements */
$translationKey = 'app.'.$resourceKey;
$slug = $settings->slug;

echo "<?php\n";
?>

namespace <?= $namespace; ?>;

<?= $use_statements; ?>

class <?= $class_name ?> extends Admin
{
    public const SECURITY_CONTEXT = '<?= $translationKey; ?>';

    public const LIST_VIEW = '<?= $translationKey; ?>.list_view';
<?php if ($settings->shouldHaveEditForm) { ?>

    public const EDIT_FORM_VIEW = '<?= $translationKey; ?>.edit_form';
<?php } ?>
<?php if ($settings->shouldHaveEditForm) { ?>

    public const ADD_FORM_VIEW = '<?= $translationKey; ?>.add_form';
<?php } ?>

    public function __construct(
        private SecurityCheckerInterface $securityChecker,
        private ViewBuilderFactoryInterface $viewBuilderFactory
<?php if ($settings->shouldHaveReferences) { ?>
        private ReferenceViewBuilderFactoryInterface $referenceViewBuilderFactory,
<?php } ?>
    ) {}

<?php if ($settings->shouldAddMenuItem) { ?>
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if (!$this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $menuItem = new NavigationItem('app.menu.<?= $resourceKey ?>');
        $menuItem->setView(static::LIST_VIEW);

        $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($menuItem);
    }
<?php } ?>

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $formToolbarActions = [
<?php foreach($settings->formToolbarActions as $actionName) { ?>
            new ToolbarAction('sulu_admin.<?= $actionName ?>'),
<?php } ?>
        ];
        $listToolbarActions = [
<?php foreach($settings->listToolbarActions as $actionName) { ?>
            new ToolbarAction('sulu_admin.<?= $actionName ?>'),
<?php } ?>
        ];

        // View that displays the table of all entities
        $viewCollection->add(
            $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '<?= $slug ?>')
                ->setResourceKey(<?= $resourceKey ?>)
                ->setListKey('<?= $settings->listKey ?>')
                ->setTitle('<?= $translationKey ?>')
                ->addListAdapters(['table'])
                ->setAddView(static::ADD_FORM_VIEW)
<?php if ($settings->shouldHaveEditForm) { ?>
                ->setEditView(static::EDIT_FORM_VIEW)
<?php } ?>
                ->addToolbarActions($listToolbarActions)
        );

<?php if ($settings->shouldHaveAddForm) { ?>
        // Add form for the resource
        $viewCollection->add(
            $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '<?= $slug ?>/add')
                ->setResourceKey(<?= $resourceKey ?>)
                ->setBackView(static::LIST_VIEW)
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder(self::ADD_FORM_VIEW.'.details', '/details')
                ->setResourceKey('<?= $resourceKey ?>')
                ->setFormKey('<?= $settings->formKey ?>')
                ->setTabTitle('sulu_admin.details')
<?php if ($settings->shouldHaveEditForm) { ?>
                ->setEditView(static::EDIT_FORM_VIEW)
<?php } ?>
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_VIEW)
        );
<?php } ?>

<?php if ($settings->shouldHaveEditForm) { ?>
        // Edit form view
        $viewCollection->add(
            $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '<?= $slug ?>/:id')
                ->setResourceKey('<?= $resourceKey ?>')
                ->setBackView(static::LIST_VIEW)
                ->setTitleProperty('name')
        );
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder(self::EDIT_FORM_VIEW.'.details', '/details')
                ->setResourceKey('<?= $resourceKey ?>')
                ->setFormKey('<?= $settings->formKey ?>')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW)
        );
    <?php } ?>

<?php if ($settings->shouldHaveReferences) { ?>
            if ($this->referenceViewBuilderFactory->hasReferenceListPermission()) {
                $insightsResourceTabViewName = static::EDIT_TABS_VIEW.'.insights';

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createResourceTabViewBuilder($insightsResourceTabViewName, '/insights')
                        ->setResourceKey(ListingTile::RESOURCE_KEY)
                        ->setTabOrder(6144)
                        ->setTabTitle('sulu_admin.insights')
                        ->setTitleProperty('')
                        ->setParent(static::EDIT_TABS_VIEW),
                );

                $viewCollection->add(
                    $this->referenceViewBuilderFactory
                        ->createReferenceListViewBuilder(
                            $insightsResourceTabViewName.'.reference',
                            '/references',
                            ListingTile::RESOURCE_KEY,
                        )
                        ->setParent($insightsResourceTabViewName),
                );
            }
    <?php } ?>
    }

    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Settings' => [
                    static::SECURITY_CONTEXT => [
<?php foreach($settings->permissionTypes as $permissionType) { ?>
                        PermissionTypes::<?= $permissionType ?>,
<?php } ?>
                    ],
                ],
            ],
        ];
    }

}
