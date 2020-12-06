<?php


require dirname(__DIR__) . '/vendor/autoload.php';

use Objectiveweb\Util\Config;

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

    /**
     * @depends testLoadLocal
     */
    public function testFromEnv() {

        if ( version_compare(PHP_VERSION, '7.1.0') < 0 ) {
            $this->markTestSkipped('Env vars are only supported on PHP > 7.1.0');
        }

        // flush config cache
        Config::flush();

        putenv("CONFIG__DEFAULT__OVERRIDE=overrideFromEnv");
        putenv("CONFIG__DEFAULT__ENVSTRING=envString");
        putenv("CONFIG__DEFAULT__ENVOBJECT__STRING=envobjectString");
        putenv("CONFIG__DEFAULT__ENVOBJECT__LIST=[1,2,3]");
        putenv("CONFIG__DEFAULT__ENVOBJECT__LIST__B=b");
        putenv("CONFIG__DEFAULT__ENVOBJECT__LIST__C__D=c/d");
        putenv("CONFIG__DEFAULT__ENVOBJECT__LIST__3=4");
        putenv("CONFIG__DEFAULT__JSON={\"clientId\" : \"123\", \"clientSecret\": \"456\"}");

        $config = new Config(static::$root);

        $this->assertEquals("overrideFromEnv", $config->getValue("default", 'override'));
        $this->assertEquals("envString", $config->getValue("default", 'envstring'));
        $envobject = $config->getValue('default', 'envobject');
        $this->assertArrayHasKey('string', $envobject);
        $this->assertArrayHasKey('list', $envobject);
        $this->assertCount(6, $envobject['list']);

        $this->assertArrayHasKey('b', $envobject['list']);
        $this->assertArrayHasKey('c', $envobject['list']);
        $this->assertArrayHasKey('d', $envobject['list']['c']);

        $this->assertEquals('c/d', $envobject['list']['c']['d']);
        $this->assertEquals(4, $envobject['list'][3]);

        $json = $config->getValue('default', 'json');
        $this->assertEquals('123', $json['clientId']);
        $this->assertEquals('456', $json['clientSecret']);
    }
}
