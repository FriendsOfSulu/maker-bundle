<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use Mamazu\SuluMaker\Objects\ListConfiguration;

class XmlGenerator
{
    public function generateProperty(string $entityClass, ConfigurationPDO $listConfiguration): string
    {
        $name = $listConfiguration->name;
        $visibility = $listConfiguration->visibility;
        $translation = $listConfiguration->translations;

        return <<<XML
    <property name="$name" visibility="$visibility" translation="$translation">
        <field-name>$name</field-name>
        <entity-name>$entityClass</entity-name>
    </property>
XML;
    }

    /**
     * @param array<ListConfiguration> $properties
     */
    public function generate(string $listKey, string $entityClass, array $properties): string {
        $properties = implode(
            "\n",
            array_map(
                fn($listConfiguration) => $this->generateProperty($entityClass, $listConfiguration),
                $properties
            ));
        return <<<XML
<?xml version="1.0" ?>
<list xmlns="http://schemas.sulu.io/list-builder/list">
    <key>$listKey</key>
    <properties>
    $properties
    </properties>
</list>
XML;
    }
}
