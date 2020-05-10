<?php

namespace Melanth\Http\Exceptions;

use Exception;
use Melanth\Http\Response;

class NotFoundHttpException extends HttpException
{
    /**
     * Create a new HTTP not found exception instance.
     *
     * @param string     $message  The error message.
     * @param \Exception $previous The previous exception instance.
     * @param array      $headers  The header entities.
     * @param int        $code     The internal exception code.
     *
     * @return void
     */
    public function __construct(
        string $message = null, Exception $previous = null, array $headers = [], int $code = 0
    ) {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $previous, $headers, $code);
    }
}
