<?php

namespace Melanth\Contracts\Support;

interface Jsonable
{
    /**
     * Convert the object to JSON representation.
     *
     * @return string
     */
    public function toJson($options = 0) : string;
}
