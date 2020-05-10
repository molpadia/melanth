<?php

namespace Melanth\Contracts\Http;

use Melanth\Http\Request;

interface Response
{
    /**
     * Prepare to handle an incoming HTTP request.
     *
     * @param \Melanth\Http\Request $requesst The request instance.
     *
     * @return $this
     */
    public function prepare(Request $request);

    /**
     * Send HTTP headers with the response.
     *
     * @return \Melanth\Http\Response
     */
    public function sendHeaders();

    /**
     * Send HTTP message content with the response.
     *
     * @return \Melanth\Http\Response
     */
    public function sendContent();

    /**
     * Send HTTP response to client.
     *
     * @return \Melanth\Http\Response
     */
    public function send();
}
