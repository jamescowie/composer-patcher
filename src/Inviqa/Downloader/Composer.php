<?php

namespace Inviqa\Downloader;

class Composer
{
    public function getContents($url, $name)
    {
        $patch = file_get_contents($url);
        $fileName = sys_get_temp_dir() . "/$name.patch";
        file_put_contents($fileName, $patch);

        return $fileName;
    }

}
