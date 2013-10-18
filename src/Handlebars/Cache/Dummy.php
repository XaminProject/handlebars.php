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
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */


/**
 * A dummy array cache
 * 
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */

class Handlebars_Cache_Dummy implements Handlebars_Cache
{
    private $_cache = array();

    /**
     * Get cache for $name if exist. 
     *
     * @param string $name Cache id
     *
     * @return data on hit, boolean false on cache not found 
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->_cache)) {
            return $this->_cache[$name];
        }           
        return false;
    }        

    /**
     * Set a cache
     *
     * @param string $name  cache id
     * @param mixed  $value data to store
     *
     * @return void
     */
    public function set($name, $value)
    {
        $this->_cache[$name] = $value;
    }     

    /**
     * Remove cache 
     *
     * @param string $name Cache id
     *
     * @return void
     */
    public function remove($name)
    {
        unset($this->_cache[$name]);
    }
}    