<?php

namespace WebLoader\Nette;

use Nette\Utils\Html;

/**
 * Css loader
 *
 * @author Jan Marek
 * @license MIT
 */
class CssLoader extends WebLoader
{

	/** @var string */
	private $media;

	/**
	 * Get media
	 * @return string
	 */
	public function getMedia()
	{
		return $this->media;
	}

	/**
	 * Set media
	 * @param string $media
	 * @return CssLoader
	 */
	public function setMedia($media)
	{
		$this->media = $media;
		return $this;
	}

	/**
	 * Get link element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		return Html::el("link")->rel("stylesheet")->type("text/css")->media($this->media)->href($source);
	}

}
