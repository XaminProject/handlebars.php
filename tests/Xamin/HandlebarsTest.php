<?php

/**
 * Class AutoloaderTest
 */
class HandlebarsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test handlebars autoloader
     *
     * @return void
     */
    public function testAutoLoad()
    {
        Handlebars\Autoloader::register(realpath(__DIR__ . '/../fixture/'));

        $this->assertTrue(class_exists('Handlebars\\Test'));
        $this->assertTrue(class_exists('Handlebars\\Example\\Test'));
    }

    /**
     * Test basic tags
     *
     * @param string $src    handlebars source
     * @param array  $data   data
     * @param string $result expected data
     *
     * @dataProvider simpleTagdataProvider
     *
     * @return void
     */
    public function testBasicTags($src, $data, $result)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $this->assertEquals($result, $engine->render($src, $data));
    }

    /**
     * Simple tag provider
     *
     * @return array
     */
    public function simpleTagdataProvider()
    {
        return array(
            array(
                '{{! This is comment}}',
                array(),
                ''
            ),
            array(
                '{{data}}',
                array('data' => 'result'),
                'result'
            ),
            array(
                '{{data.key}}',
                array('data' => array('key' => 'result')),
                'result'
            ),
        );
    }


    /**
     * Test helpers (internal helpers)
     *
     * @param string $src    handlebars source
     * @param array  $data   data
     * @param string $result expected data
     *
     * @dataProvider internalHelpersdataProvider
     *
     * @return void
     */
    public function testSimpleHelpers($src, $data, $result)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $helpers = new \Handlebars\Helpers();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader, 'helpers' => $helpers));

        $this->assertEquals($result, $engine->render($src, $data));
    }

    /**
     * Simple helpers provider
     *
     * @return array
     */
    public function internalHelpersdataProvider()
    {
        return array(
            array(
                '{{#if data}}Yes{{/if}}',
                array('data' => true),
                'Yes'
            ),
            array(
                '{{#if data}}Yes{{/if}}',
                array('data' => false),
                ''
            ),
            array(
                '{{#with data}}{{key}}{{/with}}',
                array('data' => array('key' => 'result')),
                'result'
            ),
            array(
                '{{#each data}}{{this}}{{/each}}',
                array('data' => array(1, 2, 3, 4)),
                '1234'
            ),
        );
    }

    /**
     * Management helpers
     */
    public function testHelpersManagement()
    {
        $helpers = new \Handlebars\Helpers(array('test' => function () {
        }), false);
        $engine = new \Handlebars\Handlebars(array('helpers' => $helpers));
        $this->assertTrue(is_callable($engine->getHelper('test')));
        $this->assertTrue($engine->hasHelper('test'));
        $engine->removeHelper('test');
        $this->assertFalse($engine->hasHelper('test'));
    }

    /**
     * Custom helper test
     */
    public function testCustomHelper()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->addHelper('test', function () {
            return 'Test helper is called';
        });
        $this->assertEquals('Test helper is called', $engine->render('{{#test}}', array()));
    }

    /**
     * @param $dir
     *
     * @return bool
     */
    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /**
     * Its not a good test :) but ok
     */
    public function testCacheSystem()
    {
        $path = sys_get_temp_dir() . '/__cache__handlebars';

        @$this->delTree($path);

        $dummy = new \Handlebars\Cache\Disk($path);
        $engine = new \Handlebars\Handlebars(array('cache' => $dummy));
        $this->assertEquals(0, count(glob($path . '/*')));
        $engine->render('test', array());
        $this->assertEquals(1, count(glob($path . '/*')));
    }

    /**
     * Test file system loader
     */
    public function testFileSystemLoader()
    {
        $loader = new \Handlebars\Loader\FilesystemLoader(realpath(__DIR__ . '/../fixture/data'));
        $engine = new \Handlebars\Handlebars();
        $engine->setLoader($loader);
        $this->assertEquals('test', $engine->render('loader', array()));
    }

    /**
     * Test partial loader
     */
    public function testPartialLoader()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $partialLoader = new \Handlebars\Loader\FilesystemLoader(realpath(__DIR__ . '/../fixture/data'));
        $engine = new \Handlebars\Handlebars();
        $engine->setLoader($loader);
        $engine->setPartialsLoader($partialLoader);

        $this->assertEquals('test', $engine->render('{{>loader}}', array()));
    }

    /**
     * test variable access
     */
    public function testVariableAccess()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars();
        $engine->setLoader($loader);

        $var = new \StdClass();
        $var->x = 'var-x';
        $var->y = array(
            'z' => 'var-y-z'
        );
        $this->assertEquals('test', $engine->render('{{var}}', array('var' => 'test')));
        $this->assertEquals('var-x', $engine->render('{{var.x}}', array('var' => $var)));
        $this->assertEquals('var-y-z', $engine->render('{{var.y.z}}', array('var' => $var)));
        // Access parent context in with helper
        $this->assertEquals('var-x', $engine->render('{{#with var.y}}{{../var.x}}{{/with}}', array('var' => $var)));

        $obj = new DateTime();
        $time = $obj->getTimestamp();
        $this->assertEquals($time, $engine->render('{{time.getTimestamp}}', array('time' => $obj)));

    }
}