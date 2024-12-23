<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Property;

interface PropertyToSuluTypeGuesserInterface
{
    /**
     * Returns the types based on the doctrine field type.
     *
     * @return array<null|string, string>
     */
    public function getPossibleTypes(string $doctrineType): array;
}
