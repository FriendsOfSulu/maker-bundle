<?php
/**
 * @var string $pageKey
 * @var string $viewPath
 * @var string $controller
 * @var string $pageName
 */
echo '<?xml version="1.0" ?>';
?>
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xmlns:xi="http://www.w3.org/2001/XInclude"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template-1.0.xsd">

    <key><?= $pageKey; ?></key>

    <view><?= $viewPath; ?></view>
    <controller><?= $controller; ?></controller>
    <cacheLifetime>86400</cacheLifetime>

    <meta>
        <title><?= $pageName; ?></title>
    </meta>

    <properties>
        <property name="title" type="text_line" mandatory="true">
            <meta>
                <title>sulu_admin.title</title>
            </meta>
            <params>
                <param name="headline" value="true"/>
            </params>

            <tag name="sulu.rlp.part"/>
        </property>

        <property name="url" type="resource_locator" mandatory="true">
            <meta>
                <title>sulu_admin.url</title>
            </meta>
            <tag name="sulu.rlp"/>
        </property>

        <!-- Your properties go here! -->
    </properties>
</template>

