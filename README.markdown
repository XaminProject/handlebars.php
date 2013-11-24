#Handlebars.php

---

#### A simple, logic-less, yet powerful templating engine for PHP

---
 
Name: **Handlebars.php**

License: MIT

Version : 2.x.x

Requirements: PHP >= 5.4

---


## About Handlebars.php

Handlebars.php provides the power necessary to let you build semantic templates effectively with no frustration, that keep the view and the code separated like we all know they should be.


Fork of: [Handlebars.php by XaminProject](https://github.com/XaminProject/handlebars.php)

Handlebars.php, is the PHP port of [Handlebars.js](http://handlebarsjs.com/)


---

## Install Handlebars.php


You can just download Handlebars.php as is, or with Composer. 

To install with composer, add the following in the require key in your **composer.json** file

	"voodoophp/handlebars.php": "2.*"

composer.json


	{
	    "name": "myapp/name",
	    "description": "My awesome app name",
	    "require": {
	        "voodoophp/handlebars.php": "2.*"
	    }
	}

-----

## Getting Started

At the minimum, we are required to have an array model and a template string. Alternatively we can have a file containing handlebars (or html, text, etc) expression. 

  

#### Template

Handlebars templates look like regular HTML, with embedded handlebars expressions.

Handlebars HTML-escapes values returned by a {{expression}}.

		<div class="entry">
		  <h1>{{title}}</h1>
		  <div class="body">
		    Hello, my name is {{name}}
		  </div>
		</div>


The string above can be used as is in your PHP file, or be put in a file (ie: */templates/main.tpl*), to be called upon rendering.

#### PHP file

Now the we've created our template file, in a php file (index.php) we'll create the data to passed to the model. The model is a key/value array. 

Below we are going to create the Handlebars object, set the partials loader, and put some data in the model.

**/index.php**

	<?php
		# With composer we can autoload the Handlebars package
		require_once ("./vendor/autoload.php");

		# If not using composer, you can still load it manually.  
		# require 'src/Handlebars/Autoloader.php';
		# Handlebars\Autoloader::register();
		
		use Handlebars\Handlebars;
		use Handlebars\Loader\FilesystemLoader;

		# Set the partials files
		$partialsDir = __DIR__."/templates"
		$partialsLoader = new FilesystemLoader($partialsDir, 
												[
													"extension" => "html"
												]
											);

		# We'll use $handlebars thoughout this the examples, assuming the will be all set this way
		$handlebars = new Handlebars([
						"loader" => $partialsLoader,
						"partials_loader" => $partialsLoader
					]);

		# Will render the model to the templates/main.tpl template
		echo $handlebars->render("main", $model);
	

#### Assign Data

The simplest way to assign data is to create an Array model. The model will contain all the data that will be passed to the template.

	<?php

	$model = [
		"name" => "Mardix",
		"title" => "I'm Title",
        "permalink" => "blog/",
		"foo" => "bar",
		"article" => [
			"title" => "My Article Title"
		],
		"posts" => [
            [
                "title" => "Post #1",
                "id" => 1,
                "content" => "Content"
            ],
            [
                "title" => "Post 2",
                "id" => 2,
                "content" => "Content"
            ]
		]
	];


#### Render Template

Use the method `Handlebars\Handlebars::render($template, $model)` to render you template once everything is created. 

***$template*** : Template can be the name of the file or a string containing the handlebars/html.

***$model*** : Is the array that we will pass into the template

The code below will render the model to the *templates/main.tpl* template

		echo $handlebars->render("main", $model);


Alternatively you can use $handlebars itself without invoking the render method

		echo $handlebars("main", $model);


---

## Expressions

Let's use this simple model for the following examples, assuming everything is already set like above.

	<?php

	$model = [
		"title" => "I'm Title",
                "permalink" => "/blog/",
		"foo" => "bar",
		"article" => [
			"title" => "My Article Title"
		],
		"posts" => [
            [
                "title" => "Post #1",
                "id" => 1,
                "content" => "Content"
            ],
            [
                "title" => "Post 2",
                "id" => 2,
                "content" => "Content"
            ]
		]
	];

Let's work with the template.

Handlebars expressions are the basic unit of a Handlebars template. You can use them alone in a {{mustache}}, pass them to a Handlebars helper, or use them as values in hash arguments.


The simplest Handlebars expression is a simple identifier:
	
	{{title}}

	-> I'm Title

Handlebars nested expressions which are dot-separated paths.

	{{article.title}}

	-> My Article Title

Handlebars nested expressions in an array.

	{{posts.0.title}}

	-> Post #1


Handlebars also allows for name conflict resolution between helpers and data fields via a this reference:

	{{./name}} or {{this/name}} or {{this.name}}


Handlebars expressions with a helper. In this case we're using the upper helper

	{{#upper title}}

	-> I'M TITLE


Nested handlebars paths can also include ../ segments, which evaluate their paths against a parent context.

	{{#each posts}}
		<a href="/posts/{{../permalink}}/{{id}}">{{title}}</a>
		{{content}}
	{{/each}}


Handlebars HTML-escapes values returned by a {{expression}}. If you don't want Handlebars to escape a value, use the "triple-stash", {{{ }}}

	{{{foo}}}


---


## Control Structures

`if/else` and `unless` control structures are implemented as regular Handlebars helpers

### IF/ELSE

You can use the if helper to conditionally render a block. If its argument returns false, null, "" or [] (a "falsy" value), Handlebars will not render the block.

**Example**


	{{#if isActive}}
		This part will be shown if it is active
	{{else}}	
		This part will not show if isActive is true
	{{/if}}


	<?php

	$model = [
		"isActive" => true
	];

	echo $handlebars->render($template, $model)


### UNLESS

You can use the unless helper as the inverse of the if helper. Its block will be rendered if the expression returns a falsy value.


	{{#unless isActive}}
		This part will not show if isActive is true
	{{/unless}}	
			

---
##Iterators: EACH

You can iterate over a list using the built-in each helper. Inside the block, you can use {{this}} or {{.}} to reference the element being iterated over.

**Example**

	<?php
        $model = [
            "genres" => [
                "Hip-Hop",
                "Rap",
                "Techno",
                "Country"
            ],
            "cars" => [
                "category" => "Foreign",
                "count" => 4,
                "list" => [
                    "Toyota",
                    "Kia",
                    "Honda",
                    "Mazda"
                ],
                "category" => "WTF",
                "count" => 1,
                "list" => [
                    "Fiat"
                ],
                "category" => "Luxury",
                "count" => 2,
                "list" => [
                    "Mercedes Benz",
                    "BMW"
                ],
                "category" => "Rich People Shit",
                "count" => 3,
                "list" => [
                    "Ferrari",
                    "Bugatti",
                    "Rolls Royce"
                ]
            ],    
        ];


			<h2>All genres:</h2>
			{{#each genres}}
			    {{.}}
			{{/each}}
			
			
			{{#each cars}}
			    <h3>{{category}}</h3>
			    Total: {{count}}
			    <ul>
			        {{#each list}}
			            {{.}}
			        {{/each}}
			    </ul>
			{{/each}}
		";

		echo $engine->render($template, $model)


### EACH/ELSE

You can optionally provide an {{else}} section which will display only when the list is empty.

		<h2>All genres:</h2>
		{{#each genres}}
		    {{.}}
		{{else}}
			No genres found!
		{{/each}}



### Slice EACH Array[start:end]

The #each helper (php only) also has the ability to slice the data

 * {{#each Array[start:end]}} = starts at start trhough end -1
 * {{#each Array[start:]}} = Starts at start though the rest of the array
 * {{#each Array[:end]}} = Starts at the beginning through end -1
 * {{#each Array[:]}} = A copy of the whole array
 * {{#each Array[-1]}}
 * {{#each Array[-2:]}} = Last two items
 * {{#each Array[:-2]}} = Everything except last two items


		<h2>All genres:</h2>
		{{#each genres[0:10]}}
		    {{.}}
		{{else}}
			No genres found!
		{{/each}}


#### {{@INDEX}} and {{@KEY}}

When looping through items in each, you can optionally reference the current loop index via {{@index}}

	{{#each array}}
	  {{@index}}: {{this}}
	{{/each}} 


	{{#each object}}
	  {{@key}}: {{this}}
	{{/each}}




---

## Change Context: WITH

You can shift the context for a section of a template by using the built-in with block helper.

	<?php
        $model = [
            "genres" => [
                "Hip-Hop",
                "Rap",
                "Techno",
                "Country"
            ],
			"other_genres" => [
	            "genres" => [
	                "Hip-Hop",
	                "Rap",
	                "Techno",
	                "Country"
	            ]
			]
		];
		
		<h2>All genres:</h2>
	    {{#with other_genres}}
	        {{#each genres}}
	            {{.}}
	        {{/each}}
	    {{/with}}

---

## Handlebars Built-in Helpers

### If

		{{#if isActive}}
			This part will be shown if it is active
		{{else}}	
			This part will not show if isActive is true
		{{/if}}

### Unless
		{{#unless isActive}}
			This part will show when isActive is false
		{{/unless}}

### Each
		{{#each genres[0:10]}}
		    {{.}}
		{{else}}
			No genres found!
		{{/each}}

### With

	    {{#with other_genres}}
	        {{#each genres}}
	            {{.}}
	        {{/each}}
	    {{/with}}

---

## Other Helpers

#### For convenience, a few more helpers were added in PHP to facilate some string formatting.

---

### Upper

To format string to uppercase

	{{#upper title}}

### Lower 

To format string to lowercase

	{{#lower title}}


### Capitalize 

To capitalize the first letter
	
	{{#capitalize title}}

### Capitalize_Words 

To capitalize each words in a string

	{{#capitalize_words title}}

### Reverse 

To reverse the order of string

	{{#reverse title}}

### Format_Date

To format date: `{{#format_date date '$format'}}`

	{{#format_date date 'Y-m-d H:i:s'}}


### Inflect 

To singularize or plurialize words based on count `{{#inflect count $singular $plurial}}`

	{{#inflect count '%d book' '%d books'}}


### Truncate 

To truncate a string: `{{#truncate title $length $ellipsis}}`

	{{#truncate title 21 '...'}}


### Default

To use a default value if  the string is empty: `{{#default title $defaultValue}}`

	{{#default title 'No title'}}



### Raw

This helper return handlebars expression as is. The expression will not be parsed

	{{#raw}}
		{{#each cars}}
			{{model}}
		{{/each}}
	{{/raw}}

->

	{{#each cars}}
		{{model}}
	{{/each}}


---

### Template Comments
You can use comments in your handlebars code just as you would in your code. Since there is generally some level of logic, this is a good practice.

	{{!-- only output this author names if an author exists --}}

---

### Partials

Partials are other templates you can include inside of the main template.

To do so:

	{{> my_partial}}

which is a file under /templates/my_partial.html

---

## Writing your own helpers

Block helpers make it possible to define custom iterators and other helpers that can invoke the passed block with a new context.

The following helper will UPPERCASE a string

	$handlebars->addHelper("upper", function($template, $context, $args, $source){
	                        return strtoupper($context->get($args));
	                    });

And now we can use the helper like this:

	{{#upper title}}

---



###contribution


contributions are more than welcome, just don't forget to:

 * add your name to each file that you edit as author

 * use PHP CodeSniffer to check coding style.

 * The documentation was edited by [Mardix](http://github.com/mardix)

 * Extended doc at [Handlebars.js](http://handlebarsjs.com/)


###license


    Copyright (C) 2012-2013 Xamin Project and contributors

    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
