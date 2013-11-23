WebLoader [![Build Status](https://secure.travis-ci.org/janmarek/WebLoader.png?branch=master)](http://travis-ci.org/janmarek/WebLoader)
=======================

Component for CSS and JS files loading

Author: Jan Marek
Licence: MIT

Example
-------

Control factory in Nette presenter:

```php
<?php

protected function createComponentCss()
{
	$files = new WebLoader\FileCollection(WWW_DIR . '/css');
	$files->addFiles(array(
		'style.css',
		WWW_DIR . '/colorbox/colorbox.css',
	));

	$compiler = WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/temp');

	$compiler->addFilter(new WebLoader\Filter\VariablesFilter(array('foo' => 'bar')));
	$compiler->addFilter(function ($code) {
		return cssmin::minify($code, "remove-last-semicolon");
	});

	$control = new WebLoader\Nette\CssLoader($compiler, '/webtemp');
	$control->setMedia('screen');

	return $control;
}
```

Template:

```html
{control css}
```

Example with Nette Framework extension used
-------------------------------------------


Configuration in config.neon:

```html
extensions:
    webloader: WebLoader\Nette\Extension

services:
	wlCssFilter: WebLoader\Filter\CssUrlsFilter(%wwwDir%)
	lessFilter: WebLoader\Filter\LessFilter

    cssLoader: @webloader.cssDefaultLoader
    jsLoader: @webloader.jsDefaultLoader

webloader:
	css:
		default:
			files:
				- style.css
				- {files: ["*.css", "*.less"], from: %appDir%/presenters} # Nette\Utils\Finder support
			filters:
				- @wlCssFilter
			fileFilters:
				- @lessFilter
	js:
		default:
			remoteFiles:
				- http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js
				- http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js
			files:
				- %appDir%/../libs/nette/nette/client-side/forms/netteForms.js
				- web.js
```

BasePresenter.php:

```php
    /** @var CssLoader */
    private $cssLoader;

    /** @var JavaScriptLoader */
    private $jsLoader;


    /**
     * @param CssLoader $cssLoader
     * @param JavaScriptLoader $jsLoader
     */
    public function injectWebloaders(CssLoader $cssLoader, JavaScriptLoader $jsLoader)
    {
        $this->cssLoader = $cssLoader;
        $this->jsLoader = $jsLoader;
    }

    /**
     * @return CssLoader
     */
    public function createComponentCss()
    {
        return $this->cssLoader;
    }

    /**
     * @return JavaScriptLoader
     */
    public function createComponentJs()
    {
        return $this->jsLoader;
    }
```
