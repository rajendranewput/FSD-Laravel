<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
   

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Custom report logic if needed
        });
    }

    /**
     * Report the exception.
     */
    public function report(Throwable $exception)
    {
        $this->reportException($exception);
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        return $this->handleException($request, $exception);
    }

    /**
     * Handle different types of exceptions.
     */
   protected function handleException(Request $request, Throwable $exception)
    {
        $statusCode = config('constants.HTTP_INTERNAL_SERVER_ERROR');
        $message = __('exceptions.default');

        if ($exception instanceof NotFoundHttpException) {
            $message = __('exceptions.not_found');
            $statusCode = config('constants.HTTP_NOT_FOUND');
        } elseif ($exception instanceof ModelNotFoundException) {
            $model = app($exception->getModel());
            $message = method_exists($model, 'notFoundMessage') ? $model->notFoundMessage() : __('exceptions.model_not_found');
            $statusCode = config('constants.HTTP_NOT_FOUND');
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $message = __('exceptions.method_not_allowed');
            $statusCode = config('constants.HTTP_METHOD_NOT_ALLOWED');
        } elseif ($exception instanceof HttpException) {
            $message = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof AuthenticationException) {
            $message = __('exceptions.session_expired');
            $statusCode = config('constants.HTTP_UNAUTHORIZED');
        } 

        return sendResponse(false, $message, [], $statusCode);
    }


    /**
     * Log the exception details.
     */
    protected function reportException(Throwable $exception)
    {
        $errorData = [
            'message' => $exception->getMessage(),
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : $exception->getCode(),
        ];

        Log::channel('stderr')->error('Exception caught', $errorData);
    }
}
