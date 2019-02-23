<?php

namespace Objectiveweb\Util;

class Logger
{
    /**
     * Show traces when DEBUG
     */
    const TRACE = 32;
    /**
     * Show debug messages
     */
    const DEBUG = 16;
    /**
     * Show info messages
     */
    const INFO = 8;
    /**
     * Show warnings
     */
    const WARN = 4;
    /**
     * Show errors
     */
    const ERROR = 2;
    /**
     * Show fatal errors only
     */
    const FATAL = 1;
    const OFF = 0;

    private $name;
    private $level;
    private $date_format;
    private $format = "[%s] [%s] [%s] %s\n";

    private static $loggers = [];
    private static $levels = [
        1 => 'FATAL',
        2 => 'ERROR',
        4 => 'WARN',
        8 => 'INFO',
        16 => 'DEBUG',
        32 => 'TRACE'
    ];

    function __construct($name, $level = self::ERROR, $date_format = "Y-m-d H:i:s.u")
    {
        $this->name = $name;
        $this->level = $level;
        $this->date_format = $date_format;
    }

    function setLevel($level)
    {
        $this->level = $level;
    }

    static function getLogger($name, $level = self::ERROR, $date_format = "Y-m-d H:i:s.u")
    {
        if (!in_array($name, self::$loggers)) {
            self::$loggers[$name] = new Logger($name, $level, $date_format);
        }

        return self::$loggers[$name];
    }

    function fatal($tag, $payload = null)
    {
        $this->log(self::FATAL, $tag, $payload);
    }

    function error($tag, $payload = null)
    {
        $this->log(self::ERROR, $tag, $payload);
    }

    function warn($tag, $payload = null)
    {
        $this->log(self::WARN, $tag, $payload);
    }

    function info($tag, $payload = null)
    {
        $this->log(self::INFO, $tag, $payload);
    }

    function debug($tag, $payload = null, $trace = null)
    {
        $this->log(self::DEBUG, $tag, $payload);

        if($trace) {
            $this->log(self::TRACE, $tag, $trace);
        }
    }

    function log($level, $tag, $payload = null)
    {
        if ($this->level && $this->level >= $level) {
            $date = \DateTime::createFromFormat('U.u', microtime(TRUE));
            $stderr = fopen('php://stderr', 'w');
            fprintf($stderr, $this->format, $date->format($this->date_format), self::$levels[$level], $tag, json_encode($payload));
            fclose($stderr);
        }
    }
}
