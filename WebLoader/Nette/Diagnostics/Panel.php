<?php

namespace WebLoader\Nette\Diagnostics;

use Latte\Runtime\Filters;
use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;
use Latte;



/**
 * Debugger panel.
 * @author Adam Klvač
 */
class Panel implements IBarPanel
{

	/** @var array */
	public static $types = array(
		'css' => 'CSS files',
		'js' => 'JavaScript files',
		'less' => 'Less files',
		'scss' => 'Sass files',
		'coffee' => 'CoffeeScript files'
	);

	/** @var Compiler[] */
	private $compilers = array();

	/** @var array */
	private $size;

	/** @var array */
	private $files;

	/** @var array */
	private $sizes;

	/** @var string */
	private $root;

	/**
	 * @param string
	 */
	public function __construct($appDir = NULL)
	{
		$this->root = $appDir ? str_replace('\\', DIRECTORY_SEPARATOR, realpath(dirname($appDir))) : '';
		Debugger::getBar()->addPanel($this);
	}

	/**
	 * Registers a compiler.
	 *
	 * @param string $name
	 * @param Compiler $compiler
	 * @return Panel
	 */
	public function addLoader($name, Compiler $compiler)
	{
		$this->compilers[$name] = $compiler;
		return $this;
	}

	/**
	 * Computes the info.
	 * @return array
	 */
	private function compute()
	{
		if ($this->size !== NULL) {
			return $this->size;
		}

		$size = array(
			'original' => 0,
			'combined' => 0
		);
		$this->files = $this->sizes = array();

		foreach ($this->compilers as $name => $compiler) {
			$size['combined'] += $compilerCombinedSize = strlen($compiler->getContent());
			$group = lcfirst(substr($name, $name[0] === 'c' ? 3 : 2));

			if (!isset($this->files[$group])) {
				$this->files[$group] = array();
			}
			if (!isset($this->sizes[$group])) {
				$this->sizes[$group] = array('.' => array('original' => 0, 'combined' => 0));
			}

			foreach ($compiler->getFileCollection()->getFiles() as $file) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$file = str_replace('\\', DIRECTORY_SEPARATOR, realpath($file));

				if (!isset($this->files[$group][$extension])) {
					$this->files[$group][$extension] = array();
				}
				if (!isset($this->sizes[$group][$extension])) {
					$this->sizes[$group][$extension] = array('original' => 0);
				}

				$this->files[$group][$extension][] = array(
					'name' => substr($file, strlen($this->root) + 1),
					'full' => $file,
					'size' => $fileSize = filesize($file)
				);

				$size['original'] += $fileSize;
				$this->sizes[$group][$extension]['original'] += $fileSize;
				$this->sizes[$group]['.']['original'] += $fileSize;
			}

			$this->sizes[$group]['.']['combined'] += $compilerCombinedSize;
		}

		return $this->size = $size + array('ratio' => ($size['combined'] / $size['original']) * 100);
	}

	/**
	 * Renders loaded files table.
	 * @return string
	 */
	private function getTable()
	{
		$latte = new Latte\Engine;

		$latte->addFilter('extension', function($extension) {
			return isset(Panel::$types[$extension]) ? Panel::$types[$extension] : $extension;
		});

		return $latte->renderToString(__DIR__ . '/panel.latte', array(
			'files' => $this->files,
			'sizes' => $this->sizes,
			'size' => $this->size
		));
	}

	/**
	 * Returns panel content.
	 * @return string
	 */
	public function getPanel()
	{
		return $this->compute() ? $this->getTable() : '';
	}

	/**
	 * Returns panel tab.
	 * @return string
	 */
	public function getTab()
	{
		$this->compute();

		return '<span title="WebLoader">'
			. '<img src="data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/icon.png')) . '" /> '
			. Filters::bytes($this->size['combined'])
		. '</span>';
	}

}
