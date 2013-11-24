<?php
/**
 * This file is part of Handlebars-php
 *
 * Handlebars string
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

class String
{
    private $string = "";

    /**
     * Create new string
     *
     * @param string $string input source
     */
    public function __construct($string)
    {
        $this->setString($string);
    }

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Create new string
     *
     * @param string $string input source
     *
     * @return void
     */
    public function setString($string)
    {
        $this->string = $string;
    }

}