<?php

namespace Inviqa;

use Inviqa\Patch\Factory;
use Inviqa\Patch\Patch;
use Symfony\Component\Console\Output\ConsoleOutput;

class Patcher
{
    /** @var ConsoleOutput */
    private $output;

    /** @var \Composer\Script\Event  */
    private $event;

    public function patch(\Composer\Script\Event $event)
    {
        $this->output = new ConsoleOutput();
        $this->event  = $event;

        $extra = $this->event->getComposer()->getPackage()->getExtra();
        foreach ($extra['patches'] as $patchGroupName => $patchGroup) {
            foreach ($patchGroup as $patchName => $patchDetails) {
                $patch = Factory::create($patchName, $patchGroupName, $patchDetails);
                $patch->setOutput($this->output);
                $this->applyPatch($patch);
            }
        }
    }

    private function applyPatch(Patch $patch)
    {
        try {
            $patch->apply();
        } catch (\Exception $e) {
            $this->output->writeln("<error>Error applying patch {$patch->getNamespace()}:</error>");
            $this->output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
