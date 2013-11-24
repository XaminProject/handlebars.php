<?php
/**
 * This file is part of Handlebars-php
 * Base on mustache-php https://github.com/bobthecow/mustache.php
 *
 * Handlebars helpers
 *
 * a collection of helper function. normally a function like
 * function ($sender, $name, $arguments) $arguments is unscaped arguments and
 * is a string, not array
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

use DateTime;
use InvalidArgumentException;
use Traversable;

class Helpers
{
    /**
     * @var array array of helpers
     */
    protected $helpers = [];
    protected $builtinHelpers = [
        "if", 
        "each",
        "with",
        "unless",
        "bindAttr",
        "upper",
        "lower",
        "capitalize",
        "capitalize_words",
        "reverse",
        "format_date",
        "inflect",
        "default",
        "truncate",
        "raw"
    ];

    /**
     * Create new helper container class
     *
     * @param array      $helpers  array of name=>$value helpers
     * @throws \InvalidArgumentException when $helpers is not an array
     * (or traversable) or helper is not a callable
     */
    public function __construct($helpers = null)
    {
        foreach($this->builtinHelpers as $helper) {
            $helperName = $this->underscoreToCamelCase($helper);
            $this->add($helper, [$this, "helper{$helperName}"]);
        }

        if ($helpers != null) {
            if (!is_array($helpers) && !$helpers instanceof Traversable) {
                throw new InvalidArgumentException(
                    'HelperCollection constructor expects an array of helpers'
                );
            }
            foreach ($helpers as $name => $helper) {
                $this->add($name, $helper);
            }
        }
    }

    /**
     * Add a new helper to helpers
     *
     * @param string   $name   helper name
     * @param callable $helper a function as a helper
     *
     * @throws \InvalidArgumentException if $helper is not a callable
     * @return void
     */
    public function add($name, $helper)
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
     * @throws \InvalidArgumentException if $name is not available
     * @return callable helper function
     */
    public function __get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException('Unknown helper :' . $name);
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
     */
    public function __set($name, $helper)
    {
        $this->add($name, $helper);
    }


    /**
     * Unset a helper
     *
     * @param string $name helper name to remove
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
     * @throws \InvalidArgumentException if the requested helper is not present.
     * @return void
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
        $this->helpers = [];
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

    /**
     * Create handler for the 'if' helper.
     *
     * Needed for compatibility with PHP 5.2 since it doesn't support anonymous
     * functions.
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperIf($template, $context, $args, $source)
    {
        $tmp = $context->get($args);
        if ($tmp) {            
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard();
            return $buffer;
        } else {
            return $this->renderElse($template, $context);
        }
    }


    /**
     * Create handler for the 'each' helper.
     * example {{#each people}} {{name}} {{/each}}
     * example with slice: {{#each people[0:10]}} {{name}} {{/each}}
     * example with else
     *  {{#each Array}}
     *        {{.}}
     *  {{else}}
     *      Nothing found
     *  {{/each}}
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperEach($template, $context, $args, $source)
    {
        list($keyname, $slice_start, $slice_end) = $this->extractSlice($args);
        $tmp = $context->get($keyname);
    
        if (is_array($tmp) || $tmp instanceof Traversable) {
            $tmp = array_slice($tmp, $slice_start, $slice_end);
            $buffer = '';
            $islist = (array_keys($tmp) == range(0, count($tmp) - 1));
            
            if (is_array($tmp) && ! count($tmp)) {
                return $this->renderElse($template, $context);
            } else {
                foreach ($tmp as $key => $var) {                
                    $tpl = clone $template;
                    if ($islist) {
                        $context->pushIndex($key);
                    } else {
                        $context->pushKey($key);
                    }
                    $context->push($var);
                    $tpl->setStopToken('else');
                    $buffer .= $tpl->render($context);
                    $context->pop();
                    if ($islist) {
                        $context->popIndex();
                    } else {
                        $context->popKey();
                    }
                }
                return $buffer;
            }
        } else {
            return $this->renderElse($template, $context);
        }
    }
    
    /**
     * Applying the DRY principle here.
     * This method help us render {{else}} portion of a block
     * @param \Handlebars\Template $template
     * @param \Handlebars\Context $context
     * @return string
     */
    private function renderElse($template, $context)
    {
        $template->setStopToken('else');
        $template->discard();
        $template->setStopToken(false);
        return $template->render($context);        
    }
    
    
    /**
     * Create handler for the 'unless' helper.
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperUnless($template, $context, $args, $source)
    {
        $tmp = $context->get($args);
        $buffer = '';
        if (!$tmp) {
            $buffer = $template->render($context);
        }
        return $buffer;
    }

    /**
     * Create handler for the 'with' helper.
     * Needed for compatibility with PHP 5.2 since it doesn't support anonymous
     * functions.
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperWith($template, $context, $args, $source)
    {
        $tmp = $context->get($args);
        $context->push($tmp);
        $buffer = $template->render($context);
        $context->pop();

        return $buffer;
    }

    /**
     * Create handler for the 'bindAttr' helper.
     * Needed for compatibility with PHP 5.2 since it doesn't support anonymous
     * functions.
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperBindAttr($template, $context, $args, $source)
    {
        return $args;
    }
    
    /**
     * To uppercase string
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperUpper($template, $context, $args, $source)
    {
        return strtoupper($context->get($args));
    }

    /**
     * To lowercase string
     * 
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperLower($template, $context, $args, $source)
    {
        return strtolower($context->get($args));
    }

    /**
     * to capitalize first letter
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperCapitalize($template, $context, $args, $source)
    {
        return ucfirst($context->get($args));
    }

    /**
     * To capitalize first letter in each word
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperCapitalizeWords($template, $context, $args, $source)
    {
        return ucwords($context->get($args));
    }

    /**
     * To reverse a string
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperReverse($template, $context, $args, $source)
    {
        return strrev($context->get($args));
    } 
    
    /**
     * Format a date
     * {{#format_date date 'Y-m-d @h:i:s'}}
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperFormatDate($template, $context, $args, $source)
    {
        preg_match("/(.*?)\s+(?:(?:\"|\')(.*?)(?:\"|\'))/", $args, $m);
        $keyname = $m[1];
        $format = $m[2];

        $date = $context->get($keyname);
        if ($format) {
            $dt = new DateTime;
            if (is_numeric($date)) {
                $dt = (new DateTime)->setTimestamp($date);
            } else {
                $dt = new DateTime($date);
            }
            return $dt->format($format);
        } else {
            return $date;
        }
    }  
    
    /**
     * {{inflect count 'album' 'albums'}}
     * {{inflect count '%d album' '%d albums'}}
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperInflect($template, $context, $args, $source)
    {
        preg_match("/(.*?)\s+(?:(?:\"|\')(.*?)(?:\"|\'))\s+(?:(?:\"|\')(.*?)(?:\"|\'))/", $args, $m);
        $keyname = $m[1];
        $singular = $m[2];
        $plurial = $m[3];
        $value = $context->get($keyname);
        $inflect = ($value <= 1) ? $singular : $plurial;
        return sprintf($inflect, $value);
    } 
    
   /**
     * Provide a default fallback
     * {{default title "No title available"}} 
     * 
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperDefault($template, $context, $args, $source)
    {
        preg_match("/(.*?)\s+(?:(?:\"|\')(.*?)(?:\"|\'))/", trim($args), $m);
        $keyname = $m[1];
        $default = $m[2];        
        $value = $context->get($keyname);
        return ($value) ?: $default;
    }
    
   /**
     * Truncate a string to a length, and append and ellipsis if provided
     * {{#truncate content 5 "..."}} 
     * 
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return string
     */    
    public function helperTruncate($template, $context, $args, $source)
    {
        preg_match("/(.*?)\s+(.*?)\s+(?:(?:\"|\')(.*?)(?:\"|\'))/", trim($args), $m);
        $keyname = $m[1];
        $limit = $m[2];
        $ellipsis = $m[3];
        $value = substr($context->get($keyname), 0, $limit);
        if ($ellipsis) {
            $value .= $ellipsis;
        }
        return $value;
    }    
    
    /**
     * {{#raw}} {{/raw}}
     *
     * @param \Handlebars\Template $template template that is being rendered
     * @param \Handlebars\Context  $context  context object
     * @param array                $args     passed arguments to helper
     * @param string               $source   part of template that is wrapped
     *                                       within helper
     *
     * @return mixed
     */
    public function helperRaw($template, $context, $args, $source)
    {
        return $source;
    }
    
    /**
     * Change underscore helper name to CamelCase
     * 
     * @param string $string
     * @return string
     */
    private function underscoreToCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    } 
    
    /**
     * slice
     * Allow to split the data that will be returned
     * #loop[start:end] => starts at start trhough end -1
     * #loop[start:] = Starts at start though the rest of the array
     * #loop[:end] = Starts at the beginning through end -1
     * #loop[:] = A copy of the whole array
     * 
     * #loop[-1]
     * #loop[-2:] = Last two items
     * #loop[:-2] = Everything except last two items
     * 
     * @param string $string
     * @return Array [tag_name, slice_start, slice_end]
     */    
    private function extractSlice($string) 
    {
        preg_match("/^([\w\._\-]+)(?:\[([\-0-9]*?:[\-0-9]*?)\])?/i", $string, $m);  
        $slice_start = $slice_end = null;
        if (isset($m[2])) {
            list($slice_start, $slice_end) = explode(":", $m[2]);
            $slice_start = (int) $slice_start;
            $slice_end = $slice_end ? (int) $slice_end : null;
        } 
        return [$m[1], $slice_start, $slice_end];
    }     
}
