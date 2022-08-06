<?php

namespace Mamazu\SuluMaker\Utils;

use Symfony\Bundle\MakerBundle\ConsoleStyle;

trait ConsoleHelperTrait
{
    private function askString(ConsoleStyle $io, string $prompt, ?string $default): string
    {
        /** @var string $result */
        $result = $io->ask($prompt, $default, null);

        return $result;
    }
}
