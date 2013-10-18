<?php
/**
 * This file is part of Mustache.php.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Changes to match xamin-std and handlebars made by xamin team
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
 * Autloader for handlebars.php
 * 
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */
class Handlebars_Autoloader
{

    private $_baseDir;

    /**
     * Autoloader constructor.
     *
     * @param string $baseDir Handlebars library base directory (default: dirname(__FILE__).'/..')
     */
    public function __construct($baseDir = null)
    {
        if ($baseDir === null) {
            $this->_baseDir = dirname(__FILE__).'/..';
        } else {
            $this->_baseDir = rtrim($baseDir, '/');
        }
    }

    /**
     * Register a new instance as an SPL autoloader.
     *
     * @param string $baseDir Handlebars library base directory (default: dirname(__FILE__).'/..')
     *
     * @return Handlebars_Autoloader Registered Autoloader instance
     */
    public static function register($baseDir = null)
    {
        $loader = new self($baseDir);
        spl_autoload_register(array($loader, 'autoload'));

        return $loader;
    }

    /**
     * Autoload Handlebars classes.
     *
     * @param string $class class to load
     *
     * @return void
     */
    public function autoload($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }

        if (strpos($class, 'Handlebars') !== 0) {
            return;
        }

        $file = sprintf('%s/%s.php', $this->_baseDir, str_replace('_', '/', $class));
        if (is_file($file)) {
            include $file;
        }
    }
}
