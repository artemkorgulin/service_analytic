<?php

namespace App\Services\dist;

use Throwable;

class Custom extends \Exception
{
    public $advanced;

    public function __construct($message, $advanced, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
    }


    public function render($request)
    {
        return response()->json([
            'error' => $this->advanced
        ]);
    }
}
