<?php
/**
 * This file is part of Handlebars-php
 * Base on mustache-php https://github.com/bobthecow/mustache.php
 *
 * A flat-file filesystem cache.
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    Alex Soncodi <alex@brokerloop.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Mardix <https://github.com/mardix>
 * @copyright 2013 (c) Brokerloop, Inc.
 * @copyright 2013 (c) Behrooz Shabani
 * @copyright 2013 (c) Mardix
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

namespace Handlebars\Cache;
use Handlebars\Cache;
use InvalidArgumentException;
use RuntimeException;

class Disk implements Cache
{

    private $path = '';
    private $prefix = '';
    private $suffix = '';

    /**
     * Construct the disk cache.
     *
     * @param string $path   Filesystem path to the disk cache location
     * @param string $prefix optional file prefix, defaults to empty string
     * @param string $suffix optional file extension, defaults to empty string
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($path, $prefix = '', $suffix = '')
    {
        if (empty($path)) {
            throw new InvalidArgumentException('Must specify disk cache path');
        } elseif (!is_dir($path)) {
            @mkdir($path, 0777, true);

            if (!is_dir($path)) {
                throw new RuntimeException('Could not create cache file path');
            }
        }

        $this->path = $path;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * Gets the full disk path for a given cache item's file,
     * taking into account the cache path, optional prefix,
     * and optional extension.
     *
     * @param string $name Name of the cache item
     *
     * @return string full disk path of cached item
     */
    private function getPath($name)
    {
        return $this->path . DIRECTORY_SEPARATOR .
            $this->prefix . $name . $this->suffix;
    }

    /**
     * Get cache for $name if it exists.
     *
     * @param string $name Cache id
     *
     * @return mixed data on hit, boolean false on cache not found
     */
    public function get($name)
    {
        $path = $this->getPath($name);

        return (file_exists($path)) ?
            unserialize(file_get_contents($path)) : false;
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
        $path = $this->getPath($name);

        file_put_contents($path, serialize($value));
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
        $path = $this->getPath($name);

        unlink($path);
    }

}
