<?php
/**
 * This file is part of Handlebars-php
 * Based on mustache-php https://github.com/bobthecow/mustache.php
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Mardix <https://github.com/mardix>
 * @copyright 2012 (c) ParsPooyesh Co
 * @copyright 2013 (c) Behrooz Shabani
 * @copyright 2013 (c) Mardix
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

namespace Handlebars;
use Handlebars\Loader\StringLoader;
use Handlebars\Cache\Dummy;
use InvalidArgumentException;


class Handlebars
{
    private static $instance = null;
    const VERSION = '2.0.0';

    /**
     * factory method
     *
     * @param array $options see __construct's options parameter
     *
     * @return Handlebars
     */
    public static function factory($options = array())
    {
        if (! self::$instance) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Helpers
     */
    private $helpers;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Loader
     */
    private $partialsLoader;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var callable escape function to use
     */
    private $escape = 'htmlspecialchars';

    /**
     * @var array parametes to pass to escape function
     */
    private $escapeArgs = array(
        ENT_COMPAT,
        'UTF-8'
    );

    private $aliases = array();

    /**
     * Handlebars engine constructor
     * $options array can contain :
     * helpers        => Helpers object
     * escape         => a callable function to escape values
     * escapeArgs     => array to pass as extra parameter to escape function
     * loader         => Loader object
     * partials_loader => Loader object
     * cache          => Cache object
     *
     * @param array $options array of options to set
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Array $options = [])
    {
        if (isset($options['helpers'])) {
            $this->setHelpers($options['helpers']);
        }

        if (isset($options['loader'])) {
            $this->setLoader($options['loader']);
        }

        if (isset($options['partials_loader'])) {
            $this->setPartialsLoader($options['partials_loader']);
        }

        if (isset($options['cache'])) {
            $this->setCache($options['cache']);
        }

        if (isset($options['escape'])) {
            if (!is_callable($options['escape'])) {
                throw new InvalidArgumentException(
                    'Handlebars Constructor "escape" option must be callable'
                );
            }
            $this->escape = $options['escape'];
        }

        if (isset($options['escapeArgs'])) {
            if (!is_array($options['escapeArgs'])) {
                $options['escapeArgs'] = array($options['escapeArgs']);
            }
            $this->escapeArgs = $options['escapeArgs'];
        }

        if (isset($options['partials_alias'])
            && is_array($options['partials_alias'])
        ) {
            $this->aliases = $options['partials_alias'];
        }
    }


    
    /**
     * Shortcut 'render' invocation.
     *
     * Equivalent to calling `$handlebars->loadTemplate($template)->render($data);`
     *
     * @param string $template template name
     * @param mixed  $data     data to use as context
     * @return string Rendered template
     */
    public function render($template, $data)
    {
        return $this->loadTemplate($template)->render($data);
    }
    /**
     * To invoke when this object is called as a function
     *
     * @param string $template template name
     * @param mixed  $data     data to use as context
     * @return string Rendered template
     */
    public function __invoke($template, $data)
    {
        return $this->render($template, $data);
    }
    
    /**
     * Set helpers for current enfine
     *
     * @param Helpers $helpers handlebars helper
     *
     * @return void
     */
    public function setHelpers(Helpers $helpers)
    {
        $this->helpers = $helpers;
    }

    /**
     * Get helpers, or create new one if ther is no helper
     *
     * @return Helpers
     */
    public function getHelpers()
    {
        if (!isset($this->helpers)) {
            $this->helpers = new Helpers();
        }
        return $this->helpers;
    }

    /**
     * Add a new helper.
     *
     * @param string $name   helper name
     * @param mixed  $helper helper callable
     *
     * @return void
     */
    public function addHelper($name, $helper)
    {
        $this->getHelpers()->add($name, $helper);
    }

    /**
     * Get a helper by name.
     *
     * @param string $name helper name
     * @return callable Helper
     */
    public function getHelper($name)
    {
        return $this->getHelpers()->__get($name);
    }

    /**
     * Check whether this instance has a helper.
     *
     * @param string $name helper name
     * @return boolean True if the helper is present
     */
    public function hasHelper($name)
    {
        return $this->getHelpers()->has($name);
    }

    /**
     * Remove a helper by name.
     *
     * @param string $name helper name
     * @return void
     */
    public function removeHelper($name)
    {
        $this->getHelpers()->remove($name);
    }

    /**
     * Set current loader
     *
     * @param Loader $loader handlebars loader
     * @return void
     */
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * get current loader
     *
     * @return Loader
     */
    public function getLoader()
    {
        if (! isset($this->loader)) {
            $this->loader = new StringLoader();
        }
        return $this->loader;
    }

    /**
     * Set current partials loader
     *
     * @param Loader $loader handlebars loader
     * @return void
     */
    public function setPartialsLoader(Loader $loader)
    {
        $this->partialsLoader = $loader;
    }

    /**
     * get current partials loader
     *
     * @return Loader
     */
    public function getPartialsLoader()
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new StringLoader();
        }
        return $this->partialsLoader;
    }

    /**
     * Set cache  for current engine
     *
     * @param Cache $cache handlebars cache
     * @return void
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get cache
     *
     * @return Cache
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = new Dummy();
        }
        return $this->cache;
    }

    /**
     * Get current escape function
     *
     * @return callable
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set current escape function
     *
     * @param callable $escape function
     * @throws \InvalidArgumentException
     * @return void
     */
    public function setEscape($escape)
    {
        if (!is_callable($escape)) {
            throw new InvalidArgumentException(
                'Escape function must be a callable'
            );
        }
        $this->escape = $escape;
    }

    /**
     * Get current escape function
     *
     * @return array
     */
    public function getEscapeArgs()
    {
        return $this->escapeArgs;
    }

    /**
     * Set current escape function
     *
     * @param array $escapeArgs arguments to pass as extra arg to function
     * @return void
     */
    public function setEscapeArgs($escapeArgs)
    {
        if (! is_array($escapeArgs)) {
            $escapeArgs = array($escapeArgs);
        }
        $this->escapeArgs = $escapeArgs;
    }


    /**
     * Set the Handlebars Tokenizer instance.
     *
     * @param Tokenizer $tokenizer tokenizer
     * @return void
     */
    public function setTokenizer(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * Get the current Handlebars Tokenizer instance.
     *
     * If no Tokenizer instance has been explicitly specified, this method will
     * instantiate and return a new one.
     *
     * @return Tokenizer
     */
    public function getTokenizer()
    {
        if (! isset($this->tokenizer)) {
            $this->tokenizer = new Tokenizer();
        }

        return $this->tokenizer;
    }

    /**
     * Set the Handlebars Parser instance.
     *
     * @param Parser $parser parser object
     * @return void
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Get the current Handlebars Parser instance.
     *
     * If no Parser instance has been explicitly specified, this method will
     * instantiate and return a new one.
     *
     * @return Parser
     */
    public function getParser()
    {
        if (! isset($this->parser)) {
            $this->parser = new Parser();
        }
        return $this->parser;
    }

    /**
     * Load a template by name with current template loader
     *
     * @param string $name template name
     *
     * @return Template
     */
    public function loadTemplate($name)
    {
        $source = $this->getLoader()->load($name);
        $tree = $this->tokenize($source);
        return new Template($this, $tree, $source);
    }

    /**
     * Load a partial by name with current partial loader
     *
     * @param string $name partial name
     *
     * @return Template
     */
    public function loadPartial($name)
    {
        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }
        $source = $this->getPartialsLoader()->load($name);
        $tree = $this->tokenize($source);
        return new Template($this, $tree, $source);
    }

    /**
     * Register partial alias
     *
     * @param string $alias   Partial alias
     * @param string $content The real value
     * @return void
     */
    public function registerPartial($alias, $content)
    {
        $this->aliases[$alias] = $content;
    }

    /**
     * Un-register partial alias
     *
     * @param string $alias Partial alias
     * @return void
     */
    public function unRegisterPartial($alias)
    {
        if (isset($this->aliases[$alias])) {
            unset($this->aliases[$alias]);
        }
    }

    /**
     * Load string into a template object
     *
     * @param string $source string to load
     * @return Template
     */
    public function loadString($source)
    {
        $tree = $this->tokenize($source);
        return new Template($this, $tree, $source);
    }

    /**
     * try to tokenize source, or get them from cache if available
     *
     * @param string $source handlebars source code
     * @return array handlebars parsed data into array
     */
    private function tokenize($source)
    {
        $hash = md5(sprintf('version: %s, data : %s', self::VERSION, $source));
        $tree = $this->getCache()->get($hash);
        if ($tree === false) {
            $tokens = $this->getTokenizer()->scan($source);
            $tree = $this->getParser()->parse($tokens);
            $this->getCache()->set($hash, $tree);
        }
        return $tree;
    }

}