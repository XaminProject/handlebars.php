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
 * Class HandlebarsTest
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
                "\\{{data}}", // is equal to \{{data}} in template file
                array('data' => 'foo'),
                '{{data}}',
            ),
            array(
                '\\\\{{data}}', // is equal to \\{{data}} in template file
                array('data' => 'foo'),
                '\\foo' // is equals to \foo in output
            ),
            array(
                '\\\\\\{{data}}', // is equal to \\\{{data}} in template file
                array('data' => 'foo'),
                '\\\\foo' // is equals to \\foo in output
            ),
            array(
                '\\\\\\\\{{data}}', // is equal to \\\\{{data}} in template file
                array('data' => 'foo'),
                '\\\\\\foo' // is equals to \\\foo in output
            ),
            array(
                '\{{{data}}}', // is equal to \{{{data}}} in template file
                array('data' => 'foo'),
                '{{{data}}}'
            ),
            array(
                '\pi', // is equal to \pi in template
                array(),
                '\pi'
            ),
            array(
                '\\\\foo', // is equal to \\foo in template
                array(),
                '\\\\foo'
            ),
            array(
                '\\\\\\bar', // is equal to \\\bar in template
                array(),
                '\\\\\\bar'
            ),
            array(
                '\\\\\\\\qux', // is equal to \\\\qux in template file
                array(),
                '\\\\\\\\qux'
            ),
            array(
                "var jsVar = 'It\'s a phrase in apos';",
                array(),
                "var jsVar = 'It\'s a phrase in apos';"
            ),
            array(
                'var jsVar = "A \"quoted\" text";',
                array(),
                'var jsVar = "A \"quoted\" text";',
            ),
            array(
                '{{#if first}}The first{{else}}{{#if second}}The second{{/if}}{{/if}}',
                array('first' => false, 'second' => true),
                'The second'
            ),
            array(
                '{{#value}}Hello {{value}}, from {{parent_context}}{{/value}}',
                array('value' => 'string', 'parent_context' => 'parent string'),
                'Hello string, from parent string'
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
            # see the issue #76
            array(
                '{{#if data}}0{{/if}}',
                array('data' => true),
                '0'
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
                '{{#each data}}{{@key}}{{/each}}',
                array('data' => array('the_only_key' => 1)),
                'the_only_key'
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
            ),
            array(
                '  {{~#if 1}}OK   {{~else~}} NO {{~/if~}}  END',
                array(),
                'OKEND'
            ),
            array(
                'XX {{~#bindAttr data}} XX',
                array(),
                'XXdata XX'
            ),
            array(
                '{{#each data}}{{#if @last}}the last is
                {{~this}}{{/if}}{{/each}}',
                array('data' => array('one', 'two', 'three')),
                'the last isthree'
            ),
            array(
                '{{#with data}}

                {{~key~}}

                {{/with}}',
                array('data' => array('key' => 'result')),
                'result'
            ),
            array(
                '{{= (( )) =}}((#if 1))OK((else))NO((/if))',
                array(),
                'OK'
            ),
            array(
                '{{#each data~}}    {{this}}    {{~/each}}',
                array('data' => array(1, 2, 3, 4)),
                '1234'
            ),
            array(
                '{{#each data}}{{this}}    {{~/each}}',
                array('data' => array(1, 2, 3, 4)),
                '1234'
            ),
            array(
                '{{#each data~}}    {{this}}{{/each}}',
                array('data' => array(1, 2, 3, 4)),
                '1234'
            ),
            array('{{#if first}}The first{{else}}{{#if second}}The second{{/if}}{{/if}}',
                array('first' => false, 'second' => true),
                'The second'
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
            return new \Handlebars\StringWrapper("{{test}}");
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

        $engine->addHelper('argsTest', function ($template, $context, $arg) {
            $parsedArgs = $template->parseArguments($arg);

            return implode(' ', $parsedArgs);
        });
        $this->assertEquals("a \"b\" c", $engine->render('{{{argsTest "a" "\"b\"" \'c\'}}}', array()));

        // This is just a fun thing to do :)
        $that = $this;
        $engine->addHelper('stopToken',
            function ($template, $context, $arg) use ($that) {
                /** @var $template \Handlebars\Template */
                $parsedArgs = $template->parseArguments($arg);
                $first = array_shift($parsedArgs);
                $last = array_shift($parsedArgs);
                if ($last == 'yes') {
                    $template->setStopToken($first);
                    $that->assertEquals($first, $template->getStopToken());
                    $buffer = $template->render($context);
                    $template->setStopToken(false);
                    $template->discard($context);
                } else {
                    $template->setStopToken($first);
                    $that->assertEquals($first, $template->getStopToken());
                    $template->discard($context);
                    $template->setStopToken(false);
                    $buffer = $template->render($context);
                }

                return $buffer;
            });

        $this->assertEquals("Used", $engine->render('{{# stopToken fun no}}Not used{{ fun }}Used{{/stopToken }}', array()));
        $this->assertEquals("Not used", $engine->render('{{# stopToken any yes}}Not used{{ any }}Used{{/stopToken }}', array()));

        $this->setExpectedException('InvalidArgumentException');
        $engine->getHelpers()->call('invalid', $engine->loadTemplate(''), new \Handlebars\Context(), '', '');
    }
    
    public function testRegisterHelper()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));
        //date_default_timezone_set('GMT');
        
        //FIRST UP: some awesome helpers!!
        
        //translations
        $translations = array(
            'hello' => 'bonjour',
            'my name is %s' => 'mon nom est %s',
            'how are your %s kids and %s' => 'comment sont les enfants de votre %s et %s'
        );
        
        //i18n
        $engine->registerHelper('_', function($key) use ($translations) {
            $args = func_get_args();
            $key = array_shift($args);
            $options = array_pop($args);
            
            //make sure it's a string
            $key = (string) $key;
            
            //by default the translation is the key
            $translation = $key;
            
            //if there is a translation
            if(isset($translations[$key])) {
                //translate it
                $translation = $translations[$key];
            }
            
            //if there are more arguments
            if(!empty($args)) {
                //it means the translations was 
                //something like 'Hello %s'
                return vsprintf($translation, $args);
            }
            
            //just return what we got
            return $translation;
        });
        
        //create a better if helper
        $engine->registerHelper('when', function($value1, $operator, $value2, $options) {
            $valid = false;
            //the amazing reverse switch!
            switch (true) {
                case $operator == 'eq' && $value1 == $value2:
                case $operator == '==' && $value1 == $value2:
                case $operator == 'req' && $value1 === $value2:
                case $operator == '===' && $value1 === $value2:
                case $operator == 'neq' && $value1 != $value2:
                case $operator == '!=' && $value1 != $value2:
                case $operator == 'rneq' && $value1 !== $value2:
                case $operator == '!==' && $value1 !== $value2:
                case $operator == 'lt' && $value1 < $value2:
                case $operator == '<' && $value1 < $value2:
                case $operator == 'lte' && $value1 <= $value2:
                case $operator == '<=' && $value1 <= $value2:
                case $operator == 'gt' && $value1 > $value2:
                case $operator == '>' && $value1 > $value2:
                case $operator == 'gte' && $value1 >= $value2:
                case $operator == '>=' && $value1 >= $value2:
                case $operator == 'and' && $value1 && $value2: 
                case $operator == '&&' && ($value1 && $value2):
                case $operator == 'or' && ($value1 || $value2):
                case $operator == '||' && ($value1 || $value2):
                    $valid = true;
                    break;
            }
            
            if($valid) {
                return $options['fn']();
            }
        
            return $options['inverse']();
        });
        
        //a loop helper
        $engine->registerHelper('loop', function($object, $options) {
            //expected for subtemplates of this block to use
            //  {{value.profile_name}} vs {{profile_name}}
            //  {{key}} vs {{@index}}
            
            $i = 0;
            $buffer = array();
            $total = count($object);
            
            //loop through the object
            foreach($object as $key => $value) {
                //call the sub template and 
                //add it to the buffer
                $buffer[] = $options['fn'](array(
                    'key'    => $key,
                    'value'    => $value,
                    'last'    => ++$i === $total
                ));
            }
            
            return implode('', $buffer);
        });
        
        //array in
        $engine->registerHelper('in', function(array $array, $key, $options) {
            if(in_array($key, $array)) {
                return $options['fn']();
            }

            return $options['inverse']();
        });
        
        //converts date formats to other formats
        $engine->registerHelper('date', function($time, $format, $options) {
            return date($format, strtotime($time));
        });
        
        //nesting helpers, these don't really help anyone :)
        $engine->registerHelper('nested1', function($test1, $test2, $options) {
            return $options['fn'](array(
                'test4' => $test1,
                'test5' => 'This is Test 5'
            ));
        });
        
        $engine->registerHelper('nested2', function($options) {
            return $options['fn'](array('test6' => 'This is Test 6'));
        });
        
        //NEXT UP: some practical case studies
        
        //case 1 - i18n
        $variable1 = array();
        $template1 = "{{_ 'hello'}}, {{_ 'my name is %s' 'Foo'}}! {{_ 'how are your %s kids and %s' 6 'dog'}}?";
        $expected1 = 'bonjour, mon nom est Foo! comment sont les enfants de votre 6 et dog?';
        
        //case 2 - when
        $variable2 = array('gender' => 'female', 'foo' => 'bar');
        $template2 = "Hello {{#when gender '===' 'male'}}sir{{else}}maam{{/when}} {{foo}}";
        $expected2 = 'Hello maam bar';
        
        //case 3 - when else
        $variable3 = array('gender' => 'male');
        $template3 = "Hello {{#when gender '===' 'male'}}sir{{else}}maam{{/when}}";
        $expected3 = 'Hello sir';
        
        //case 4 - loop
        $variable4 = array(
            'rows' => array(
                array(
                    'profile_name' => 'Jane Doe',
                    'profile_created' => '2014-04-04 00:00:00'
                ),
                array(
                    'profile_name' => 'John Doe',
                    'profile_created' => '2015-01-21 00:00:00'
                )
            )
        );
        $template4 = "{{#loop rows}}<li>{{value.profile_name}} - {{date value.profile_created 'M d'}}</li>{{/loop}}";
        $expected4 = '<li>Jane Doe - Apr 04</li><li>John Doe - Jan 21</li>';
        
        //case 5 - array in
        $variable5 = $variable4;
        $variable5['me'] = 'Jack Doe';
        $variable5['admins'] = array('Jane Doe', 'John Doe');
        $template5 = "{{#in admins me}}<ul>".$template4."</ul>{{else}}No Access{{/in}}{{suffix}}";
        $expected5 = 'No Access';
        
        //case 6 - array in else
        $variable6 = $variable5;
        $variable6['me'] = 'Jane Doe';
        $variable6['suffix'] = 'qux';
        $template6 = $template5;
        $expected6 = '<ul><li>Jane Doe - Apr 04</li><li>John Doe - Jan 21</li></ul>qux';
        
        //case 7 - nested templates and parent-grand variables
        $variable7 = array('test' => 'Hello World');
        $template7 = '{{#nested1 test "test2"}}  '
            .'In 1: {{test4}} {{#nested1 ../test \'test3\'}} '
            .'In 2: {{test5}}{{#nested2}}  '
            .'In 3: {{test6}}  {{../../../test}}{{/nested2}}{{/nested1}}{{/nested1}}';
        $expected7 = '  In 1: Hello World  In 2: This is Test 5  In 3: This is Test 6  Hello World';
        
        //case 8 - when inside an each
        $variable8 = array('data' => array(0, 1, 2, 3),'finish' => 'ok');
        $template8 = '{{#each data}}{{#when this ">" "0"}}{{this}}{{/when}}{{/each}} {{finish}}';
        $expected8 = '123 ok';
        
        //case 9 - when inside an each
        $variable9 = array('data' => array(),'finish' => 'ok');
        $template9 = '{{#each data}}{{#when this ">" "0"}}{{this}}{{/when}}{{else}}foo{{/each}} {{finish}}';
        $expected9 = 'foo ok';
        
        //LAST UP: the actual testing
        
        $this->assertEquals($expected1, $engine->render($template1, $variable1));
        $this->assertEquals($expected2, $engine->render($template2, $variable2));
        $this->assertEquals($expected3, $engine->render($template3, $variable3));
        $this->assertEquals($expected4, $engine->render($template4, $variable4));
        $this->assertEquals($expected5, $engine->render($template5, $variable5));
        $this->assertEquals($expected6, $engine->render($template6, $variable6));
        $this->assertEquals($expected7, $engine->render($template7, $variable7));
        $this->assertEquals($expected8, $engine->render($template8, $variable8));
        $this->assertEquals($expected9, $engine->render($template9, $variable9));
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
        $this->assertEquals('', $engine->render('{{#NOTVALID}}XXX{{/NOTVALID}}', array()));
        $this->assertEquals('yes', $engine->render('{{^x}}yes{{/x}}', array('x' => false)));
        $this->assertEquals('', $engine->render('{{^x}}yes{{/x}}', array('x' => true)));
        $this->assertEquals('1234', $engine->render('{{#x}}{{this}}{{/x}}', array('x' => array(1, 2, 3, 4))));
        $this->assertEquals('012', $engine->render('{{#x}}{{@index}}{{/x}}', array('x' => array('a', 'b', 'c'))));
        $this->assertEquals('abc', $engine->render('{{#x}}{{@key}}{{/x}}', array('x' => array('a' => 1, 'b' => 2, 'c' => 3))));
        $this->assertEquals('the_only_key', $engine->render('{{#x}}{{@key}}{{/x}}', array('x' => array('the_only_key' => 1))));
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
     * Test add/addHelpers/get/getAll/has/clear functions on helper class
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
        $helpers->add('test', function () {
        });
        $this->assertCount(0, array_diff(array_keys($helpers->getAll()), array('test')));
        $extraHelpers = new \Handlebars\Helpers();
        $extraHelpers->add('test', function () {
        });
        $extraHelpers->add('test2', function () {
        });
        $helpers->addHelpers($extraHelpers);
        $this->assertTrue($helpers->has('test2'));
        $this->assertEquals($helpers->test, $extraHelpers->test);
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
    public function testStringWrapperClass()
    {
        $string = new \Handlebars\StringWrapper('test');
        $this->assertEquals('test', $string->getString());
        $string->setString('new');
        $this->assertEquals('new', $string->getString());
    }

    /**
     * test SafeString class
     */
    public function testSafeStringClass()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $helpers = new \Handlebars\Helpers();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader, 'helpers' => $helpers));

        $this->assertEquals('<strong>Test</strong>', $engine->render('{{string}}', array(
            'string' => new \Handlebars\SafeString('<strong>Test</strong>')
        )));
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
     * Test inline loader
     */
    public function testInlineLoader()
    {
        $loader = new \Handlebars\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $this->assertEquals('This is a inline template.', $loader->load('template1'));

        $expected = <<<EOM
a
b
c
d
EOM;
        $this->assertEquals($expected, $loader->load('template2'));
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
        $partialLoader = new \Handlebars\Loader\ArrayLoader(array(
            'test' => '{{key}}',
            'bar' => 'its foo',
            'presetVariables' => '{{myVar}}',
        ));
        $partialAliasses = array('foo' => 'bar');
        $engine = new \Handlebars\Handlebars(
            array(
                'loader' => $loader,
                'partials_loader' => $partialLoader,
                'partials_alias' => $partialAliasses
            )
        );

        $this->assertEquals('foobar', $engine->render("{{>presetVariables myVar='foobar'}}", array()));
        $this->assertEquals('foobar=barbaz', $engine->render("{{>presetVariables myVar='foobar=barbaz'}}", array()));
        $this->assertEquals('qux', $engine->render("{{>presetVariables myVar=foo}}", array('foo' => 'qux')));
        $this->assertEquals('qux', $engine->render("{{>presetVariables myVar=foo.bar}}", array('foo' => array('bar' => 'qux'))));

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
        // Reference array as string
        $this->assertEquals('Array', $engine->render('{{var}}', array('var' => array('test'))));

        // Test class with __toString method
        $this->assertEquals('test', $engine->render('{{var}}', array('var' => new TestClassWithToStringMethod())));

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
     * Test invalid custom template class
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCustomTemplateClass()
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array(
            'loader' => $loader,
            'template_class' => 'stdclass'
        ));
    }

    /**
     * Test custom template class
     */
    public function testValidCustomTemplateClass()
    {
        Handlebars\Autoloader::register(realpath(__DIR__ . '/../fixture/'));

        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array(
            'loader' => $loader,
            'template_class' => 'Handlebars\CustomTemplate'
        ));

        $render = $engine->render('Original Template', array());

        $this->assertEquals($render, 'Altered Template');
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
            array('0', array('0')),
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

    public function namedArgumentParserProvider()
    {
        return array(
            array('arg1="value" arg2="value 2"', array('arg1' => 'value', 'arg2' => 'value 2')),
            array('arg1=var1', array('arg1' => 'var1')),
            array('[arg "1"]="arg number 1"', array('arg "1"' => "arg number 1")),
            array('arg1 =   "value"', array('arg1' => "value")),
        );
    }

    /**
     * Test Named Argument Parser
     *
     * @param string $arg_string argument text
     * @param        $expected_array
     *
     * @dataProvider namedArgumentParserProvider
     *
     * @return void
     */
    public function testNamedArgumentsParser($arg_string, $expected_array)
    {
        $engine = new \Handlebars\Handlebars();
        $template = new \Handlebars\Template($engine, null, null);
        // get the string version of the arguments array
        $args = $template->parseNamedArguments($arg_string);
        $args = array_map(function ($a) {
            return (string)$a;
        }, $args);
        $this->assertEquals($args, $expected_array);
    }

    /**
     * Test Combined Arguments Parser
     *
     * @param string $arg_string argument text
     * @param        $positional_args
     * @param        $named_args
     *
     * @dataProvider combinedArgumentsParserProvider
     *
     * @return void
     */
    public function testCombinedArgumentsParser($arg_string, $positional_args, $named_args)
    {
        $args = new \Handlebars\Arguments($arg_string);

        // Get the string version of the arguments array
        $stringify = function ($a) {
            return (string)$a;
        };

        if ($positional_args !== false) {
            $this->assertEquals(
                array_map($stringify, $args->getPositionalArguments()),
                $positional_args
            );
        }
        if ($named_args !== false) {
            $this->assertEquals(
                array_map($stringify, $args->getNamedArguments()),
                $named_args
            );
        }
    }

    public function combinedArgumentsParserProvider()
    {
        $result = array();

        // Use data provider for positional arguments parser
        foreach ($this->argumentParserProvider() as $dataSet) {
            $result[] = array(
                $dataSet[0],
                $dataSet[1],
                false,
            );
        }

        // Use data provider for named arguments parser
        foreach ($this->namedArgumentParserProvider() as $dataSet) {
            $result[] = array(
                $dataSet[0],
                false,
                $dataSet[1],
            );
        }

        // Add test cases with combined arguments
        return array_merge(
            $result,
            array(
                array(
                    'arg1 arg2 arg3=value1 arg4="value2"',
                    array('arg1', 'arg2'),
                    array('arg3' => 'value1', 'arg4' => 'value2')
                ),
                array(
                    '@first arg=@last',
                    array('@first'),
                    array('arg' => '@last')
                ),
                array(
                    '[seg arg1] [seg arg2] = [seg value "1"]',
                    array('[seg arg1]'),
                    array('seg arg2' => '[seg value "1"]')
                )
            )
        );
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

    public function integerLiteralInCustomHelperProvider()
    {
        return array(
            array('{{test -5}}', array(), '-5'),
            array('{{test 0}}', array(), '0'),
            array('{{test 12}}', array(), '12'),
            array('{{test 12 [12]}}', array('12' => 'foo'), '12:foo'),
        );
    }

    /**
     * Test integer literals in the context of a helper
     *
     * @param string $template template text
     * @param array  $data     context data
     * @param string $results  The Expected Results
     *
     * @dataProvider integerLiteralInCustomHelperProvider
     *
     * @return void
     */
    public function testIntegerLiteralInCustomHelper($template, $data, $results)
    {
        $engine = new \Handlebars\Handlebars();
        $engine->addHelper('test', function ($template, $context, $args) {
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
        $string = new \Handlebars\StringWrapper("Hello World");
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
        $engine->addHelper('test', function ($template, $context, $args) {
            $parsedArgs = $template->parseArguments($args);

            return (count($parsedArgs) ? $context->get($parsedArgs[0]) : '') . 'Test.';
        });

        // assert that nested syntax is accepted and sub-helper is run
        $this->assertEquals('Test.Test.', $engine->render('{{test (test)}}', array()));

        $engine->addHelper('add', function ($template, $context, $args) {
            $sum = 0;

            foreach ($template->parseArguments($args) as $value) {
                $sum += intval($context->get($value));
            }

            return $sum;
        });

        // assert that subexpression result is inserted correctly as argument to top level helper
        $this->assertEquals('42', $engine->render('{{add 21 (add 10 (add 5 6))}}', array()));

        // assert that bracketed expressions within string literals are treated correctly
        $this->assertEquals("(test)Test.", $engine->render("{{test '(test)'}}", array()));
        $this->assertEquals(")Test.Test.", $engine->render("{{test (test ')')}}", array()));

        $engine->addHelper('concat', function (\Handlebars\Template $template, \Handlebars\Context $context, $args) {
            $result = '';

            foreach ($template->parseArguments($args) as $arg) {
                $result .= $context->get($arg);
            }

            return $result;
        });

        $this->assertEquals('ACB', $engine->render('{{concat a (concat c b)}}', array('a' => 'A', 'b' => 'B', 'c' => 'C')));
        $this->assertEquals('ACB', $engine->render('{{concat (concat a c) b}}', array('a' => 'A', 'b' => 'B', 'c' => 'C')));
        $this->assertEquals('A-B', $engine->render('{{concat (concat a "-") b}}', array('a' => 'A', 'b' => 'B')));
        $this->assertEquals('A-B', $engine->render('{{concat (concat a "-") b}}', array('a' => 'A', 'b' => 'B', 'A-' => '!')));
    }

    public function ifUnlessDepthDoesntChangeProvider()
    {
        return array(array(
            '{{#with b}}{{#if this}}{{../a}}{{/if}}{{/with}}',
            array('a' => 'good', 'b' => 'stump'),
            'good',
        ), array(
            '{{#with b}}{{#unless false}}{{../a}}{{/unless}}{{/with}}',
            array('a' => 'good', 'b' => 'stump'),
            'good',
        ), array(
            '{{#with foo}}{{#if goodbye}}GOODBYE cruel {{../world}}!{{/if}}{{/with}}',
            array('foo' => array('goodbye' => true), 'world' => 'world'),
            'GOODBYE cruel world!',
        ));
    }

    /**
     * Test if and unless do not add an extra layer when accessing parent
     *
     * @dataProvider ifUnlessDepthDoesntChangeProvider
     */
    public function testIfUnlessDepthDoesntChange($template, $data, $expected)
    {
        $loader = new \Handlebars\Loader\StringLoader();
        $engine = new \Handlebars\Handlebars(array('loader' => $loader));

        $this->assertEquals($expected, $engine->render($template, $data));
    }

    /**
     * Test of Arguments to string conversion.
     */
    public function testArgumentsString()
    {
        $argsString = 'foo bar [foo bar] baz="value"';
        $args = new \Handlebars\Arguments($argsString);
        $this->assertEquals($argsString, (string)$args);
    }


    public function stringLiteralsInIfAndUnlessHelpersProvider()
    {
        return array(
            // IfHelper
            array('{{#if "truthyString"}}true{{else}}false{{/if}}', array(), "true"),
            array("{{#if 'truthyStringSingleQuotes'}}true{{else}}false{{/if}}", array(), "true"),
            array("{{#if ''}}true{{else}}false{{/if}}", array(), "false"),
            array("{{#if '0'}}true{{else}}false{{/if}}", array(), "false"),
            array("{{#if (add 0 1)}}true{{else}}false{{/if}}", array(), "true"),
            array("{{#if (add 1 -1)}}true{{else}}false{{/if}}", array(), "false"),
            // UnlessHelper
            array('{{#unless "truthyString"}}true{{else}}false{{/unless}}', array(), "false"),
            array("{{#unless 'truthyStringSingleQuotes'}}true{{else}}false{{/unless}}", array(), "false"),
            array("{{#unless ''}}true{{else}}false{{/unless}}", array(), "true"),
            array("{{#unless '0'}}true{{else}}false{{/unless}}", array(), "true"),
            array("{{#unless (add 0 1)}}true{{else}}false{{/unless}}", array(), "false"),
            array("{{#unless (add 1 -1)}}true{{else}}false{{/unless}}", array(), "true"),
        );
    }

    /**
     * Test string literals in the context of if and unless helpers
     *
     * @param string $template template text
     * @param array  $data     context data
     * @param string $results  The Expected Results
     *
     * @dataProvider stringLiteralsInIfAndUnlessHelpersProvider
     *
     * @return void
     */
    public function testStringLiteralsInIfAndUnlessHelpers($template, $data, $results)
    {
        $engine = new \Handlebars\Handlebars();

        $engine->addHelper('add', function ($template, $context, $args) {
            $sum = 0;

            foreach ($template->parseArguments($args) as $value) {
                $sum += intval($context->get($value));
            }

            return $sum;
        });

        $res = $engine->render($template, $data);
        $this->assertEquals($res, $results);
    }

    public function rootSpecialVariableProvider()
    {
        return array(
            array('{{foo}} {{ @root.foo }}', array( 'foo' => 'bar' ), "bar bar"),
            array('{{@root.foo}} {{#each arr}}{{ @root.foo }}{{/each}}', array( 'foo' => 'bar', 'arr' => array( '1' ) ), "bar bar"),
        );
    }

    /**
     * Test 'root' special variable
     *
     * @param string $template template text
     * @param array  $data     context data
     * @param string $results  The Expected Results
     *
     * @dataProvider rootSpecialVariableProvider
     *
     * @return void
     */
    public function testRootSpecialVariableHelpers($template, $data, $results)
    {
        $engine = new \Handlebars\Handlebars();

        $res = $engine->render($template, $data);
        $this->assertEquals($res, $results);
    }

}

class TestClassWithToStringMethod {
    public function __toString() {
        return 'test';
    }
}

/**
 * Testcase for testInlineLoader
 *
 */
__halt_compiler();
@@ template1
This is a inline template.

@@ template2
a
b
c
d
