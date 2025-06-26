<?php

namespace App\Exception;

use Exception;

class CafemanagerApiException extends Exception
{
    /**
     * Custom Cafemanager API Exception
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "Cafemanager API Error", $code = 500)
    {
        parent::__construct($message, $code);
    }

    /**
     * Report the exception.
     */
    public function report()
    {
        return false; // prevent auto reporting unless manually triggered
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 500);
    }
}
