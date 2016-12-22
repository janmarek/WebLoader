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
		$content = $this->getCompiledFileContent($source);
		$sriChecksum = $this->getSriChecksums($content) ?: false;

		return Html::el("script")
			->integrity($sriChecksum)
			->type("text/javascript")
			->src($source);
	}

}