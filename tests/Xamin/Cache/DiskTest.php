<?php
/**
 * This file is part of Handlebars-php
 * Base on mustache-php https://github.com/bobthecow/mustache.php
 *
 * PHP version 5.3
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    Mária Šormanová <maria.sormanova@gmail.com>
 * @copyright 2016 (c) Mária Šormanová
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

/**
 * Test of Disk cache driver
 *
 * @category   Xamin
 * @package    Handlebars
 * @subpackage Test
 * @author     Mária Šormanová <maria.sormanova@gmail.com>
 * @license    MIT <http://opensource.org/licenses/MIT>
 * @version    Release: @package_version@
 * @link       http://xamin.ir
 */

class DiskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp()
    {
        \Handlebars\Autoloader::register();
    }

    /**
     * Return the new driver
     *
     * @param string $path folder where the cache is located
     *
     * @return \Handlebars\Cache\Disk
     */
    private function _getCacheDriver( $path = '')
    {
        return new \Handlebars\Cache\Disk($path);
    }

    /**
     * Test the Disk cache
     *
     * @return void
     */
    public function testDiskCache()
    {
        $cache_dir = getcwd().'/tests/cache';
        $driver = $this->_getCacheDriver($cache_dir);

        $this->assertEquals(false, $driver->get('foo'));

        $driver->set('foo', "hello world");
        $this->assertEquals("hello world", $driver->get('foo'));

        $driver->set('foo', "hello world", -1);
        $this->assertEquals(false, $driver->get('foo'));

        $driver->set('foo', "hello world", 3600);
        $this->assertEquals("hello world", $driver->get('foo'));

        $driver->set('foo', array(12));
        $this->assertEquals(array(12), $driver->get('foo'));

        $driver->remove('foo');
        $this->assertEquals(false, $driver->get('foo'));
        
        rmdir($cache_dir);
    }
}

?>