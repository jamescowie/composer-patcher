<?php

namespace Inviqa\Patch;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

abstract class Patch
{
    private $group;

    private $name;

    private $title;

    private $description;

    private $url;

    private $tempPatchFilePath;

    /**
     * @var Output
     */
    private $output;

    private $log;

    /**
     * @var array
     */
    private $composerExtra = array();

    /**
     * @return boolean
     */
    abstract protected function doApply();

    /**
     * @return boolean
     */
    abstract protected function canApply();

    public function __construct($name, $group, array $details, array $composerExtra)
    {
        $this->setName($name);
        $this->setGroup($group);
        $this->setComposerExtra($composerExtra);

        if (!empty($details['url'])) {
            $this->setUrl($details['url']);
        }
    }

    /**
     * @return boolean|null
     * @throws \Exception
     */
    public final function apply()
    {
        $namespace = $this->getNamespace();
        if ($this->canApply()) {
            $this->beforeApply();
            $res = (bool) $this->doApply();

            if ($res) {
                $this->getOutput()->writeln("<info>Patch $namespace successfully applied.</info>");
            } else {
                $this->getOutput()->writeln("<comment>Patch $namespace was not applied.</comment>");
            }

            $this->afterApply($res);

            return $res;
        }
        $this->getOutput()->writeln("<comment>Patch $namespace skipped. Patch was already applied?</comment>");
        return null;
    }

    protected function beforeApply()
    {}

    protected function afterApply($patchingWasSuccessful)
    {}

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->getGroup() . '/' . $this->getName();
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    protected function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    protected function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    protected function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    protected function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    protected function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getPatchTemporaryPath()
    {
        if (is_null($this->tempPatchFilePath)) {
            $this->getOutput()->writeln("<info>Fetching patch {$this->getNamespace()}</info>");

            if (!$this->getUrl()) {
                return $this->tempPatchFilePath = '';
            }

            if (!$patch = file_get_contents($this->getUrl())) {
                throw new \Exception("Could not get contents from {$this->getUrl()}");
            }

            $patchFilePath = $this->getPatchTempAbsolutePath();
            if (!file_put_contents($patchFilePath, $patch)) {
                throw new \Exception("Could not save patch content to $patchFilePath");
            }

            $this->tempPatchFilePath = $patchFilePath;
        }

        return $this->tempPatchFilePath;
    }

    /**
     * @return string
     */
    private function getPatchTempAbsolutePath()
    {
        // digest unsafe characters
        return sys_get_temp_dir() . '/mage_patch_' . md5($this->getGroup() . $this->getName()) . '.tmp';
    }

    /**
     * @return Output
     */
    public function getOutput()
    {
        if (!$this->output) {
            $this->output = new ConsoleOutput();
        }
        return $this->output;
    }

    /**
     * @param Output $output
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    /**
     * @return Output
     */
    public function getComposerExtra()
    {
        return $this->composerExtra;
    }

    /**
     * @param array $composerExtra
     */
    private function setComposerExtra(array $composerExtra)
    {
        $this->composerExtra = $composerExtra;
    }
}
