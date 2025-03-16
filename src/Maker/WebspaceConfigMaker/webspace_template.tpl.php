<?php
/**
 * @var string $webspaceKey
 * @var string $webspaceName
 */
?>
<?xml version="1.0" encoding="utf-8"?>
<webspace xmlns="http://schemas.sulu.io/webspace/webspace"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/webspace/webspace http://schemas.sulu.io/webspace/webspace-1.1.xsd">
    <!-- See: http://docs.sulu.io/en/latest/book/webspaces.html how to configure your webspace-->

    <key><?= $webspaceKey; ?></key>
    <name><?= $webspaceName; ?></name>

    <localizations>
        <!-- See: http://docs.sulu.io/en/latest/book/localization.html how to add new localizations -->
        <localization language="en" default="true"/>
    </localizations>

    <default-templates>
        <default-template type="page">default</default-template>
        <default-template type="home">homepage</default-template>
    </default-templates>

    <!-- See: https://docs.sulu.io/en/latest/book/webspaces.html#excluded-templates-optional
    <excluded-templates>
        <excluded-template>other</excluded-template>
    </excluded-templates>
    -->

    <templates>
        <template type="search">search/search</template>
        <!-- See: http://docs.sulu.io/en/latest/cookbook/custom-error-page.html how to create a custom error page -->
        <template type="error">error/error</template>
    </templates>

    <navigation>
        <contexts>
            <context key="main">
                <meta>
                    <title lang="en">Main Navigation</title>
                    <title lang="de">Hauptnavigation</title>
                </meta>
            </context>
        </contexts>
    </navigation>

    <portals>
        <portal>
            <name>Website</name>
            <key>website</key>

            <environments>
                <environment type="prod">
                    <urls>
                        <url language="en">{host}</url>
                    </urls>
                </environment>
                <environment type="stage">
                    <urls>
                        <url language="en">{host}</url>
                    </urls>
                </environment>
                <environment type="test">
                    <urls>
                        <url language="en">{host}</url>
                    </urls>
                </environment>
                <environment type="dev">
                    <urls>
                        <url language="en">{host}</url>
                    </urls>
                </environment>
            </environments>
        </portal>
    </portals>
</webspace>
