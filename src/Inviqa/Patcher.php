<?php

namespace Inviqa;

use Inviqa\Patch\Factory;
use Inviqa\Patch\Patch;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Patcher
{
    const EXTRA_KEY_MAGE_ROOT_DIR = 'magento-root-dir';

    /** @var ConsoleOutput */
    private $output;

    /** @var \Composer\Script\Event  */
    private $event;

    public function patch(\Composer\Script\Event $event)
    {
        $this->init($event);

        $extraTmp = $extra = $this->event->getComposer()->getPackage()->getExtra();

        if (empty($extra['patches'])) {
            $this->output->writeln('<info>No Magento patches were found</info>');
        }

        // don't pass the patch information
        unset($extraTmp['patches']);

        foreach ($extra['patches'] as $patchGroupName => $patchGroup) {
            foreach ($patchGroup as $patchName => $patchDetails) {
                $patch = Factory::create(
                    $patchName,
                    $patchGroupName,
                    $patchDetails,
                    $extraTmp
                );
                $patch->setOutput($this->output);
                $this->applyPatch($patch);
            }
        }
    }

    /**
     * @param \Composer\Script\Event $event
     */
    private function init(\Composer\Script\Event $event)
    {
        $this->output = new ConsoleOutput();

        if ($event->getIo()->isDebug()) {
            $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        $this->event  = $event;

        $checker = new EnvChecker($event);
        $checker->check();
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
