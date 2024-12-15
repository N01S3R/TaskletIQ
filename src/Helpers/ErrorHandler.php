<?php

namespace App\Helpers;

use App\View;

class ErrorHandler
{
    public static function handleException(\Throwable $exception)
    {
        self::logException($exception);

        if (getenv('APP_ENV') === 'production') {
            http_response_code(500);
            View::render('500_page', ['message' => 'Something went wrong, please try again later.']);
        } else {
            http_response_code(500);
            View::render('500_page', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function register()
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
                self::handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    private static function logException(\Throwable $exception)
    {
        $logFile = __DIR__ . '/../logs/error.log';
        $message = sprintf(
            "[%s] %s in %s:%d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
