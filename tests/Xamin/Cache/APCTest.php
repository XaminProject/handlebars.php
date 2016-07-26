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
 * @author    Mária Šormanová <maria.sormanova@gmail.com>
 * @copyright 2013 (c) f0ruD A
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

/**
 * Test of APC cache driver
 *
 * Run without sikp:
 * php -d apc.enable_cli=1  ./vendor/bin/phpunit
 *
 * @category   Xamin
 * @package    Handlebars
 * @subpackage Test
 * @author     Tamás Szijártó <szijarto.tamas.developer@gmail.com>
 * @license    MIT <http://opensource.org/licenses/MIT>
 * @version    Release: @package_version@
 * @link       http://xamin.ir
 */
class APCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp()
    {
        if ( ! extension_loaded('apc') || false === @apc_cache_info()) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of APC');
        }
    }

    /**
     * Return the new driver
     *
     * @param null|string $prefix optional key prefix, defaults to null
     *
     * @return \Handlebars\Cache\APC
     */
    private function _getCacheDriver( $prefix = null )
    {
        return new \Handlebars\Cache\APC($prefix);
    }

    /**
     * Test with cache prefix
     *
     * @return void
     */
    public function testWithPrefix()
    {
        $prefix = __CLASS__;
        $driver = $this->_getCacheDriver($prefix);

        $this->assertEquals(false, $driver->get('foo'));

        $driver->set('foo', 10);
        $this->assertEquals(10, $driver->get('foo'));

        $driver->set('foo', array(12));
        $this->assertEquals(array(12), $driver->get('foo'));

        $driver->remove('foo');
        $this->assertEquals(false, $driver->get('foo'));
    }

    /**
     * Test without cache prefix
     *
     * @return void
     */
    public function testWithoutPrefix()
    {
        $driver = $this->_getCacheDriver();

        $this->assertEquals(false, $driver->get('foo'));

        $driver->set('foo', 20);
        $this->assertEquals(20, $driver->get('foo'));

        $driver->set('foo', array(22));

        $this->assertEquals(array(22), $driver->get('foo'));

        $driver->remove('foo');
        $this->assertEquals(false, $driver->get('foo'));
    }

    /**
     * Test ttl
     *
     * @return void
     */
    public function testTtl()
    {
        $driver = $this->_getCacheDriver();

        $driver->set('foo', 10, -1);
        $this->assertEquals(false, $driver->get('foo'));

        $driver->set('foo', 20, 3600);
        $this->assertEquals(20, $driver->get('foo'));
    }
}