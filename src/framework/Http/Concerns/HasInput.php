<?php

namespace Melanth\Http\Concerns;

trait HasInput
{
    /**
     * Get all input data with the request.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->input();
    }

    /**
     * Get an input item with the request.
     *
     * @param string|null $key     The given key.
     * @param string|null $default The default value.
     *
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        return data_get(
            $this->query->all() + $this->getInputSource()->all(), $key, $default
        );
    }
}
