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
 * @license   GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
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
     * Add default helpers (if unless each with)
     * 
     * @return void
     */
    protected function addDefaultHelpers()
    {
        $this->add(
            'if', 
            function ($template, $context, $args, $source) {
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
        );

        $this->add(
            'each', 
            function ($template, $context, $args, $source) {
                $tmp = $context->get($args);
                $buffer = '';
                if (is_array($tmp) || $tmp instanceof Traversable) {
                    foreach ($tmp as $var) {
                        $context->push($var);
                        $buffer .= $template->render($context);
                        $context->pop();
                    }
                }            
                return $buffer;
            }
        ); 

        $this->add(
            'unless', 
            function ($template, $context, $args, $source) {
                $tmp = $context->get($args);
                $buffer = '';
                if (!$tmp) {
                    $buffer = $template->render($context);
                }            
                return $buffer;
            }
        ); 

        $this->add(
            'with', 
            function ($template, $context, $args, $source) {
                $tmp = $context->get($args);
                $context->push($tmp);
                $buffer = $template->render($context);
                $context->pop();
                return $buffer;
            }
        );       
        
        //Just for compatibility with ember
        $this->add(
            'bindAttr',
            function ($template, $context, $args, $source) {
                return $args;
            }
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
