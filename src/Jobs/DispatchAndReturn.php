<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Contracts\Bus\Dispatcher;

trait DispatchAndReturn
{
    public static $response = true;
    public static $error;

    public static function dispatch()
    {
        $object = new static(...func_get_args());
        app(Dispatcher::class)->dispatchNow($object);
        if($object::$response) {
            return self::$response;
        }

        return self::$error;
    }

    public function addResponse($response)
    {
        self::$response = $response;
    }

    public function addError($error)
    {
        self::$response = false;
        self::$error = $error;
    }
}
