<?php

namespace TerminalFaApi\Services;

use TerminalFaApi\Exceptions\TerminalFaExceptions;

class TerminalFaApi
{
    /**
     * @var
     */
    private $host = '';

    /**
     * @var string
     */
    private $port = '';

    public function location($location = '')
    {
        if (empty($location)) {
            throw new \Exception('Location is not specified');
        }
        $locations = config('terminalfa.locations');

        if (!isset($locations[$location])) {
            throw new \Exception('Specified location not found in config');
        }

        if ($this->keysCheck($location, $locations)) {
            throw new \Exception('Specified location config does not contain host or port');
        }

        $this->host = (string) $locations[$location]['host'];
        $this->port = (string) $locations[$location]['port'];

        return $this;
    }

    /**
     * @param string $address
     * @param array  $config
     *
     * @return bool
     */
    private function keysCheck($location, $config)
    {
        return !isset($config[$location]['host']) || !isset($config[$location]['port']);
    }


    /**
     * @param string $cmd
     *
     * @throws Exceptions
     *
     * @return string
     */
    public function send($cmd)
    {
        // ...
    }
}
