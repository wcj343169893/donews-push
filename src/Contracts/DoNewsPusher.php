<?php
namespace Mofing\DoNewsPush\Contracts;

interface DoNewsPusher
{
    public function send($deviceToken, $title, $message, $platform, $type, $after_open, $customize);
}