<?php

namespace Melanth\Http;

use Exception;
use Melanth\Foundation\Application;
use Melanth\Http\Exceptions\HttpException;

class ErrorHandler
{
    /**
     * The application instance.
     *
     * @var \Melanth\Foundation\Application
     */
    protected $app;

    /**
     * Create a new error handler instance.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @var void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Render an error exception to a response.
     *
     * @param \Exception $e An error exception instance.
     *
     * @return \Melanth\Http\Response
     */
    public function render(Exception $e) : Response
    {
        return $this->prepareResponse($e);
    }

    /**
     * Prepare the response with an exception.
     *
     * @param \Exception $e An error exception instance.
     *
     * @return \Melanth\Http\Response
     */
    protected function prepareResponse(Exception $e) : Response
    {
        [$content, $statusCode, $headers] = [$e->getMessage(), 500, []];

        if ($this->isHttpException($e)) {
            [$statusCode, $headers] = [$e->getStatusCode(), $e->getHeaders()];
        }

        if ($this->app['request']->isJson()) {
            $content = $this->convertExceptionContent($e);
        }

        return Response::create($content, $statusCode, $headers);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param \Exception $e An exception instance.
     *
     * @return array
     */
    protected function convertExceptionContent(Exception $e) : array
    {
        return $this->app['config']['app.debug'] ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ] : ['message' => $e->getMessage()];
    }

    /**
     * Determine whether an error exception is HTTP exception instance.
     *
     * @param \Exception $e An error exception instance.
     *
     * @return bool
     */
    protected function isHttpException(Exception $e) : bool
    {
        return $e instanceof HttpException;
    }
}
