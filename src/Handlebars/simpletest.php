<?php 

require "Template.php";
require "Engine.php";
require "Context.php";
require "Tokenizer.php";
require "Parser.php";

require "Cache.php";
require "Cache/Dummy.php";
require "Loader.php";
require "Loader/StringLoader.php";
require "Helpers.php";


$temp = <<<END_HERE
  <div id="message"> {{!Place holder for message, leave it be in any case}}
    {{#if error}}
    <ul>
      {{#each errors}}
      <li>{{.}}</li>
      {{/each}}
    </ul>
    {{/if}}
  </div>   
  {{#with t}}
    {{{form}}}
  {{/with}}
{{>Test}}   {{! since there is no Test partial and loader is string loder, just Test is printed out}} 
{{{slots.search}}}

{{slots.tags}}

Test

      {{#each t.errors}}
      <li>{{.}}</li>
      {{/each}}
END_HERE;



$contextArray = 
    [
        'error' =>  true,
        'errors' => ['err1', 'err2', 'err3'],
        'slots' => [ 'search' => '<b>search</b>' ,'tags' => '<b>tags</b>'],
        't' => 
            [
                'errors' => ['t.err1', 't.err2'],
                'form' => '<form></form>'
            ]
    ];
$engine = new Handlebars_Engine();

$helper = new Handlebars_Helpers();


$engine->setHelpers($helper);

echo $engine->render($temp, $contextArray);