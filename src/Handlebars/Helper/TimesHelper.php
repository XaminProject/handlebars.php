<?php

namespace Handlebars\Helper;
use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;


class TimesHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsed_args = $template->parseArguments($args);
        if (count($parsed_args) != 1) {
            throw new \InvalidArgumentException(
                '"repeat" helper expects exactly one argument.'
            );
        }
        $times = intval($context->get($parsed_args[0]));
        if ($times < 0) {
            throw new \InvalidArgumentException(
                'The first argument of "repeat" helper has to be greater than or equal to 0.'
            );
        }
        $string = $template->render($context);
        return str_repeat($string, $times);
    }
}