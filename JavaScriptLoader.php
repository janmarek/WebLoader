<?php

namespace WebLoader;

use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader {

	/**
	 * Construct
	 * @param IComponentContainer parent
	 * @param string name
	 */
	public function __construct(IContainer $parent = null, $name = null) {
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix("jsloader-");
		$this->setGeneratedFileNameSuffix(".js");
	}

	/**
	 * Get script element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source) {
		if ($this->joinFiles == TRUE) {
			return Html::el("script")->type("text/javascript")->setText("
				var node = document.createElement('script');
				node.type = 'text/javascript';
				node.async = true;
				node.src = '$source';
				document.body.appendChild(node);
			");
		} else {
			return Html::el("script")->type("text/javascript")->src($source);
		}
	}

}