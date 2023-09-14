<?php

declare(strict_types=1);

namespace App\Infrastructure\Slim\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

class ShutdownHandler
{
    private Request $request;

    private HttpErrorHandler $errorHandler;

    private bool $displayErrorDetails;

    public function __construct(
        Request $request,
        HttpErrorHandler $errorHandler,
        bool $displayErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke()
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $message = $this->getErrorMessage($error);
        $exception = new HttpInternalServerErrorException($this->request, $message);
        $response = $this->errorHandler->__invoke(
            $this->request,
            $exception,
            $this->displayErrorDetails,
            false,
            false,
        );

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }

    private function getErrorMessage(array $error): string
    {
        if (!$this->displayErrorDetails) {
            return 'An error while processing your request. Please try again later.';
        }

        $errorFile = $error['file'];
        $errorLine = $error['line'];
        $errorMessage = $error['message'];
        $errorType = $error['type'];

        if ($errorType === E_USER_ERROR) {
            return "FATAL ERROR: {$errorMessage}. on line {$errorLine} in file {$errorFile}.";
        }

        if ($errorType === E_USER_WARNING) {
            return "WARNING: {$errorMessage}";
        }

        if ($errorType === E_USER_NOTICE) {
            return "NOTICE: {$errorMessage}";
        }

        return "FATAL ERROR: {$errorMessage}. on line {$errorLine} in file {$errorFile}.";
    }
}
