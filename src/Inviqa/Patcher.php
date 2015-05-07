<?php

namespace Inviqa;

use Inviqa\Downloader\Composer as composerDownloader;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Patcher
{
    private $patchFiles = [];

    /** @var ConsoleOutput */
    private $output;

    public function patch(\Composer\Script\Event $event)
    {
        $this->output = new ConsoleOutput();

        $extra = $event->getComposer()->getPackage()->getExtra();

        foreach ($extra['patches']['magento'] as $option) {
            $this->output->writeln("<info>Downloading patch: " . $option['name'] . "</info>");
            $downloader = new composerDownloader();
            $this->patchFiles[] = $downloader->getContents($option['url'], $option['name']);
        }

        $this->applyPatch();
    }

    private function applyPatch()
    {
        $this->output->writeln("<info>Applying Patch</info>");

        foreach ($this->patchFiles as $filesToPatch)
        {
            if (!$this->canApplyPatch($filesToPatch)) {
                $this->output->writeln('<comment>Patch skipped. Patch was already applied?</comment>');
                continue;
            }

            $process = new Process("patch -p 1 < " . $filesToPatch);
            try {
                $process->mustRun();

                echo $process->getOutput();
            } catch (ProcessFailedException $e) {
                echo $e->getMessage();
            }

            $this->output->writeln("<info>File successfully patched.</info>");
        }
    }

    /**
     * @param $filesToPatch
     * @return bool
     */
    private function canApplyPatch($filesToPatch)
    {
        $process = new Process("patch --dry-run -p 1 < " . $filesToPatch);
        try {
            $process->mustRun();
            return $process->getExitCode() === 0;
        } catch (ProcessFailedException $e) {
            return false;
        }
    }
}
