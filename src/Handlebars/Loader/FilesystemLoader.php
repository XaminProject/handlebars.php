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
 * Handlebars Template filesystem Loader implementation.
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
class Handlebars_Loader_FilesystemLoader implements Handlebars_Loader
{
    private $_baseDir;
    private $_extension = '.handlebars';
    private $_prefix = '';
    private $_templates = array();

    /**
     * Handlebars filesystem Loader constructor.
     *
     * Passing an $options array allows overriding certain Loader options during instantiation:
     *
     *     $options = array(
     *         // The filename extension used for Handlebars templates. Defaults to '.handlebars'
     *         'extension' => '.other',
     *     );
     *
     * @param string|array $baseDirs A path contain template files or array of paths
     * @param array        $options  Array of Loader options (default: array())
     *
     * @throws RuntimeException if $baseDir does not exist.
     */
    public function __construct($baseDirs, array $options = array())
    {
        if (is_string($baseDirs)) {
            $baseDirs = array(rtrim(realpath($baseDirs), '/'));
        } else {
            foreach ($baseDirs as &$dir) {
                $dir = array(rtrim(realpath($dir), '/'));
            }
        }

        $this->_baseDir = $baseDirs;

        foreach ($this->_baseDir as $dir) {
            if (!is_dir($dir)) {
                throw new RuntimeException('FilesystemLoader baseDir must be a directory: ' . $dir);
            }
        }

        if (isset($options['extension'])) {
            $this->_extension = '.' . ltrim($options['extension'], '.');
        }

        if (isset($options['prefix'])) {
            $this->_prefix = $options['prefix'];
        }
    }

    /**
     * Load a Template by name.
     *
     *     $loader = new FilesystemLoader(dirname(__FILE__).'/views');
     *     $loader->load('admin/dashboard'); // loads "./views/admin/dashboard.handlebars";
     *
     * @param string $name template name
     *
     * @return Handlebars_String Handlebars Template source
     */
    public function load($name)
    {
        if (!isset($this->_templates[$name])) {
            $this->_templates[$name] = $this->loadFile($name);
        }
        return new Handlebars_String($this->_templates[$name]);
    }

    /**
     * Helper function for loading a Handlebars file by name.
     *
     * @param string $name template name
     *
     * @return string Handlebars Template source
     * @throws InvalidArgumentException if a template file is not found.
     */
    protected function loadFile($name)
    {
        $fileName = $this->getFileName($name);

        if ($fileName === false) {
            throw new InvalidArgumentException('Template ' . $name . ' not found.');
        }

        return file_get_contents($fileName);
    }

    /**
     * Helper function for getting a Handlebars template file name.
     *
     * @param string $name template name
     *
     * @return string Template file name
     */
    protected function getFileName($name)
    {
        foreach ($this->_baseDir as $baseDir) {
            $fileName = $baseDir . '/';
            $fileParts = explode('/', $name);
            $file = array_pop($fileParts);

            if (substr($file, strlen($this->_prefix)) !== $this->_prefix) {
                $file = $this->_prefix . $file;
            }

            $fileParts[] = $file;
            $fileName .= implode('/', $fileParts);

            if (substr($fileName, 0 - strlen($this->_extension)) !== $this->_extension) {
                $fileName .= $this->_extension;
            }
            if (file_exists($fileName)) {
                break;
            }
            $fileName = false;
        }
        return $fileName;
    }
}
