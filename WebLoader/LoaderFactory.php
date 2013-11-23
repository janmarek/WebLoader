<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 23.11.13
 * Time: 14:46
 */

namespace WebLoader;


use Nette\Http\Request;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;

class LoaderFactory
{

	/** @var \Nette\Http\Request */
	private $httpRequest;

	/**
	 * @param Request $httpRequest
	 */
	public function __construct(Request $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}


	/**
	 * @param Compiler $cssCompiler
	 * @param $tempDir
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createCssLoader(Compiler $cssCompiler, $tempDir)
	{
		return new CssLoader($cssCompiler, rtrim($this->httpRequest->url->basePath, '/') . '/' . $tempDir);
	}


	/**
	 * @param Compiler $javaScriptCompiler
	 * @param $tempDir
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createJavaScriptLoader(Compiler $javaScriptCompiler, $tempDir)
	{
		return new JavaScriptLoader($javaScriptCompiler, rtrim($this->httpRequest->url->basePath, '/') . '/' . $tempDir);
	}

} 
