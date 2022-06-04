<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AccessException extends Exception
{

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->api_fail($this->getMessage());
    }
}
