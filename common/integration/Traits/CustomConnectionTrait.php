<?php

namespace common\integration\Traits;

use common\integration\Override\CustomBuilder;

trait CustomConnectionTrait
{
    //@Override
    public function query(): CustomBuilder
    {
        return new CustomBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}