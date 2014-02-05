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
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Chris Gray <chris.w.gray@gmail.com>
 * @author    Dmitriy Simushev <simushevds@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @copyright 2013 (c) Behrooz Shabani
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

namespace Handlebars;

/**
 * Handlebars base template
 * contain some utility method to get context and helpers
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */

class Template
{
    /**
     * @var Handlebars
     */
    protected $handlebars;


    protected $tree = array();

    protected $source = '';

    /**
     * @var array Run stack
     */
    private $_stack = array();

    /**
     * Handlebars template constructor
     *
     * @param Handlebars $engine handlebar engine
     * @param array      $tree   Parsed tree
     * @param string     $source Handlebars source
     */
    public function __construct(Handlebars $engine, $tree, $source)
    {
        $this->handlebars = $engine;
        $this->tree = $tree;
        $this->source = $source;
        array_push($this->_stack, array(0, $this->getTree(), false));
    }

    /**
     * Get current tree
     *
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Get current source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get current engine associated with this object
     *
     * @return Handlebars
     */
    public function getEngine()
    {
        return $this->handlebars;
    }

    /**
     * set stop token for render and discard method
     *
     * @param string $token token to set as stop token or false to remove
     *
     * @return void
     */

    public function setStopToken($token)
    {
        $topStack = array_pop($this->_stack);
        $topStack[2] = $token;
        array_push($this->_stack, $topStack);
    }

    /**
     * get current stop token
     *
     * @return string|bool
     */

    public function getStopToken()
    {
        $topStack = end($this->_stack);

        return $topStack[2];
    }

    /**
     * Render top tree
     *
     * @param mixed $context current context
     *
     * @throws \RuntimeException
     * @return string
     */
    public function render($context)
    {
        if (!$context instanceof Context) {
            $context = new Context($context);
        }
        $topTree = end($this->_stack); // never pop a value from stack
        list($index, $tree, $stop) = $topTree;

        $buffer = '';
        while (array_key_exists($index, $tree)) {
            $current = $tree[$index];
            $index++;
            //if the section is exactly like waitFor
            if (is_string($stop)
                && $current[Tokenizer::TYPE] == Tokenizer::T_ESCAPED
                && $current[Tokenizer::NAME] === $stop
            ) {
                break;
            }
            switch ($current[Tokenizer::TYPE]) {
            case Tokenizer::T_SECTION :
                $newStack = isset($current[Tokenizer::NODES])
                    ? $current[Tokenizer::NODES] : array();
                array_push($this->_stack, array(0, $newStack, false));
                $buffer .= $this->_section($context, $current);
                array_pop($this->_stack);
                break;
            case Tokenizer::T_INVERTED :
                $newStack = isset($current[Tokenizer::NODES]) ?
                    $current[Tokenizer::NODES] : array();
                array_push($this->_stack, array(0, $newStack, false));
                $buffer .= $this->_inverted($context, $current);
                array_pop($this->_stack);
                break;
            case Tokenizer::T_COMMENT :
                $buffer .= '';
                break;
            case Tokenizer::T_PARTIAL:
            case Tokenizer::T_PARTIAL_2:
                $buffer .= $this->_partial($context, $current);
                break;
            case Tokenizer::T_UNESCAPED:
            case Tokenizer::T_UNESCAPED_2:
                $buffer .= $this->_get($context, $current, false);
                break;
            case Tokenizer::T_ESCAPED:

                $buffer .= $this->_get($context, $current, true);
                break;
            case Tokenizer::T_TEXT:
                $buffer .= $current[Tokenizer::VALUE];
                break;
            default:
                throw new \RuntimeException(
                    'Invalid node type : ' . json_encode($current)
                );
            }
        }
        if ($stop) {
            //Ok break here, the helper should be aware of this.
            $newStack = array_pop($this->_stack);
            $newStack[0] = $index;
            $newStack[2] = false; //No stop token from now on
            array_push($this->_stack, $newStack);
        }

        return $buffer;
    }

    /**
     * Discard top tree
     *
     * @return string
     */
    public function discard()
    {
        $topTree = end($this->_stack); //This method never pop a value from stack
        list($index, $tree, $stop) = $topTree;
        while (array_key_exists($index, $tree)) {
            $current = $tree[$index];
            $index++;
            //if the section is exactly like waitFor
            if (is_string($stop)
                && $current[Tokenizer::TYPE] == Tokenizer::T_ESCAPED
                && $current[Tokenizer::NAME] === $stop
            ) {
                break;
            }
        }
        if ($stop) {
            //Ok break here, the helper should be aware of this.
            $newStack = array_pop($this->_stack);
            $newStack[0] = $index;
            $newStack[2] = false;
            array_push($this->_stack, $newStack);
        }

        return '';
    }

    /**
     * Rewind top tree index to the first element
     *
     * @return void
     */
    public function rewind()
    {
        $topStack = array_pop($this->_stack);
        $topStack[0] = 0;
        array_push($this->_stack, $topStack);
    }

    /**
     * Process section nodes
     *
     * @param Context $context current context
     * @param array   $current section node data
     *
     * @throws \RuntimeException
     * @return string the result
     */
    private function _section(Context $context, $current)
    {
        $helpers = $this->handlebars->getHelpers();
        $sectionName = $current[Tokenizer::NAME];
        if ($helpers->has($sectionName)) {
            if (isset($current[Tokenizer::END])) {
                $source = substr(
                    $this->getSource(),
                    $current[Tokenizer::INDEX],
                    $current[Tokenizer::END] - $current[Tokenizer::INDEX]
                );
            } else {
                $source = '';
            }
            $params = array(
                $this, //First argument is this template
                $context, //Second is current context
                $current[Tokenizer::ARGS], //Arguments
                $source
            );

            $return = call_user_func_array($helpers->$sectionName, $params);
            if ($return instanceof String) {
                return $this->handlebars->loadString($return)->render($context);
            } else {
                return $return;
            }
        } elseif (trim($current[Tokenizer::ARGS]) == '') {
            // fallback to mustache style each/with/for just if there is
            // no argument at all.
            try {
                $sectionVar = $context->get($sectionName, true);
            } catch (\InvalidArgumentException $e) {
                throw new \RuntimeException(
                    $sectionName . ' is not registered as a helper'
                );
            }
            $buffer = '';
            if (is_array($sectionVar) || $sectionVar instanceof \Traversable) {
                foreach ($sectionVar as $index => $d) {
                    $context->pushIndex($index);
                    $context->push($d);
                    $buffer .= $this->render($context);
                    $context->pop();
                    $context->popIndex();
                }
            } elseif (is_object($sectionVar)) {
                //Act like with
                $context->push($sectionVar);
                $buffer = $this->render($context);
                $context->pop();
            } elseif ($sectionVar) {
                $buffer = $this->render($context);
            }

            return $buffer;
        } else {
            throw new \RuntimeException(
                $sectionName . ' is not registered as a helper'
            );
        }
    }

    /**
     * Process inverted section
     *
     * @param Context $context current context
     * @param array   $current section node data
     *
     * @return string the result
     */
    private function _inverted(Context $context, $current)
    {
        $sectionName = $current[Tokenizer::NAME];
        $data = $context->get($sectionName);
        if (!$data) {
            return $this->render($context);
        } else {
            //No need to discard here, since it has no else
            return '';
        }
    }

    /**
     * Process partial section
     *
     * @param Context $context current context
     * @param array   $current section node data
     *
     * @return string the result
     */
    private function _partial(Context $context, $current)
    {
        $partial = $this->handlebars->loadPartial($current[Tokenizer::NAME]);

        if ($current[Tokenizer::ARGS]) {
            $context = $context->get($current[Tokenizer::ARGS]);
        }

        return $partial->render($context);
    }


    /**
     * Check if there is a helper with this variable name available or not.
     *
     * @param array $current current token
     *
     * @return boolean
     */
    private function _isSection($current)
    {
        $helpers = $this->getEngine()->getHelpers();
        // Tokenizer doesn't process the args -if any- so be aware of that
        $name = explode(' ', $current[Tokenizer::NAME], 2);
        return $helpers->has(reset($name));
    }

    /**
     * get replacing value of a tag
     *
     * will process the tag as section, if a helper with the same name could be
     * found, so {{helper arg}} can be used instead of {{#helper arg}}.
     *
     * @param Context $context current context
     * @param array   $current section node data
     * @param boolean $escaped escape result or not
     *
     * @return string the string to be replaced with the tag
     */
    private function _get(Context $context, $current, $escaped)
    {
        if ($this->_isSection($current)) {
            return $this->_getSection($context, $current, $escaped);
        } else {
            return $this->_getVariable($context, $current, $escaped);
        }
    }

    /**
     * Process section
     *
     * @param Context $context current context
     * @param array   $current section node data
     * @param boolean $escaped escape result or not
     *
     * @return string the result
     */
    private function _getSection(Context $context, $current, $escaped)
    {
        $args = explode(' ', $current[Tokenizer::NAME], 2);
        $name = array_shift($args);
        $current[Tokenizer::NAME] = $name;
        $current[Tokenizer::ARGS] = implode(' ', $args);
        $result = $this->_section($context, $current);

        if ($escaped && !($result instanceof SafeString)) {
            $escape_args = $this->handlebars->getEscapeArgs();
            array_unshift($escape_args, $result);
            $result = call_user_func_array(
                $this->handlebars->getEscape(),
                array_values($escape_args)
            );
        }

        return $result;
    }

    /**
     * Process variable
     *
     * @param Context $context current context
     * @param array   $current section node data
     * @param boolean $escaped escape result or not
     *
     * @return string the result
     */
    private function _getVariable(Context $context, $current, $escaped)
    {
        $name = $current[Tokenizer::NAME];
        $value = $context->get($name);
        if ($escaped) {
            $args = $this->handlebars->getEscapeArgs();
            array_unshift($args, $value);
            $value = call_user_func_array(
                $this->handlebars->getEscape(),
                array_values($args)
            );
        }

        return $value;
    }
    
    /**
     * Break an argument string into an array of strings
     *
     * @param string $string Argument String as passed to a helper
     *
     * @return array the argument list as an array
     */
    public function parseArguments($string)
    {
        $args = array();
        preg_match_all('#(?:[^\'"\[\]\s]|\[.+?\])+|(?<!\\\\)("|\')(?:[^\\\\]|\\\\.)*?\1|\S+#s', $string, $args);
        $args =  isset($args[0])?$args[0]:array();
        
        for ($x=0, $argc = count($args); $x<$argc;$x++) {
            // check to see if argument is a quoted string literal
            if ($args[$x][0] == "'" || $args[$x][0] == '"') {
                if ($args[$x][0] === substr($args[$x], -1)) {
                    // remove enclosing quotes and unescape
                    $args[$x] = new \Handlebars\String(stripcslashes(substr($args[$x], 1, strlen($args[$x]) -2)));
                } else {
                    throw new \RuntimeException("Malformed string: ".$args);
                }
            }
            
        }
        return $args;
    }
}
