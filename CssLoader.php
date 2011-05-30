<?php

namespace WebLoader;

use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;

/**
 * Css loader
 *
 * @author Jan Marek
 * @license MIT
 */
class CssLoader extends WebLoader {

	/** @var string */
	private $media;


	/**
	 * Construct
	 * @param IComponentContainer parent
	 * @param string name
	 */
	public function __construct(IContainer $parent = null, $name = null) {
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix("cssloader-");
		$this->setGeneratedFileNameSuffix(".css");
		$this->fileFilters[] = new CssUrlsFilter;
	}


	/**
	 * Get media
	 * @return string
	 */
	public function getMedia() {
		return $this->media;
	}


	/**
	 * Set media
	 * @param string media
	 * @return CssLoader
	 */
	public function setMedia($media) {
		$this->media = $media;
		return $this;
	}


	/**
	 * Get link element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source) {
		return Html::el("link")
			->rel("stylesheet")
			->type("text/css")
			->media($this->media)
			->href($source);
	}

}
