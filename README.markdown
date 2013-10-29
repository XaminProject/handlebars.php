Handlebars.php
==============


Ethnaでの利用：

Controllerにてロード

    require_once $root . '/lib/handlebars.php/src/Handlebars/Autoloader.php';
    Handlebars_Autoloader::register();

SmartyPlugin:

    src/Extra/smarty_function_handlebars.php
smarty_pluginとしてロードする。
