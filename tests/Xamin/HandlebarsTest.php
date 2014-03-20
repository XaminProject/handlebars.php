<?php
/**
 * This file is part of Handlebars-php
 * Base on mustache-php https://github.com/bobthecow/mustache.php
 *
 * PHP version 5.3
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Dmitriy Simushev <simushevds@gmail.com>
 * @copyright 2013 (c) f0ruD A
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

/**
 * Class AutoloaderTest
 */
class HandlebarsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Handlebars\Autoloader::register();
    }

    /**
     * Test handlebars autoloader
     *
     * @return void
     */
    public function testAutoLoad()
    {
        Handlebars\Autoloader::register(realpath(__DIR__ . '/../fixture/'));

        $this->assertTrue(class_exists('Handlebars\\Test'));
        $this->assertTrue(class_exists('\\Handlebars\\Test'));
        $this->assertTrue(class_exists('Handlebars\\Example\\Test'));
        $this->assertTrue(class_exists('\\Handlebars\\Example\\Test'));
        $this->assertFalse(class_exists('\\Another\\Example\\Test'));
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
            array(
                '{{data.length}}',
                array("data" => array(1, 2, 3, 4)),
                '4'
            ),
            array(
                '{{data.length}}',
                array("data" => (object)array(1, 2, 3, 4)),
                ''
            ),
            array(
                '{{data.length}}',
                array("data" => array("length" => "15 inches", "test", "test", "test")),
                "15 inches"
            ),
            array(
                '{{data.0}}',
                array("data" => array(1, 2, 3, 4)),
                '1'
            ),
            array(
                '{{data.property.3}}',
                array("data" => array("property" => array(1, 2, 3, 4))),
                '4'
            ),
            array(
                '{{data.unsafe}}',
                array('data' => array('unsafe' => '<strong>Test</strong>')),
                '&lt;strong&gt;Test&lt;/strong&gt;'
            ),
            array(
                '{{{data.safe}}}',
                array('data' => array('safe' => '<strong>Test</strong>')),
                '<strong>Test</strong>'
            ),
            array(
                "\{{data}}", // is equal to \\{{data}}
                array('data' => 'foo'),
                '{{data}}',
            ),
            array(
                '\\\\{{data}}',
                array('data' => 'foo'),
                '\\\\foo'
            ),
            array(
                '\\\{{data}}', // is equal to \\\\{{data}} in php
                array('data' => 'foo'),
                '\\\\foo'
            ),
            array(
                '\{{{data}}}',
                array('data' => 'foo'),
                '{{{data}}}'
            ),
            array(
                '\pi',
                array(),
                '\pi'
            ),
            array(
                '\\\\\\\\qux',
                array(),
                '\\\\\\\\qux'
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
            array(
                '{{#each data}}{{@key}}=>{{this}}{{/each}}',
                array('data' => array('key1' => 1, 'key2' => 2,)),
                'key1=>1key2=>2'
            ),
            array(
                '{{#each data}}{{@key}}=>{{this}}{{/each}}',
                array('data' => new \ArrayIterator(array('key1' => 1, 'key2' => 2))),
                'key1=>1key2=>2'
            ),
            array(
                '{{#each data}}{{@index}}=>{{this}},{{/each}}',
                array('data' => array('key1' => 1, 'key2' => 2,)),
                '0=>1,1=>2,'
            ),
            array(
                '{{#each data}}{{#if @first}}the first is {{this}}{{/if}}{{/each}}',
                array('data' => array('one', 'two', 'three')),
                'the first is one'
            ),
            array(
                '{{#each data}}{{#if @last}}the last is {{this}}{{/if}}{{/each}}',
                array('data' => array('one', 'two', 'three')),
                'the last is three'
            ),
            array(
                '{{#each data}}{{this}}{{else}}fail{{/each}}',
                array('data' => array(1, 2, 3, 4)),
                '1234'
            ),
            array(
                '{{#each data}}fail{{else}}ok{{/each}}',
                array('data' => false),
                'ok'
            ),
            array(
                '{{#unless data}}ok{{/unless}}',
                array('data' => true),
                ''
            ),
            array(
                '{{#unless data}}ok{{/unless}}',
                array('data' => false),
                'ok'
            ),
            array(
                '{{#unless data}}ok{{else}}fail{{/unless}}',
                array('data' => false),
                'ok'
            ),
            array(
                '{{#unless data}}fail{{else}}ok{{/unless}}',
                array('data' => true),
                'ok'
            ),
            array(
                '{{#bindAttr data}}',
                array(),
                'data'
            ),
            array(
                '{{#if 1}}ok{{else}}fail{{/if}}',
                array(),
                'ok'
            ),
            array(
                '{{#if 0}}ok{{else}}fail{{/if}}',
                array(),
                'fail'
            )

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
        $this->assertEquals('Test helper is called', $engine->render('{{test}}', array()));

        $engine->addHelper('test2', function ($template, $context, $arg) {
            return 'Test helper is called with ' . $arg;
        });
        $this->assertEquals('Test helper is called with a b c', $engine->render('{{#test2 a b c}}', array()));
        $this->assertEquals('Test helper is called with a b c', $engine->render('{{test2 a b c}}', array()));

        $engine->addHelper('renderme', function () {
            return new \Handlebars\String("{{test}}");
        });
        $this->assertEquals('Test helper is called', $engine->render('{{#renderme}}', array()));

        $engine->addHelper('dontrenderme', function () {
            return "{{test}}";
        });
        $this->assertEquals('{{test}}', $engine->render('{{#dontrenderme}}', array()));

        $engine->addHelper('markupHelper', function () {
            return '<strong>Test</strong>';
        });
        $this->assertEquals('<strong>Test</strong>', $engine->render('{{{markupHelper}}}', array()));
        $this->assertEquals('&lt;strong&gt;Test&lt;/strong&gt;', $engine->render('{{markupHelper}}', array()));

        $engine->addHelper('safeStringTest', function () {
            return new \Handlebars\SafeString('<strong>Test</strong>');
        });
        $this->assertEquals('<strong>Test</strong>', $engine->render('{{safeStringTest}}', array()));
    }

    public function testInvalidHelperMustacheStyle()
    {
        $this->setExpectedException('RuntimeException');
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->render('{{#NOTVALID}}XXX{{/NOTVALID}}', array());
    }

    public function testInvalidHelper()
    {
        $this->setExpectedException('RuntimeException');
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->render('{{#NOTVALID argument}}XXX{{/NOTVALID}}', array());
    }

    /**
     * Test mustache style loop and if
     */
    public function testMustacheStyle()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $this->assertEquals('yes', $engine->render('{{#x}}yes{{/x}}', array('x' => true)));
        $this->assertEquals('', $engine->render('{{#x}}yes{{/x}}', array('x' => false)));
        $this->assertEquals('yes', $engine->render('{{^x}}yes{{/x}}', array('x' => false)));
        $this->assertEquals('1234', $engine->render('{{#x}}{{this}}{{/x}}', array('x' => array(1, 2, 3, 4))));
        $this->assertEquals('012', $engine->render('{{#x}}{{@index}}{{/x}}', array('x' => array('a', 'b', 'c'))));
        $this->assertEquals('abc', $engine->render('{{#x}}{{@key}}{{/x}}', array('x' => array('a' => 1, 'b' => 2, 'c' => 3))));
        $std = new stdClass();
        $std->value = 1;
        $this->assertEquals('1', $engine->render('{{#x}}{{value}}{{/x}}', array('x' => $std)));
        $this->assertEquals('1', $engine->render('{{{x}}}', array('x' => 1)));
    }

    /**
     * @expectedException \LogicException
     */
    public function testParserException()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->render('{{#test}}{{#test2}}{{/test}}{{/test2}}', array());
    }

    /**
     * Test add/get/has/clear functions on helper class
     */
    public function testHelpersClass()
    {
        $helpers = new \Handlebars\Helpers();
        $helpers->add('test', function () {
        });
        $this->assertTrue($helpers->has('test'));
        $this->assertTrue(isset($helpers->test));
        $this->assertFalse($helpers->isEmpty());
        $helpers->test2 = function () {
        };
        $this->assertTrue($helpers->has('test2'));
        $this->assertTrue(isset($helpers->test2));
        $this->assertFalse($helpers->isEmpty());
        unset($helpers->test2);
        $this->assertFalse($helpers->has('test2'));
        $this->assertFalse(isset($helpers->test2));
        $helpers->clear();
        $this->assertFalse($helpers->has('test'));
        $this->assertFalse(isset($helpers->test));
        $this->assertTrue($helpers->isEmpty());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHelperWrongConstructor()
    {
        $helper = new \Handlebars\Helpers("helper");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHelperWrongCallable()
    {
        $helper = new \Handlebars\Helpers();
        $helper->add('test', 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHelperWrongGet()
    {
        $helper = new \Handlebars\Helpers();
        $x = $helper->test;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHelperWrongUnset()
    {
        $helper = new \Handlebars\Helpers();
        unset($helper->test);
    }

    /**
     * test String class
     */
    public function testStringClass()
    {
        $string = new \Handlebars\String('test');
        $this->assertEquals('test', $string->getString());
        $string->setString('new');
        $this->assertEquals('new', $string->getString());
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

    public function testArrayLoader()
    {
        $loader = new \Handlebars\Loader\ArrayLoader(array('test' => 'HELLO'));
        $loader->addTemplate('another', 'GOODBYE');
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $this->assertEquals($engine->render('test', array()), 'HELLO');
        $this->assertEquals($engine->render('another', array()), 'GOODBYE');

        $this->setExpectedException('RuntimeException');
        $engine->render('invalid-template', array());
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
     * Test file system loader
     */
    public function testFileSystemLoaderMultipleFolder()
    {
        $paths = array(
            realpath(__DIR__ . '/../fixture/data'),
            realpath(__DIR__ . '/../fixture/another')
        );

        $options = array(
            'prefix' => '__',
            'extension' => 'hb'
        );
        $loader = new \Handlebars\Loader\FilesystemLoader($paths, $options);
        $engine = new \Handlebars\Handlebars();
        $engine->setLoader($loader);
        $this->assertEquals('test_extra', $engine->render('loader', array()));
        $this->assertEquals('another_extra', $engine->render('another', array()));
    }

    /**
     * Test file system loader
     *
     * @expectedException \InvalidArgumentException
     */
    public function testFileSystemLoaderNotFound()
    {
        $loader = new \Handlebars\Loader\FilesystemLoader(realpath(__DIR__ . '/../fixture/data'));
        $engine = new \Handlebars\Handlebars();
        $engine->setLoader($loader);
        $engine->render('invalid_file', array());
    }

    /**
     * Test file system loader
     *
     * @expectedException \RuntimeException
     */
    public function testFileSystemLoaderInvalidFolder()
    {
        new \Handlebars\Loader\FilesystemLoader(realpath(__DIR__ . '/../fixture/') . 'invalid/path');
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

    public function testPartial()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $partialLoader = new \Handlebars\Loader\ArrayLoader(array('test' => '{{key}}', 'bar' => 'its foo'));
        $partialAliasses = array('foo' => 'bar');
        $engine = new \Handlebars\Handlebars(
            array(
                'loader' => $loader,
                'partials_loader' => $partialLoader,
                'partials_alias' => $partialAliasses
            )
        );

        $this->assertEquals('HELLO', $engine->render('{{>test parameter}}', array('parameter' => array('key' => 'HELLO'))));
        $this->assertEquals('its foo', $engine->render('{{>foo}}', array()));
        $engine->registerPartial('foo-again', 'bar');
        $this->assertEquals('its foo', $engine->render('{{>foo-again}}', array()));
        $engine->unRegisterPartial('foo-again');

        $this->setExpectedException('RuntimeException');
        $engine->render('{{>foo-again}}', array());
    }

    /**
     * test variable access
     */
    public function testVariableAccess()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = \Handlebars\Handlebars::factory();
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


    public function testContext()
    {
        $test = new stdClass();
        $test->value = 'value';
        $test->array = array(
            'a' => '1',
            'b' => '2',
            '!"#%&\'()*+,./;<=>@[\\^`{|}~ ' => '3',
        );
        $context = new \Handlebars\Context($test);
        $this->assertEquals('value', $context->get('value'));
        $this->assertEquals('value', $context->get('value', true));
        $this->assertEquals('value', $context->get('[value]', true));
        $this->assertEquals('1', $context->get('array.a', true));
        $this->assertEquals('2', $context->get('array.b', true));
        $this->assertEquals('3', $context->get('array.[!"#%&\'()*+,./;<=>@[\\^`{|}~ ]', true));
        $new = array('value' => 'new value');
        $context->push($new);
        $this->assertEquals('new value', $context->get('value'));
        $this->assertEquals('new value', $context->get('value', true));
        $this->assertEquals('value', $context->get('../value'));
        $this->assertEquals('value', $context->get('../value', true));
        $this->assertEquals($new, $context->last());
        $this->assertEquals($new, $context->get('.'));
        $this->assertEquals($new, $context->get('this'));
        $this->assertEquals($new, $context->get('this.'));
        $this->assertEquals($test, $context->get('../.'));
        $context->pop();
        $this->assertEquals('value', $context->get('value'));
        $this->assertEquals('value', $context->get('value', true));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidData
     */
    public function testInvalidAccessContext($invalid)
    {
        $context = new \Handlebars\Context(array());
        $this->assertEmpty($context->get($invalid));
        $context->get($invalid, true);
    }

    public function getInvalidData()
    {
        return array(
            array('../../data'),
            array('data'),
            array(''),
            array('data.key.key'),
        );
    }

    /**
     * Test for proper handling of the length property
     **/
    public function testArrayLengthEmulation()
    {

        $data = array("numbers" => array(1, 2, 3, 4),
            "object" => (object)array("prop1" => "val1", "prop2" => "val2"),
            "object_with_length_property" => (object)array("length" => "15cm")
        );
        $context = new \Handlebars\Context($data);
        // make sure we are getting the array length when given an array
        $this->assertEquals($context->get("numbers.length"), 4);
        // make sure we are not getting a length when given an object
        $this->assertEmpty($context->get("object.length"));
        // make sure we can still get the length property when given an object
        $this->assertEquals($context->get("object_with_length_property.length"), "15cm");
    }

    public function argumentParserProvider()
    {
        return array(
            array('arg1 arg2', array("arg1", "arg2")),
            array('"arg1 arg2"', array("arg1 arg2")),
            array('arg1 arg2 "arg number 3"', array("arg1", "arg2", "arg number 3")),
            array('arg1 "arg\"2" "\"arg3\""', array("arg1", 'arg"2', '"arg3"')),
            array("'arg1 arg2'", array("arg1 arg2")),
            array("arg1 arg2 'arg number 3'", array("arg1", "arg2", "arg number 3")),
            array('arg1 "arg\"2" "\\\'arg3\\\'"', array("arg1", 'arg"2', "'arg3'")),
            array('arg1 arg2.[value\'s "segment"].val', array("arg1", 'arg2.[value\'s "segment"].val')),
            array('"arg1.[value 1]" arg2', array("arg1.[value 1]", 'arg2')),
        );
    }

    /**
     * Test Argument Parser
     *
     * @param string $arg_string argument text
     * @param        $expected_array
     *
     * @dataProvider argumentParserProvider
     *
     * @return void
     */
    public function testArgumentParser($arg_string, $expected_array)
    {
        $engine = new \Handlebars\Handlebars();
        $template = new \Handlebars\Template($engine, null, null);
        // get the string version of the arguments array
        $args = $template->parseArguments($arg_string);
        $args = array_map(function ($a) {
            return (string)$a;
        }, $args);
        $this->assertEquals($args, $expected_array);

    }

    public function stringLiteralInCustomHelperProvider()
    {
        return array(
            array('{{#test2 arg1 "Argument 2"}}', array("arg1" => "Argument 1"), "Argument 1:Argument 2"),
            array('{{#test2 "Argument 1" "Argument 2"}}', array("arg1" => "Argument 1"), "Argument 1:Argument 2"),
            array('{{#test2 "Argument 1" arg2}}', array("arg2" => "Argument 2"), "Argument 1:Argument 2")
        );
    }

    /**
     * Test String literals in the context of a helper
     *
     * @param string $template template text
     * @param array  $data     context data
     * @param string $results  The Expected Results
     *
     * @dataProvider stringLiteralInCustomHelperProvider
     *
     * @return void
     */
    public function testStringLiteralInCustomHelper($template, $data, $results)
    {
        $engine = new \Handlebars\Handlebars();
        $engine->addHelper('test2', function ($template, $context, $args) {
            $args = $template->parseArguments($args);

            $args = array_map(function ($a) use ($context) {
                return $context->get($a);
            }, $args);

            return implode(':', $args);
        });
        $res = $engine->render($template, $data);
        $this->assertEquals($res, $results);
    }

    public function testString()
    {
        $string = new \Handlebars\String("Hello World");
        $this->assertEquals((string)$string, "Hello World");
    }

    public function testInvalidNames()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(
            array(
                'loader' => $loader,
            )
        );
        $all = \Handlebars\Context::NOT_VALID_NAME_CHARS;

        for ($i = 0; $i < strlen($all); $i++) {
            // Dot in string is valid, its an exception here
            if ($all{$i} === '.') {
                continue;
            }
            try {
                $name = 'var' . $all{$i} . 'var';
                $engine->render('{{' . $name . '}}', array($name => 'VALUE'));
                throw new Exception("Accept the $name :/");
            } catch (Exception $e) {
                $this->assertInstanceOf("InvalidArgumentException", $e);
            }
        }

    }

    /**
     * Helper subexpressions test
     */
    public function testHelperSubexpressions()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        $engine->addHelper('test', function ($template, $context, $arg) {
            return $arg.'Test.';
        });

        // assert that nested syntax is accepted and sub-helper is run
        $this->assertEquals('Test.Test.', $engine->render('{{test (test)}}', array()));

        $engine->addHelper('add', function ($template, $context, $arg) {
            $values = explode( " ", $arg );
            return $values[0] + $values[1];
        });

        // assert that subexpression result is inserted correctly as argument to top level helper
        $this->assertEquals('42', $engine->render('{{add 21 (add 10 (add 5 6))}}', array()));


        // assert that bracketed expressions within string literals are treated correctly
        $this->assertEquals("'(test)'Test.", $engine->render("{{test '(test)'}}", array()));
        $this->assertEquals("')'Test.Test.", $engine->render("{{test (test ')')}}", array()));
    }

}