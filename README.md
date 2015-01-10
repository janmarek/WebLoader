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

Configuration in `app/config/config.neon`:

```html
extensions:
	webloader: WebLoader\Nette\Extension

services:
	wlCssFilter: WebLoader\Filter\CssUrlsFilter(%wwwDir%)
	lessFilter: WebLoader\Filter\LessFilter

webloader:
	css:
		default:
			files:
				- style.css
				- {files: ["*.css", "*.less"], from: %appDir%/presenters} # Nette\Utils\Finder support
			fileFilters:
				- @lessFilter
				- @wlCssFilter
	js:
		default:
			remoteFiles:
				- http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js
				- http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js
			files:
				- %appDir%/../libs/nette/nette/client-side/netteForms.js
				- web.js
```

For older versions of Nette, you have to register the extension in `app/bootstrap.php`:

```php
$webloaderExtension = new \WebLoader\Nette\Extension();
$webloaderExtension->install($configurator);
```

Usage in `app/presenters/BasePresenter.php`:

```php
	/** @var \WebLoader\Nette\LoaderFactory @inject */
	public $webLoader;

	/** @return CssLoader */
	protected function createComponentCss()
	{
		return $this->webLoader->createCssLoader('default');
	}

	/** @return JavaScriptLoader */
	protected function createComponentJs()
	{
		return $this->webLoader->createJavaScriptLoader('default');
	}
```


Template:

```html
{control css}
{control js}
```
