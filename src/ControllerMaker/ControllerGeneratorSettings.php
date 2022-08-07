<?php

namespace Mamazu\SuluMaker\ControllerMaker;

class ControllerGeneratorSettings
{
    public bool $shouldHaveGetListAction = true;

    public bool $shouldHaveGetAction = true;

    public bool $shouldHavePostAction = true;

    public bool $shouldHavePutAction = true;

    public bool $shouldHaveDeleteAction = true;

    public function needsEntityManager(): bool
    {
        return $this->shouldHaveGetAction
            || $this->shouldHavePostAction
            || $this->shouldHavePutAction
            || $this->shouldHaveDeleteAction;
    }

    public function hasUpdateActions(): bool
    {
        return $this->shouldHavePutAction || $this->shouldHavePostAction;
    }
}
