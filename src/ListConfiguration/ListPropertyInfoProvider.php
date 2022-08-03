<?php

namespace Mamazu\SuluMaker\ListConfiguration;

use Mamazu\SuluMaker\Enums\Visibility;
use Mamazu\SuluMaker\Property\PropertyToSuluTypeGuesser;
use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Webmozart\Assert\Assert;

class ListPropertyInfoProvider
{
    public function __construct(
        private /* readonly */ PropertyToSuluTypeGuesser $typeGuesser
    ) {

    }

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

        $returnValue[] = new ListPropertyInfo('id', Visibility::from(Visibility::NO), false, 'sulu_admin.id');

        foreach($properties as $property) {
            $name = $property->getName();
            if ($property->isStatic() || $name === 'id') { continue; }

            $this->io->info(sprintf('Configuring property: "%s"', $name));
            if (!$this->io->confirm(sprintf('Should this property "%s" be configured', $name))) {
                $this->io->info(sprintf('Property "%s" skipped', $name));
                continue;
            }

            $visibility = Visibility::from($this->io->choice('Visible?', Visibility::descriptions(), 'yes'));

            $searchable = false;
            if ($visibility->isVisible()) {
                $searchable = $this->io->confirm('Searchable?');
            }

            $type = $this->getType($property);

            /** @var string $translation */
            $translation = $this->io->ask('Translation', 'sulu_admin.'.$name);
            $returnValue[$name] = new ListPropertyInfo( $name, $visibility, $searchable, $translation, $type);
        }

        return $returnValue;
    }

    private function getType(ReflectionProperty $property): ?string {
        Assert::notNull($this->io, 'No io set. Please call '.self::class.'::setIo() before');

        if ($property->getType() === null) {
            $this->io->note('There is no PHP type configured for this property. Assuming it is a string.');
            return null;
        }

        $possibleTypes = $this->typeGuesser->getPossibleTypes($property);
        if ($possibleTypes === []) {
            $this->io->note('Could not find any suggestions for the PHP Type of the property. You can extend the class '. PropertyToSuluTypeGuesser::class. ' for smarter type guessing.');
            return null;
        }

        if (count($possibleTypes) === 1) {
            $keys = array_keys($possibleTypes);
            $type = reset($keys);
            $description = reset($possibleTypes);
            $this->io->info(sprintf('Choosing the only possible type: %s (%s)', $type, $description));

            return $type;
        }

        /** @var string|null $type */
        $type = $this->io->choice('Sulu display type', $possibleTypes);

        if ($type === null) {
            $keys = array_keys($possibleTypes);
            $type = reset($keys);
            $description = reset($possibleTypes);
            $this->io->info(sprintf('Choosing the best guess: %s (%s)', $type, $description));
        }

        return $type;
    }

}
