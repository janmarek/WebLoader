<?php

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader
{
	/** @var string */
	public $generatedFileNamePrefix = "jsloader-";
	
	/** @var string */
	public $generatedFileNameSuffix = ".js";

	/**
	 * Get script element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		return Html::el("script")->type("text/javascript")->src($source);
	}
}