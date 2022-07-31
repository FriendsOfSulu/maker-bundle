<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;

class PropertyInfoProvider
{
    private ?ConsoleStyle $io = null;

    public function setIo(ConsoleStyle $io): void {
        $this->io = $io;
    }

    /**
     * @param array<ReflectionProperty> $properties
     *
     * @return array<ConfigurationPDO>
     */
    public function provide(array $properties): array {
        $returnValue = [];
        foreach($properties as $property) {
            if ($property->isStatic()) { continue; }
            $name = $property->getName();

            if (!$this->io->confirm(sprintf('Should this property "%s" be configured', $name))) {
                $this->io->note(sprintf('Property "%s" skipped', $name));
                continue;
            }

            if ($name === 'id') {
                $visible = $this->io->choice('When should this property be visible.', ['never', 'yes', 'no'], 'no');

                $returnValue[$name] = new ConfigurationPDO(
                    $name,
                    $visible,
                    $visible === 'yes' ? $this->io->choice('Searchable', ['yes', 'no'], 'yes') : 'no',
                    'sulu_admin.'.$name,
                );
            } else {
                $visible = $this->io->choice('When should this property be visible.', ['never', 'yes', 'no'], 'yes');

                $returnValue[$name] = new ConfigurationPDO(
                    $name,
                    $visible,
                    $visible === 'yes' ? $this->io->choice('Searchable', ['yes', 'no'], 'yes') : 'no',
                    $this->io->ask('Translation', 'sulu_admin.'.$name),
                );
            }

            $this->io->note(sprintf('Property "%s" added', $name));
        }

        return $returnValue;
    }
}
