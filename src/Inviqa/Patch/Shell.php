<?php

namespace Inviqa\Patch;

class Shell extends Patch
{
    const TYPE = 'shell';

    protected function doApply()
    {
        return true;
    }

    protected function canApply()
    {
        return true;
    }
}
