<?php

namespace Inviqa\Patch;

use Inviqa\Patcher;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Shell extends Patch
{
    const TYPE = 'shell';

    const SHELL_SCRIPT_TMP_NAME = 'mage_shell_patch.sh';

    private $shellScriptTmpPath;

    /**
     * @throws ProcessFailedException
     * @return boolean
     */
    protected function doApply()
    {
        $patchPath = ProcessUtils::escapeArgument($this->shellScriptTmpPath);
        $process = new Process("sh $patchPath");
        $process->mustRun();
        return $process->getExitCode() === 0;
    }

    /**
     * Official Magento patches check if they can be applied beforehand,
     * so no need to check it ourselves.
     *
     * @return bool
     */
    protected function canApply()
    {
        return true;
    }

    /**
     * Magento "sh" patch-scripts need to be in the Mage root when applying.
     *
     * @throws \Exception
     */
    protected function beforeApply()
    {
        $tempPath = $this->getPatchTemporaryPath();
        $extra = $this->getComposerExtra();

        $mageDir = $extra[Patcher::EXTRA_KEY_MAGE_ROOT_DIR];

        $destinationFilePath = realpath("./$mageDir") . '/' . self::SHELL_SCRIPT_TMP_NAME;

        if (!@rename($tempPath, $destinationFilePath)) {
            throw new \Exception("Could not move form $tempPath to $destinationFilePath");
        }

        if ($this->getOutput()->isDebug()) {
            $this->getOutput()->writeln("Shell script moved from $tempPath to $destinationFilePath");
        }

        $this->shellScriptTmpPath = $destinationFilePath;
    }

    protected function afterApply($patchWasOk)
    {
        if (file_exists($this->shellScriptTmpPath)) {
            if ($this->getOutput()->isDebug()) {
                $this->getOutput()->writeln("Deleting {$this->shellScriptTmpPath}");
            }
            @unlink($this->shellScriptTmpPath);
        }
    }
}
