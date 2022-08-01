<?php

namespace Mamazu\SuluMaker\ListConfiguration;

class XmlGenerator
{
    public function generateProperty(string $entityClass, ListPropertyInfo $listConfiguration): string
    {
        $name = $listConfiguration->name;
        $visibility = $listConfiguration->visibility;
        $translation = $listConfiguration->translations;
        $additionlAttributes = '';
        if ($listConfiguration->type !== null) {
            $additionlAttributes .= 'type="' . $listConfiguration->type . '" ';
        }

        return <<<XML
    <property name="$name" visibility="$visibility" translation="$translation" $additionlAttributes>
        <field-name>$name</field-name>
        <entity-name>$entityClass</entity-name>
    </property>
XML;
    }

    /**
     * @param array<ListPropertyInfo> $properties
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
