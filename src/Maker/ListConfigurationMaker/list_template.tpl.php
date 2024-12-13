<?php

/**
 * @var string $listKey
 * @var string $entityClass
 * @var array<ListPropertyInfo> $properties
 * @var array<ListJoinInfo> $joins
 */

use FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker\ListJoinInfo;
use FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker\ListPropertyInfo;

/** @param array<string, string> $attributes */
function renderAttributes(array $attributes): string {
    return implode(" ", array_map(
        fn (string $key, $value) => $key.'="'.$value.'"',
        array_keys($attributes), array_values($attributes),
    ));
}

echo '<?xml version="1.0" ?>'.PHP_EOL;
?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key><?= $listKey ?></key>
    <properties>
<?php foreach ($properties as $property) {
        $attributes = [
            'name' => $property->name,
            'visibility'=>  $property->visibility->value,
            'translation'=> $property->translations,
        ];
        if ($property->visibility->isVisible()) { $attributes['searchability'] = $property->searchability ? 'yes' : 'no'; }
        if ($property->type){ $attributes['type'] = $property->type; }
?>        <property <?= renderAttributes($attributes); ?>>
            <field-name><?= $property->name ?></field-name>
            <entity-name><?= $entityClass ?></entity-name>
        </property>
<?php } ?>
    </properties>
<?php foreach ($joins as $join) { ?>
    <joins name="<?= $join->name ?>">
        <join>
            <entity-name><?= $join->name ?></entity-name>
            <field-name><?= $join->targetEntity ?></field-name>
            <method><?= $join->joinType->value ?></method>
<?php
    if ($join->condition !== null) {
        echo '                <condition>'.$join->condition.'</condition>'.PHP_EOL;
    }
    if ($join->conditionType !== null) {
    echo '                <condition-type>'.$join->conditionType->value.'</condition-type>'.PHP_EOL;
    }
?>            </join>
    </joins>
<?php } ?>
</list>
