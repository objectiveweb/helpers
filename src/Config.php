<?php

namespace Objectiveweb\Util;

class Config
{

    private $root;

    private $envPrefix;

    private static $config = [];

    function __construct($root, $envPrefix = "CONFIG")
    {
        $this->root = $root . (substr($root, -1) !== '/' ? '/' : '');
        $this->envPrefix = strtolower($envPrefix);
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

            // merge environment vars
            if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
                $envConfig = [$this->envPrefix => [$name => []]];
                foreach(getenv() as $envkey => $envval) {
                    $localConfig = &$envConfig;
                    $__keys = explode("__", strtolower($envkey));
                    if($__keys[0] == $this->envPrefix && $__keys[1] == $name) {
                        foreach($__keys as $__key) {
                            $localConfig = &$localConfig[$__key];
                        }
                        $localConfig = !empty($envval) && ($envval[0] == '[' || $envval[0] == '{') ? json_decode($envval, true) : $envval;
                    }
                }

                $this->set($name, $envConfig[$this->envPrefix][$name]);
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
