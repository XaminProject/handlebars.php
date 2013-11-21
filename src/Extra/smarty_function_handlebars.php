<?php
/**
 * smarty_function_handlebars
 * HandlebarsをSmartyで
 * 
 * @param mixed $params 
 * @param mixed $smarty 
 * @access public
 * @return void
 */
function smarty_function_handlebars($params, &$smarty)
{
 
    $tplname = $params['tpl'] . ".handlebars";
    $data    = $params['data'];
    $engine = new Handlebars_Engine(
        array(
            'template_class_prefix' => '__MyTemplates_',
            'cache' => new Handlebars_Cache_APC('/tmp/cache/mustache'),
            'loader' => new Handlebars_Loader_FilesystemLoader('/home/gree/xgree/avatar/frontend/gavatar/data/hbtpl'),
            'partials_loader' => new Handlebars_Loader_FilesystemLoader('/home/gree/xgree/avatar/frontend/gavatar/data/hbtpl'),
            'charset' => 'UTF8',
            'strict_callables' => true,
            'escapeArgs' => array(ENT_QUOTES),
        )
    );
    $tpl = $engine->loadTemplate($tplname);
    return $tpl->render($data);
}
