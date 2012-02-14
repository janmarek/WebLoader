WebLoader
=========

Component for CSS and JS files loading

Author: Jan Marek
Licence: MIT

Example:

	<?php

	// presenter factory in nette

	protected function createComponentCss()
	{
		$files = new WebLoader\FileCollection(WWW_DIR . '/css');
		$files->addFiles(array(
			'style.css',
			WWW_DIR . '/colorbox/colorbox.css',
		));

		$compiler = WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/temp');

		$compiler->addFilter(new WebLoader\Filter\VariablesFilter(array('foo' => 'bar'));
		$compiler->addFilter(function ($code) {
			return cssmin::minify($code, "remove-last-semicolon");
		});

		$control = new WebLoader\Nette\CssLoader($compiler, '/webtemp');
		$control->setMedia('screen');

		return $control;
	}

Template:

	{control css}