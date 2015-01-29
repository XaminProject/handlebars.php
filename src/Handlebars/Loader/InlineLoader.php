<?php
/**
 * Handlebars Inline Template string Loader implementation.
 *
 * With the InlineLoader, templates can be defined at the end of any PHP source
 * file:
 *
 *     $loader  = new \Handlebars\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
 *     $hello   = $loader->load('hello');
 *     $goodbye = $loader->load('goodbye');
 *
 *     __halt_compiler();
 *
 *     @@ hello
 *     Hello, {{ planet }}!
 *
 *     @@ goodbye
 *     Goodbye, cruel {{ planet }}
 *
 * Templates are deliniated by lines containing only `@@ name`.
 */

namespace Handlebars\Loader;

use Handlebars\Loader;
use Handlebars\String;

class InlineLoader implements Loader
{
    protected $_fileName;
    protected $_offset;
    protected $_templates;

    /**
     * The InlineLoader requires a filename and offset to process templates.
     * The magic constants `__FILE__` and `__COMPILER_HALT_OFFSET__` are usually
     * perfectly suited to the job:
     *
     *     $loader = new \Handlebars\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
     *
     * Note that this only works if the loader is instantiated inside the same
     * file as the inline templates. If the templates are located in another
     * file, it would be necessary to manually specify the filename and offset.
     *
     * @param string $fileName The file to parse for inline templates
     * @param int    $offset   A string offset for the start of the templates.
     *                         This usually coincides with the `__halt_compiler`
     *                         call, and the `__COMPILER_HALT_OFFSET__`.
     */
    public function __construct($fileName, $offset)
    {
        if (!is_file($fileName)) {
            throw new \InvalidArgumentException('InlineLoader expects a valid filename.');
        }

        if (!is_int($offset) || $offset < 0) {
            throw new \InvalidArgumentException('InlineLoader expects a valid file offset.');
        }

        $this->_fileName = $fileName;
        $this->_offset   = $offset;
    }

    /**
     * Load a Template by name.
     *
     * @param string $name
     *
     * @return string Mustache Template source
     */
    public function load($name)
    {
        $this->loadTemplates();

        if (!array_key_exists($name, $this->_templates)) {
            throw new \InvalidArgumentException("Template {$name} not found.");
        }

        return $this->_templates[$name];
    }

    /**
     * Parse and load templates from the end of a source file.
     */
    protected function loadTemplates()
    {
        if (!is_null($this->_templates)) {
            return;
        }

        $this->_templates = array();
        $data = file_get_contents($this->_fileName, false, null, $this->_offset);
        foreach (preg_split('/^@@(?= [\w\d\.]+$)/m', $data, -1) as $chunk) {
            if (trim($chunk)) {
                list($name, $content)         = explode("\n", $chunk, 2);
                $this->_templates[trim($name)] = trim($content);
            }
        }
    }
}