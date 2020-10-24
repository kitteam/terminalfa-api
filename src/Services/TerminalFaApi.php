<?php

namespace TerminalFaApi\Services;

use Socket\Raw\Factory;
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

    public function __construct()
    {
        $this->location('default');
    }

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

        $this->host = (string)$locations[$location]['host'];
        $this->port = (string)$locations[$location]['port'];

        return $this;
    }

    /**
     * @param string $address
     * @param array $config
     *
     * @return bool
     */
    private function keysCheck($location, $config)
    {
        return !isset($config[$location]['host']) || !isset($config[$location]['port']);
    }


    /**
     * @param string $cmd
     * @param string $data
     *
     * @return string
     * @throws Exceptions
     *
     */
    public function send($cmd, $data = "")
    {
        $length = dechex(strlen(hex2bin($cmd . implode(unpack("H*", $data)))));

        $data = [
            str_pad($length, 4, "0", STR_PAD_LEFT),
            $cmd,
            implode(unpack("H*", $data))
        ];

        $crc = $this->crc16ccitt(hex2bin(join('', $data)));
        $crc = str_pad(dechex($crc), 4, "0", STR_PAD_LEFT);
        $bytes = str_split($crc, 2);

        array_unshift($data, "B629");
        array_push($data, $bytes[1] . $bytes[0]);

        $data = hex2bin(join("", $data));

        $factory = new Factory();
        $socket = $factory->createClient("{$this->host}:{$this->port}");
        $socket->write($data);

        usleep(100000); // Нужна пауза между отправкой и чтением данных

        $data = $socket->read(8192);

        $length = hexdec(bin2hex(substr($data, 2, 2)));
        $result = hexdec(bin2hex(substr($data, 4, 1)));
        $crc = bin2hex(substr($data, -2));
        $data = substr($data, 5, $length - 1);

        $socket->close();

        if ($result) {
            throw new \Exception($data);
        }

        return $data;
    }

    protected function crc16ccitt($data)
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return $crc;
    }
}
