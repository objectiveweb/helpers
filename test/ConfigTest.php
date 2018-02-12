<?php


require dirname(__DIR__) . '/vendor/autoload.php';

use Objectiveweb\Helpers\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{

    /** @var Config */
    public static $config = null;

    public static $root = __DIR__ . '/config';
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if(file_exists(static::$root.'/default.local.php'))
            unlink(static::$root.'/default.local.php');

        static::$config = new Config(static::$root);


    }

    public function testConfig() {

        $this->assertFileNotExists(static::$root.'/default.local.php');

        $default = static::$config->get('default');

        $this->assertTrue($default['default']);
        $this->assertFalse($default['override']);

        return static::$config;
    }

    /**
     * @depends testConfig
     */
    public function testSet($config) {

        // Test update using array
        $config->set('default', [
            'default.local' => true,
            'override' => true
        ]);

        $default = $config->get('default');

        $this->assertTrue($default['default.local']);
        $this->assertTrue($default['override']);

        return $config;
    }

    /**
     * @depends testSet
     * @param $config
     */
    public function testUpdateReference($config) {
        $default = &$config->get('default');

        $default['updatedreference'] = true;

        $default = $config->get('default');

        $this->assertTrue($default['updatedreference']);

        return $config;
    }

    /**
     * @depends testUpdateReference
     * @param $config
     */
    public function testSave($config) {
        $config->save('default');

        $this->assertFileExists(static::$root.'/default.local.php');
    }

    /**
     * @depends testSave
     */
    public function testLoadLocal() {
        // flush config cache
        Config::flush();

        $config = new Config(static::$root);

        $this->assertTrue($config->getValue('default', 'override'));
        $this->assertTrue($config->getValue('default', 'updatedreference'));
    }
}