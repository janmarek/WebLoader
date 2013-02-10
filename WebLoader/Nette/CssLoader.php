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

	/** @var string */
	private $title;

	/** @var string */
	private $type = 'text/css';

	/** @var bool */
	private $alternate = FALSE;

	/**
	 * Get media
	 * @return string
	 */
	public function getMedia()
	{
		return $this->media;
	}

	/**
	 * Get type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Is alternate ?
	 * @return bool
	 */
	public function isAlternate()
	{
		return $this->alternate;
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
	 * Set type
	 * @param string $type
	 * @return CssLoader
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set title
	 * @param string $title
	 * @return CssLoader
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Set alternate
	 * @param bool $alternate
	 * @return CssLoader
	 */
	public function setAlternate($alternate)
	{
		$this->alternate = $alternate;
		return $this;
	}

	/**
	 * Get link element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		if ($this->alternate) {
			$alternate = ' alternate';
		} else {
			$alternate = '';
		}

		return Html::el("link")->rel("stylesheet".$alternate)->type($this->type)->media($this->media)->title($this->title)->href($source);
	}

}
