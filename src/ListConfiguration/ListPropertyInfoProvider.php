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

        $returnValue[] = new ListPropertyInfo('id', false, false, 'sulu_admin.id');

        foreach($properties as $property) {
            $name = $property->getName();
            if ($property->isStatic() || $name === 'id') { continue; }

            if (!$this->io->confirm(sprintf('Should this property "%s" be configured', $name))) {
                $this->io->note(sprintf('Property "%s" skipped', $name));
                continue;
            }

            /** @var string $visible */
            $visible = $this->io->choice('Visible?', ['yes', 'no']);

            /** @var string $searchable */
            $searchable = $visible === 'yes' ? $this->io->choice('Searchable?', ['yes', 'no', 'hidden'], 'yes') : 'no';

            /** @var string|null $type */
            $type = $this->io->ask('Type (leave empty if it is a primitive type)', null);

            /** @var string $translation */
            $translation = $this->io->ask('Translation', 'sulu_admin.'.$name);
            $returnValue[$name] = new ListPropertyInfo(
                $name,
                $searchable !== 'hidden',
                $searchable === 'yes',
                $translation,
                $type
            );

            $this->io->note(sprintf('Property "%s" added', $name));
        }

        return $returnValue;
    }
}
