<?php

namespace Objectiveweb\Util;

class Config
{

    private $root;

    private static $config = [];

    function __construct($root)
    {
        $this->root = $root . (substr($root, -1) !== '/' ? '/' : '');
    }

    public static function flush()
    {
        static::$config = [];
    }

    function getValue($name, $key, $default = null)
    {
        $config = $this->get($name);

        return isset($config[$key]) ? $config[$key] : $default;
    }

    function &get($name)
    {
        $key = $this->root . $name;

        if (!isset(static::$config[$key])) {
            static::$config[$key] = [];

            $files = ["$name.php", "$name.local.php"];

            foreach ($files as $file) {
                if (file_exists($this->root . $file)) {

                    $config = include $this->root . $file;
                    static::$config[$key] = array_merge(static::$config[$key], $config);
                }
            }
        }

        return static::$config[$key];
    }

    /**
     * Sets a configuration property
     *
     * @param $name string The configuration name
     * @param $key array Contents to merge with current config
     */
    function set($name, array $values)
    {

        $config = &$this->get($name);

        foreach ($values as $key => $value) {
            $config[$key] = $value;
        }
    }

    function save($name)
    {
        $config = $this->get($name);

        return file_put_contents($this->root . $name . '.local.php',
            "<?php\n\nreturn " . var_export($config, true) . ";\n");
    }
}
