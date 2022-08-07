<?php

namespace Mamazu\SuluMaker\Utils;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

trait ConsoleHelperTrait
{
    private function askString(ConsoleStyle $io, string $prompt, ?string $default): string
    {
        /** @var string $result */
        $result = $io->ask($prompt, $default, null);

        return $result;
    }

    private static function getStringArgument(InputInterface $input, string $key): string
    {
        $result = $input->getArgument($key);
        Assert::string($result, 'Input option: "'. $key. '" should be a string');

        return $result;
    }
}
