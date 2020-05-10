<?php

namespace Melanth\Http;

class UrlParser
{
    /**
     * The URL components for incoming request.
     *
     * @var string
     */
    protected $components;

    /**
     * Create a new URL parser instance.
     *
     * @param string $url The HTTP uniform resource identifier.
     *
     * @return void
     */
    public function __construct(string $url)
    {
        $this->components = parse_url($url);
    }

    /**
     * Get the query parameters from URL.
     *
     * @return array
     */
    public function parseQuery() : array
    {
        if (! isset($this->components['query'])) {
            return [];
        }

        parse_str(html_entity_decode($this->components['query']), $parameters);

        return $parameters;
    }

    /**
     * Get server configuration from URL components.
     *
     * @return array
     */
    public function parseConfig() : array
    {
        $config = $this->getDefaultConfig();
        $config['REQUEST_URI'] = $this->components['path'] ?? '/';

        if (isset($this->components['scheme'])) {
            if ($this->components['scheme'] === 'https') {
                [$config['HTTPS'], $config['SERVER_PORT']] = ['on', Request::HTTPS_PORT];
            } else {
                $config['SERVER_PORT'] = Request::HTTP_PORT;
            }
        }

        if (isset($this->components['host'])) {
            $config['SERVER_NAME'] = $this->components['host'];
            $config['HTTP_HOST'] = $this->components['host'];
        }

        if (isset($this->components['port'])) {
            $config['SERVER_PORT'] = $this->components['port'];
            $config['HTTP_HOST'] .= ':'.$this->components['port'];
        }

        if (isset($this->components['user'])) {
            $config['PHP_AUTH_USER'] = $this->components['user'];
        }

        if (isset($this->components['pass'])) {
            $config['PHP_AUTH_PW'] = $this->components['pass'];
        }

        if (isset($this->components['query'])) {
            $config['QUERY_STRING'] = $this->components['query'];
            $config['REQUEST_URI'] .= '?'.$config['QUERY_STRING'];
        }

        return $config;
    }

    /**
     * Get default server configuration.
     *
     * @return array
     */
    public function getDefaultConfig() : array
    {
        return [
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => Request::HTTP_PORT,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Melanth',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_TIME'         => time(),
            'PATH'                 => '',
        ];
    }
}
