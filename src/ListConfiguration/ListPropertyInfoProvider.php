<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Webmozart\Assert\Assert;

class ListPropertyInfoProvider
{
    private ?ConsoleStyle $io = null;

    public function setIo(ConsoleStyle $io): void {
        $this->io = $io;
    }

    /**
     * @param array<ReflectionProperty> $properties
     *
     * @return array<ListPropertyInfo>
     */
    public function provide(array $properties): array {
        Assert::notNull($this->io, 'No io set. Please call '.self::class.'::setIo() before');
        $returnValue = [];
        foreach($properties as $property) {
            if ($property->isStatic()) { continue; }
            $name = $property->getName();

            if (!$this->io->confirm(sprintf('Should this property "%s" be configured', $name))) {
                $this->io->note(sprintf('Property "%s" skipped', $name));
                continue;
            }

            if ($name === 'id') {
                /** @var string $visible */
                $visible = $this->io->choice('When should this property be visible.', ['never', 'yes', 'no'], 'no');

                /** @var string $searchable */
                $searchable = $visible === 'yes' ? $this->io->choice('Searchable', ['yes', 'no'], 'yes') : 'no';

                $returnValue[$name] = new ListPropertyInfo($name, $visible, $searchable, 'sulu_admin.'.$name);
            } else {
                /** @var string $visible */
                $visible = $this->io->choice('When should this property be visible.', ['never', 'yes', 'no'], 'yes');

                /** @var string $searchable */
                $searchable = $visible === 'yes' ? $this->io->choice('Searchable', ['yes', 'no'], 'yes') : 'no';

                /** @var string $translation */
                $translation = $this->io->ask('Translation', 'sulu_admin.'.$name);
                $returnValue[$name] = new ListPropertyInfo($name, $visible, $searchable, $translation);
            }

            $this->io->note(sprintf('Property "%s" added', $name));
        }

        return $returnValue;
    }
}
