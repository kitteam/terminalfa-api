<?php

namespace TerminalFaApi\Services;

use Socket\Raw\Factory;
use DateTime;
use TerminalFaApi\Exceptions\TerminalFaExceptions;

class TerminalFaApi
{
    use Status, Shift;

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
        //$length = dechex(strlen(hex2bin($cmd . implode(unpack("H*", $data)))));
        $length = $this->dechex(strlen(hex2bin($cmd .  $data)), 4);

        $data = [
            $length,
            $cmd,
            //implode(unpack("H*", $data))
            $data
        ];

        $crc = $this->crc16ccitt(hex2bin(implode($data)));
        $crc = $this->reverse($this->dechex($crc, 4), 2);

        array_unshift($data, "B629");
        array_push($data, $crc);

        $data = hex2bin(implode($data));

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

    /*
     * Преобразование бинарных данных в шестнадцатеричную систему
     */
    protected function binhex($binary)
    {
        return current(unpack("H*", $binary));
    }

    /*
     * Преобразование бинарных данных в шестнадцатеричную систему в обратном порядке
     */
    protected function binhexrev($binary)
    {
        return $this->reverse($this->binhex($binary));
    }

    /*
     * Преобразование бинарных данных в десятиричную систему
     */
    protected function bindec($binary)
    {
        return hexdec($this->binhex($binary));
    }

    /*
     * Преобразование бинарных данных в десятиричную систему в обратном порядке
     */
    protected function bindecrev($binary)
    {
        return hexdec($this->binhexrev($binary));
    }

    /*
     * Преобразование бинарных данных в дату
     */
    protected function bindate($binary)
    {
        foreach (str_split($this->binhex($binary),2) as $hex) {
            $data[] = substr('0'. hexdec($hex), -2);
        }

        $format = substr('ymdHis', 0, strlen($binary));
        $date = DateTime::createFromFormat($format, implode($data));

        return $date;
    }

    /*
     * Преобразование бинарных данных в текст
     */
    protected function bintext($binary)
    {
        return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $binary);
    }

    /*
     * Преобразование бинарных данных в IP адрес
     */
    protected function binip($binary)
    {
        $parts = str_split($this->binhex($binary), 2);

        foreach ($parts as $key => $hex) {
            $parts[$key] = hexdec($hex);
        }

        return join('.', $parts);
    }

    /*
     * Преобразование строки в кодировку CP866
     */
    protected function cp866($string)
    {
        return mb_convert_encoding($string, "CP866");
    }

    /*
     * Перевод числа из десятичной системы счисления в шестнадцатеричную с ведущим нулем
     */
    protected function dechex($dec, $length = false)
    {
        $hex = dechex($dec);

        $length = $length ?: strlen($hex);
        $length = $length & 1 ? $length + 1 : $length;

        $hex = str_pad($hex, $length, "0", STR_PAD_LEFT);

        return $hex;
    }

    /*
     * Элементы строки в обратном порядке
     */
    protected function reverse($string, $length = 2)
    {
        return implode(array_reverse(str_split($string, $length)));
    }

    /*
     * Метод записи данных в электронной форме в виде структуры,
     * состоящей из трех полей: тип-длина-значение (tag-length-value), когда
     * значение представлено данными установленного формата
     */
    public function tlv($tag, $value)
    {
        $tag = $this->reverse($this->dechex($tag));
        $value = $this->cp866($value);

        return implode([
            $tag,
            $this->dechex(strlen($value)),
            $this->dechex(0),
            $this->binhex($value)
        ]);
    }

    /*// Deprecated
    protected function intle($binary)
    {
        $hex = join('', array_reverse(str_split($this->binhex($binary), 2)));
        return hexdec($hex);
    }



    // Deprecated
    protected function byte($binary)
    {
        $hex = current(unpack("H*", $binary));
        return hexdec($hex);
    }

    // Deprecated
    protected function uint($binary)
    {
        $hex = current(unpack("H*", $binary));
        return hexdec($hex);
    }

    // Deprecated
    protected function uintle($binary)
    {
        $hex = current(unpack("H*", $binary));
        $hex = join('', array_reverse(str_split($hex, 2)));
        return hexdec($hex);
    }*/
}
