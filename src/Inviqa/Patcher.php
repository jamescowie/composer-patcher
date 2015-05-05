<?php

namespace Inviqa;

use Inviqa\Downloader\Composer as composerDownloader;
use Symfony\Component\Console\Output\ConsoleOutput;

class Patcher
{
    private $patchFiles = [];

    /** @var ConsoleOutput */
    private $output;

    public function patchMage()
    {
        $this->output = new ConsoleOutput();

        $reader = new \Eloquent\Composer\Configuration\ConfigurationReader;
        $configuration = $reader->read('composer.json');

        foreach ($configuration->extra()->patches->magento as $option) {
            $options = json_decode(json_encode($option), true);
            $this->output->writeln("<info>Downloading patch: " . $options['name'] . "</info>");
            $downloader = new composerDownloader();
            $this->patchFiles[] = $downloader->getContents($options['url'], $options['name']);
        }

        //var_dump($this->patchFiles);
        $this->applyPatch();
    }

    private function applyPatch()
    {
        $this->output->writeln("<info>Applying Patch</info>");
    }
}
