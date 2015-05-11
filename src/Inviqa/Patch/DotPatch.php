<?php

namespace Inviqa\Patch;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DotPatch extends Patch
{
    const TYPE = 'patch';

    /**
     * @throws ProcessFailedException
     * @return boolean
     */
    protected function doApply()
    {
        $patchPath = ProcessUtils::escapeArgument($this->getPatchTemporaryPath());
        $process = new Process("patch -p 1 < $patchPath");
        $process->mustRun();
        return $process->getExitCode() === 0;
    }

    protected function canApply()
    {
        $patchPath = ProcessUtils::escapeArgument($this->getPatchTemporaryPath());
        $process = new Process("patch --dry-run -p 1 < $patchPath");
        try {
            $process->mustRun();
            return $process->getExitCode() === 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
