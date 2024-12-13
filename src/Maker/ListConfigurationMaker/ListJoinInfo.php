<?php

declare(strict_types=1);

namespace FriendsOfSulu\MakerBundle\Maker\ListConfigurationMaker;

class ListJoinInfo
{
    public function __construct(
        public string $name,
        public string $targetEntity,
        public JoinType $joinType,
        public ?string $condition = null,
        public ?ConditionType $conditionType = null,
    ) {
    }
}
