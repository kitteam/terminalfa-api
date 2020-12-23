<?php

namespace TerminalFaApi\Services;

use Socket\Raw\Factory;
use DateTime;
use TerminalFaApi\Exceptions\TerminalFaExceptions;

class TerminalFaApi
{
    use Status;

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
     * @throws TerminalFaExceptions
     *
     */
    public function send($cmd, $data = "", $structure = [])
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

        $response = null;
        while ($data = $socket->read(2048)) {
            $response .= $data;
        }

        $socket->close();

        if ('b629' !== ($start = bin2hex(mb_strcut($response, 0, 2)))) {
            throw new TerminalFaExceptions('No correct response');
        }
        if ($result = hexdec(bin2hex(mb_strcut($response, 4, 1)))) {
            throw new TerminalFaExceptions('An error occurred', $result);
        }

        $crc = bin2hex(mb_strcut($response, -2));
        $length = hexdec(bin2hex(mb_strcut($response, 2, 2)));
        $response = mb_strcut($response, 5, $length - 1);

        if ($structure) {
            $data = [];

            foreach ($structure as $key => $type)
            {
                if (preg_match('/(?P<type>[a-z]*)\((?P<length>\d*)\)/mi', $type, $matches)) {
                    $data[$key] = $this->{$matches['type']}(substr($response, 0, $matches['length']));
                    $response = substr($response, $matches['length']);
                } elseif (preg_match('/(?P<type>[a-z]*)/mi', $type, $matches)) {
                    $data[$key] = $this->{$matches['type']}($response);
                }
            }

            return $data;
        }

        return $response;
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

    protected function ascii($string)
    {
        return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $string);
    }

    protected function datetime($binary)
    {
        $hex = current(unpack("H*", $binary));
        foreach (str_split($hex,2) as $hex) {
            $data[] = substr('0'. hexdec($hex), -2);
        }

        $format = substr('ymdHis', 0, strlen($binary));
        $datetime = DateTime::createFromFormat($format, join('', $data));

        return $datetime;
    }

    protected function byte($binary)
    {
        $hex = current(unpack("H*", $binary));
        return hexdec($hex);
    }

    protected function uint($binary)
    {
        $hex = current(unpack("H*", $binary));
        return hexdec($hex);
    }

    protected function uintle($binary)
    {
        $hex = current(unpack("H*", $binary));
        $hex = join('', array_reverse(str_split($hex, 2)));
        return hexdec($hex);
    }
}
