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
 * Handlebars Template string Loader implementation.
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir *
 * @implements Loader
 */
namespace Handlebars\Loader;
use Handlebars\Loader;
use Handlebars\String;

class StringLoader implements Loader
{

    /**
     * Load a Template by source.
     *
     * @param string $name Handlebars Template source
     *
     * @return String Handlebars Template source
     */
    public function load($name)
    {
        return new String($name);
    }
}
