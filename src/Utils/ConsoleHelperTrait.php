<?php

namespace FriendsOfSulu\MakerBundle\Utils;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Webmozart\Assert\Assert;
use BackedEnum;

trait ConsoleHelperTrait
{
    private function askString(ConsoleStyle $io, string $prompt, string $default): string
    {
        /** @var string $result */
        $result = $io->ask($prompt, $default, null);

        return $result;
    }

    /**
     * @template T of BackedEnum $enum
     *
     * @param class-string<T> $enum
     * @param ?T $default
     * @return ($default is null ? T|null : T)
     */
    private function askEnum(ConsoleStyle $io, string $prompt, string $enum, ?BackedEnum $default): ?BackedEnum
    {
        Assert::implementsInterface($enum, BackedEnum::class);
        $options = [];
        if (method_exists($enum, 'descriptions')) {
            $options = [$enum, 'descriptions']();
        } else {
            foreach ([$enum, 'cases']() as $option) {
                $options[$option->value] = $option->name;
            }
        }

        $question = new ChoiceQuestion($prompt, $options, $default?->value);
        /** @var null|string $valueString */
        $valueString = $io->askQuestion($question);
        if ($valueString === null) {
            return $default;
        }

        return [$enum, 'from']($valueString);
    }

    private static function getStringArgument(InputInterface $input, string $key): string
    {
        $result = $input->getArgument($key);
        Assert::string($result, 'Input option: "'. $key. '" should be a string');

        return $result;
    }
}
