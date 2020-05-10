<?php

namespace Melanth\Contracts\Http;

interface Kernel
{
    /**
     * Bootstrap all of the bootstrapped services.
     *
     * @return void
     */
    public function bootstrap() : void;

    /**
     * Handle an incoming request.
     *
     * @param \Melanth\Http\Request $request The request instance.
     *
     * @return \Melanth\Http\Response
     */
    public function handle(Request $request) : Response;
}
