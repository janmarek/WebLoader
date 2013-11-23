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
	 * @param string $tempPath
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createCssLoader(Compiler $cssCompiler, $tempPath)
	{
		return new CssLoader($cssCompiler, rtrim($this->httpRequest->url->basePath, '/') . '/' . $tempPath);
	}


	/**
	 * @param Compiler $javaScriptCompiler
	 * @param string $tempPath
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createJavaScriptLoader(Compiler $javaScriptCompiler, $tempPath)
	{
		return new JavaScriptLoader($javaScriptCompiler, rtrim($this->httpRequest->url->basePath, '/') . '/' . $tempPath);
	}

} 
