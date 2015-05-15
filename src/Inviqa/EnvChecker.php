<?php

namespace Inviqa;
use Inviqa\Patch\Factory;
use Inviqa\Patch\Shell;

/**
 * Checks the validity of the environment before applying the patches.
 */
class EnvChecker
{
    private $event;

    public function __construct(\Composer\Script\Event $event)
    {
        $this->event = $event;
    }

    public function check()
    {
        $this->clientDeclaredMageRootOnShellPatches();
    }

    private function clientDeclaredMageRootOnShellPatches()
    {
        $extra = $this->event->getComposer()->getPackage()->getExtra();

        if (empty($extra['patches'])) {
            return;
        }

        $containsShellPatches = false;

        foreach ($extra['patches'] as $patchGroupName => $patchGroup) {
            foreach ($patchGroup as $patchName => $patchDetails) {
                $patch = Factory::create(
                    $patchName,
                    $patchGroupName,
                    $patchDetails,
                    array()
                );

                if ($patch instanceof Shell) {
                    $containsShellPatches = true;
                    break 2;
                }
            }
        }

        if ($containsShellPatches && empty($extra[Patcher::EXTRA_KEY_MAGE_ROOT_DIR])) {
            throw new \Exception(
                'When using shell patches, you must declare the Mage root using the extra key: ' .
                Patcher::EXTRA_KEY_MAGE_ROOT_DIR
            );
        }
    }
}
