<?php

namespace Inviqa;

class Command
{
    /**
     * Mehotd used as Composer script callback.
     * E.g. { "scripts": { "post-update-cmd": "Inviqa\\Command::patch" } }
     *
     * The \Composer\Script\Event type is accessible only for Composer's "Command Events".
     * These are the only events when the "Composer" object (hence its package config)
     * is available.
     *
     * @link https://getcomposer.org/doc/articles/scripts.md#event-names
     * @param \Composer\Script\Event $event
     */
    public static function patch(\Composer\Script\Event $event)
    {
        $patcher = new Patcher;
        $patcher->patch($event);
    }
}
