<?php
/**
 * This file is part of Handlebars-php
 * Base on mustache-php https://github.com/bobthecow/mustache.php
 *
 * PHP version 5.3
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    Joey Baker <joey@byjoeybaker.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @copyright 2013 (c) Meraki, LLP
 * @copyright 2013 (c) Behrooz Shabani
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

namespace Handlebars\Cache;
use Handlebars\Cache;

/**
 * A APC cache
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    Joey Baker <joey@byjoeybaker.com>
 * @copyright 2012 (c) Meraki, LLP
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */

class APC implements Cache
{

    /**
     * @var string
     */
    private $_prefix;

    /**
     * Construct the APC cache.
     *
     * @param string|null $prefix optional key prefix, defaults to null
     */
    public function __construct( $prefix = null )
    {
        $this->_prefix = (string)$prefix;
    }

    /**
     * Get cache for $name if exist.
     *
     * @param string $name Cache id
     *
     * @return mixed data on hit, boolean false on cache not found
     */
    public function get($name)
    {
        $success = null;
        $result = apc_fetch($this->_getKey($name), $success);

        return $success ? $result : false;

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
        apc_store($this->_getKey($name), $value);
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
        apc_delete($this->_getKey($name));
    }

    /**
     * Gets the full cache key for a given cache item's
     *
     * @param string $name Name of the cache item
     *
     * @return string full cache key of cached item
     */
    private function _getKey($name)
    {
        return $this->_prefix . ':' . $name;
    }

}
