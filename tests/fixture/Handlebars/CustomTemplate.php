<?php
namespace Handlebars;

use Handlebars\Template;

class CustomTemplate extends Template
{
    public function render($context)
    {
        return 'Altered Template';
    }
}
