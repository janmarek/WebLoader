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
		$el = Html::el("script")->type("text/javascript")->src($source);

		$nonce = $this->getCompiler()->getNonce();
		if ($nonce)
		{
			$el->nonce($nonce);
		}

		return $el;
	}

}
