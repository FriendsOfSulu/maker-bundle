<?php

use Mamazu\SuluMaker\ListConfiguration\ListPropertyInfo;
/**
 * @var string $listKey
 * @var string $entityClass
 * @var array<ListPropertyInfo> $properties
 */

echo '<?xml version="1.0" ?>';
?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key><?= $listKey ?></key>
    <properties>
<?php foreach($properties as $property) {
    $additionlAttributes = '';
    if ($property->visibility->isVisible()) {
        $searchability = $property->searchability ? 'yes': 'no';
        $additionlAttributes .= sprintf('searchability="%s" ', $searchability);
    }
?>
        <property
            name="<?= $property->name ?>"
            visibility="<?= $property->visibility ?>"
            translation="<?= $property->translations ?>"
            <?= ($property->type) ? ('type="' . $property->type . '"') : '' ?>

         <?= $additionlAttributes ?>
        >
            <field-name><?= $property->name ?></field-name>
            <entity-name><?= $entityClass ?></entity-name>
        </property>
<?php } ?>
    </properties>
</list>
