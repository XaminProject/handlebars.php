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
 * Handlebars base template
 * contain some utility method to get context and helpers
 * 
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @copyright 2012 (c) ParsPooyesh Co
 * @license   GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @version   Release: @package_version@
 * @link      http://xamin.ir
 */

class Handlebars_Template
{
    /**
     * @var Handlebars_Engine
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
     * @param Handlebars_Engine $engine handlebar engine
     * @param array             $tree   Parsed tree
     * @param string            $source Handlebars source
     */
    public function __construct(Handlebars_Engine $engine, $tree, $source)
    {
        $this->handlebars = $engine;
        $this->tree = $tree;
        $this->source = $source;
        array_push($this->_stack, array (0, $this->getTree()));
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
     * @return Handlebars_Engine
     */
    public function getEngine()
    {
        return $this->handlebars;
    }

    /**
     * Render top tree
     *
     * @param mixed $context current context
     *
     * @return string
     */
    public function render($context)
    {
        if (!$context instanceof Handlebars_Context) {
            $context = new Handlebars_Context($context);
        }            
        $topTree = end($this->_stack); //This method never pop a value from stack
        list($index ,$tree) = $topTree;

        $buffer = '';
        while (array_key_exists($index, $tree)) {
            $current = $tree[$index];
            $index++;
            switch ($current[Handlebars_Tokenizer::TYPE]) {
            case Handlebars_Tokenizer::T_SECTION :
                $newStack = isset($current[Handlebars_Tokenizer::NODES]) ? $current[Handlebars_Tokenizer::NODES] : array();
                array_push($this->_stack, array(0, $newStack));
                $buffer .= $this->_section($context, $current);
                array_pop($this->_stack);
                break;
            case Handlebars_Tokenizer::T_COMMENT : 
                $buffer .= '';
                break;
            case Handlebars_Tokenizer::T_PARTIAL:
            case Handlebars_Tokenizer::T_PARTIAL_2:
                $buffer .= $this->_partial($context, $current);
                break;
            case Handlebars_Tokenizer::T_UNESCAPED:
            case Handlebars_Tokenizer::T_UNESCAPED_2:
                $buffer .= $this->_variables($context, $current, false);
                break;
            case Handlebars_Tokenizer::T_ESCAPED:
                $buffer .= $this->_variables($context, $current, true);
                break;
            case Handlebars_Tokenizer::T_TEXT:
                $buffer .= $current[Handlebars_Tokenizer::VALUE];
                break;
            default:
                throw new RuntimeException('Invalid node type : ' . json_encode($current));
            }
        }
        return $buffer;
    }

    /**
     * Process section nodes
     *
     * @param Handlebars_Context $context current context
     * @param array              $current section node data
     *
     * @return string the result
     */ 
    private function _section(Handlebars_Context $context, $current)
    {
        $helpers = $this->handlebars->getHelpers();
        $sectionName = $current[Handlebars_Tokenizer::NAME];
        if ($helpers->has($sectionName)) {
            if (isset($current[Handlebars_Tokenizer::END])) {
                $source = substr(
                    $this->getSource(),
                    $current[Handlebars_Tokenizer::INDEX],
                    $current[Handlebars_Tokenizer::END] - $current[Handlebars_Tokenizer::INDEX]
                );
            } else {
                $source = '';
            }    
            $params = array(
                $this,  //First argument is this template
                $context, //Secound is current context
                $current[Handlebars_Tokenizer::ARGS],  //Arguments
                $source
                );
            return call_user_func_array($helpers->$sectionName, $params);
        } else {
            throw new RuntimeException($sectionName . ' is not registered as a helper');
        }                    
    }

    /**
     * Process partial section
     *
     * @param Handlebars_Context $context current context
     * @param array              $current section node data
     *
     * @return string the result
     */
    private function _partial($context, $current)
    {
        $partial = $this->handlebars->loadPartial($current[Handlebars_Tokenizer::NAME]);
        return $partial->render($context);
    }

    /**
     * Process partial section
     *
     * @param Handlebars_Context $context current context
     * @param array              $current section node data
     * @param boolean            $escaped escape result or not
     *
     * @return string the result
     */
    private function _variables($context, $current, $escaped)
    {
        $value = $context->get($current[Handlebars_Tokenizer::NAME]);
        if ($escaped) {
            $args = $this->handlebars->getEscapeArgs();
            array_unshift($args, $value);
            $value = call_user_func_array($this->handlebars->getEscape(), array_values($args));
        }
        return $value;
    }
    
    
}