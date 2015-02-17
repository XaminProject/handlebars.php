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
 * Handlebars helpers 
 *
 * a collection of helper function. normally a function like 
 * function ($sender, $name, $arguments) $arguments is unscaped arguments and is a string, not array
 * TODO: Add support for an interface with an execute method
 * 
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */

class Handlebars_Helpers
{
    /**
     * @var array array of helpers
     */
    protected $helpers = array();

    /**
     * Create new helper container class
     *
     * @param array $helpers  array of name=>$value helpers
     * @param array $defaults add defaults helper (if, unless, each,with)
     *
     * @throw InvalidArgumentException when $helpers is not an array (or traversable) or helper is not a caallable
     */
    public function __construct($helpers = null, $defaults = true)
    {
        if ($defaults) {
            $this->addDefaultHelpers();
        }            
        if ($helpers != null) {
            if (!is_array($helpers) && !$helpers instanceof Traversable) {
                throw new InvalidArgumentException('HelperCollection constructor expects an array of helpers');
            }                
            foreach ($helpers as $name => $helper) {
                $this->add($name, $helpers);
            }
        }            
    }

	/**
	 * Create handler for the 'if' helper.
	 * Needed for compatibility with PHP 5.2 since it doesn't support anonymous functions.
	 *
	 * @param $template
	 * @param $context
	 * @param $args
	 * @param $source
	 *
	 * @return mixed
	 */
	public static function _helper_if($template, $context, $args, $source) {
		$tmp = $context->get($args);
		$buffer = '';

		if ($tmp) {
			$template->setStopToken('else');
			$buffer = $template->render($context);
			$template->setStopToken(false);
			$template->discard($context);
		} else {
			$template->setStopToken('else');
			$template->discard($context);
			$template->setStopToken(false);
			$buffer = $template->render($context);
		}
		return $buffer;
	}

	/**
	 * Create handler for the 'each' helper.
	 * Needed for compatibility with PHP 5.2 since it doesn't support anonymous functions.
	 *
	 * @param $template
	 * @param $context
	 * @param $args
	 * @param $source
	 *
	 * @return mixed
	 */
	public static function _helper_each($template, $context, $args, $source) {
		$tmp = $context->get($args);
		$buffer = '';
		if (is_array($tmp) || $tmp instanceof Traversable) {
			$islist = ( array_keys($tmp) == range(0, count($tmp) - 1) );

			$index = 0;
			foreach ($tmp as $key => $var) {

				$var['@first'] = (!array_key_exists('@first', $var)) ? ($index === 0) : $var['@first'];
				$var['@last']  = (!array_key_exists('@last', $var))  ? ($index === count($tmp)-1) : $var['@last'];
				$index++;

				if( $islist ) {
					$context->pushIndex($key);
				} else {
					$context->pushKey($key);
				}
				$context->push($var);
				$buffer .= $template->render($context);
				$context->pop();
				if( $islist ) {
					$context->popIndex();
				} else {
					$context->popKey();
				}
			}
		}
		return $buffer;
	}

	/**
	 * Create handler for the 'unless' helper.
	 * Needed for compatibility with PHP 5.2 since it doesn't support anonymous functions.
	 *
	 * @param $template
	 * @param $context
	 * @param $args
	 * @param $source
	 *
	 * @return mixed
	 */
	public static function _helper_unless($template, $context, $args, $source) {
		$tmp = $context->get($args);
		$buffer = '';
		if (!$tmp) {
			$buffer = $template->render($context);
		}
		return $buffer;
	}

	/**
	 * Create handler for the 'with' helper.
	 * Needed for compatibility with PHP 5.2 since it doesn't support anonymous functions.
	 *
	 * @param $template
	 * @param $context
	 * @param $args
	 * @param $source
	 *
	 * @return mixed
	 */
	public static function _helper_with($template, $context, $args, $source) {
		$tmp = $context->get($args);
		$context->push($tmp);
		$buffer = $template->render($context);
		$context->pop();
		return $buffer;
	}

	/**
	 * Create handler for the 'bindAttr' helper.
	 * Needed for compatibility with PHP 5.2 since it doesn't support anonymous functions.
	 *
	 * @param $template
	 * @param $context
	 * @param $args
	 * @param $source
	 *
	 * @return mixed
	 */
	public static function _helper_bindAttr($template, $context, $args, $source) {
		return $args;
	}

    /**
     * Add default helpers (if unless each with bindAttr)
     * 
     * @return void
     */
    protected function addDefaultHelpers()
    {
        $this->add(
            'if',
	        array('Handlebars_Helpers', '_helper_if')
        );

        $this->add(
            'each',
	        array('Handlebars_Helpers', '_helper_each')
        );

        $this->add(
            'unless',
	        array('Handlebars_Helpers', '_helper_unless')
        );

        $this->add(
            'with',
	        array('Handlebars_Helpers', '_helper_with')
        );

        //Just for compatibility with ember
        $this->add(
            'bindAttr',
	        array('Handlebars_Helpers', '_helper_bindAttr')
        );
    }

    /**
     * Add a new helper to helpers 
     * 
     * @param string   $name   helper name
     * @param callable $helper a function as a helper
     *
     * @return void
     * @throw InvalidArgumentException if $helper is not a callable
     */
    public function add($name ,$helper) 
    {
        if (!is_callable($helper)) {
            throw new InvalidArgumentException("$name Helper is not a callable.");
        }            
        $this->helpers[$name] = $helper;
    }
    
    /**
     * Check if $name helper is available
     * 
     * @param string $name helper name
     *
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->helpers);
    }

    /**
     * Get a helper. __magic__ method :) 
     * 
     * @param string $name helper name
     *
     * @return callable helper function
     * @throw InvalidArgumentException if $name is not available
     */
    public function __get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException('Unknow helper :' . $name);
        }            
        return $this->helpers[$name];
    }

    /**
     * Check if $name helper is available __magic__ method :) 
     * 
     * @param string $name helper name
     *
     * @return boolean
     * @see Handlebras_Helpers::has
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Add a new helper to helpers __magic__ method :) 
     * 
     * @param string   $name   helper name
     * @param callable $helper a function as a helper
     *
     * @return void
     * @throw InvalidArgumentException if $helper is not a callable
     */
    public function __set($name ,$helper)
    {
        $this->add($name, $helpers);
    }        


    /**
     * Unset a helper
     * 
     * @param string $name helpername to remove
     *
     * @return void 
     */
    public function __unset($name)
    {
        unset($this->helpers[$name]);
    }

    /**
     * Check whether a given helper is present in the collection.
     *
     * @param string $name helper name
     *
     * @return void
     * @throws InvalidArgumentException if the requested helper is not present.
     */
    public function remove($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException('Unknown helper: ' . $name);
        }

        unset($this->helpers[$name]);
    }

    /**
     * Clear the helper collection.
     *
     * Removes all helpers from this collection
     *
     * @return void
     */
    public function clear()
    {
        $this->helpers = array();
    }

    /**
     * Check whether the helper collection is empty.
     *
     * @return boolean True if the collection is empty
     */
    public function isEmpty()
    {
        return empty($this->helpers);
    }
    
}    
