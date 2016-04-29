<?php

namespace WebLoader\Nette;

use Nette\Utils\Html;

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader
{

	/**
	 * Get script element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		$el = Html::el("script");
		$this->getCompiler()->isAsync() ? $el = $el->addAttributes(['async' => TRUE]) : NULL;
		$this->getCompiler()->isDefer() ? $el = $el->addAttributes(['defer' => TRUE]) : NULL;
		return $el->type("text/javascript")->src($source);
	}

}