<?php

namespace Ipaynow\Notify;

final class NotifyAck
{
    private function __construct()
    {
    }

    public static function success()
    {
        return 'success=Y';
    }

    public static function fail()
    {
        return 'success=N';
    }
}
