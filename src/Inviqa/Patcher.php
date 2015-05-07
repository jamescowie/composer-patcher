<?php

namespace Inviqa;

use Inviqa\Downloader\Composer as composerDownloader;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use \Symfony\Component\Process\ProcessUtils;

class Patcher
{
    private $patchFiles = [];

    /** @var ConsoleOutput */
    private $output;

    /** @var \Composer\Script\Event  */
    private $event;

    public function patch(\Composer\Script\Event $event)
    {
        $this->output = new ConsoleOutput();
        $this->event  = $event;

        $this->fetchPatches();
        $this->applyPatches();
    }

    private function fetchPatches()
    {
        $downloader = new composerDownloader();
        $extra = $this->event->getComposer()->getPackage()->getExtra();

        foreach ($extra['patches'] as $patchGroupName => $patchGroup) {
            foreach ($patchGroup as $patchName => $patchInfo) {
                $patchNamespace = $patchGroupName . '/' . $patchName;
                $this->output->writeln("<info>Fetching patch $patchNamespace</info>");
                $patchContent = $downloader->getContents($patchInfo['url'], $patchGroupName . '_' . $patchName);
                $this->patchFiles[$patchNamespace] = $patchContent;
            }
        }
    }

    private function applyPatches()
    {
        $this->output->writeln("<info>Applying patches...</info>");

        foreach ($this->patchFiles as $patchNamespace => $filesToPatch) {
            if (!$this->canApplyPatch($filesToPatch)) {
                $this->output->writeln('<comment>Patch skipped. Patch was already applied?</comment>');
                continue;
            }

            $process = new Process("patch -p 1 < " . ProcessUtils::escapeArgument($filesToPatch));
            try {
                $process->mustRun();
                $this->output->writeln("<info>Patch $patchNamespace successfully applied.</info>");
            } catch (\Exception $e) {
                $this->output->getErrorOutput()->writeln("<error>Error applying patch $patchNamespace:</error>");
                $this->output->getErrorOutput()->writeln("<error>{$e->getMessage()}</error>");
            }
        }
    }

    /**
     * @param $filesToPatch
     * @return bool
     */
    private function canApplyPatch($filesToPatch)
    {
        $process = new Process("patch --dry-run -p 1 < " . ProcessUtils::escapeArgument($filesToPatch));
        try {
            $process->mustRun();
            return $process->getExitCode() === 0;
        } catch (ProcessFailedException $e) {
            return false;
        }
    }
}
