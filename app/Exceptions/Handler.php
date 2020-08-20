<?php

namespace App\Exceptions;

use App\Http\Models\JsonError;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $statusCode = 500;
        $message = env('APP_DEBUG') ? $exception->getMessage() : "Internal Server Error";
        $errors = [new JsonError($message, $message)];
        $data = null;

        if ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "Resource not Found";
            $errors = [new JsonError($message, "Resource not Found")];
        } else if ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $message = "Resource not Found";
            $errors = [new JsonError($message, $message)];
        } else if ($exception instanceof AuthenticationException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 401;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "Authentication Error";
            $errors = [new JsonError("Authentication Error", $message)];
        } else if ($exception instanceof AuthorizationException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 401;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "Authorization Error";
            $errors = [new JsonError("Authorization Error", $message)];
        } else if ($exception instanceof UnauthorizedException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 401;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "The user is not authorized to perform this action";
            $errors = [new JsonError("Authorization Error", $message)];
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 405;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "This method is not supported for this route";
            $errors = [new JsonError("This method is not supported for this route", $message)];
        } else if ($exception instanceof ConflictHttpException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 409;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "Request conflict with current state of the server";
            $errors = [new JsonError("Request conflict with current state of the server", $message)];
        } else if ($exception instanceof ValidationException) {
            $statusCode = ($exception->getCode() > 0) ? $exception->getCode() : 422;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : "Validation Error";
            $errors = $this->prepareValidationErrors($exception);
        } else if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode() ?? 500;
            $message = !empty($exception->getMessage()) ? $exception->getMessage() : $message;
            $errors = [new JsonError($message, $message)];
        } else if ($exception instanceof ThrottleRequestsException) {

            $statusCode = $exception->getStatusCode() ?? 500;
            $message = $exception->getMessage();
            $errors = [new JsonError($message, $message)];
        }

        return response()->errorJson($statusCode, ["type" => "error"], $errors, [
            "statusCode" => $statusCode,
            "message" => $message
        ]);
    }

    /**
     * Renders appropriate array of errors
     * @param ValidationException $exception
     * @return array
     */
    private function prepareValidationErrors(ValidationException $exception): array
    {
        $errors = [];
        foreach ($exception->errors() as $key => $errorMessages) {
            foreach ($errorMessages as $message) {
                array_push($errors, new JsonError($key, $message));
            }
        }
        return $errors;
    }

}
