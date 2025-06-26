<?php


function handleExceptionError($ex)
{
    return  json_encode([
        "message" => __('auth.processing_error'),
        "errorCode" => 500,
        "originalError" => $ex->getMessage(),
        "originalErrorCode" => $ex->getCode()
    ]);
}

/**
 * Logs an exception and returns a JSON response based on the exception message.
 *
 * @param \Exception $ex The exception to handle.
 * @return \Illuminate\Http\JsonResponse JSON response with the error message and status code.
 */
function exceptionError(Exception $ex)
{
    \Log::error($ex);
    // $message  = json_decode($ex->getMessage(), true);
    return response()->json(['message' => __('auth.processing_error')], 500);
}

    function sendResponse($status=true, $message = '', $data=[], $code = 200) {
		return response([
			'success'=> $status,
			'message' => $message,
			'payload' => $data
		], $code);
    }