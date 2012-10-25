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
 * @license   GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

/**
 * Handlebars Template filesystem Loader implementation.
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version   Release: @package_version@
 * @link      http://xamin.ir *
 * @implements Loader
 */
class Handlebars_Loader_FilesystemLoader implements Handlebars_Loader
{
    private $_baseDir;
    private $_extension = '.handlebars';
    private $_templates = array();

    /**
     * Handlebars filesystem Loader constructor.
     *
     * Passing an $options array allows overriding certain Loader options during instantiation:
     *
     *     $options = array(
     *         // The filename extension used for Mustache templates. Defaults to '.mustache'
     *         'extension' => '.ms',
     *     );
     *
     * @param string $baseDir Base directory containing Mustache template files.
     * @param array  $options Array of Loader options (default: array())
     *
     * @throws RuntimeException if $baseDir does not exist.
     */
    public function __construct($baseDir, array $options = array())
    {
        $this->_baseDir = rtrim(realpath($baseDir), '/');

        if (!is_dir($this->_baseDir)) {
            throw new RuntimeException('FilesystemLoader baseDir must be a directory: '.$baseDir);
        }

        if (isset($options['extension'])) {
            $this->_extension = '.' . ltrim($options['extension'], '.');
        }
    }

    /**
     * Load a Template by name.
     *
     *     $loader = new FilesystemLoader(dirname(__FILE__).'/views');
     *     $loader->load('admin/dashboard'); // loads "./views/admin/dashboard.mustache";
     *
     * @param string $name template name
     *
     * @return string Handkebars Template source
     */
    public function load($name)
    {
        if (!isset($this->_templates[$name])) {
            $this->_templates[$name] = $this->loadFile($name);
        }

        return $this->_templates[$name];
    }

    /**
     * Helper function for loading a Mustache file by name.
     *
     * @param string $name template name
     *
     * @return string Mustache Template source
     * @throws InvalidArgumentException if a template file is not found.
     */
    protected function loadFile($name)
    {
        $fileName = $this->getFileName($name);

        if (!file_exists($fileName)) {
            throw new InvalidArgumentException('Template '.$name.' not found.');
        }

        return file_get_contents($fileName);
    }

    /**
     * Helper function for getting a Mustache template file name.
     *
     * @param string $name template name
     *
     * @return string Template file name
     */
    protected function getFileName($name)
    {
        $fileName = $this->_baseDir . '/' . $name;
        if (substr($fileName, 0 - strlen($this->_extension)) !== $this->_extension) {
            $fileName .= $this->_extension;
        }

        return $fileName;
    }
}
