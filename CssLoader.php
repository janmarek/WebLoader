<?php

namespace WebLoader;

use Nette\Web\Html;
use Nette\IComponentContainer;

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
	public function __construct(IComponentContainer $parent = null, $name = null) {
		parent::__construct($parent, $name);
		$this->setGeneratedFileNamePrefix("ccsloader-");
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