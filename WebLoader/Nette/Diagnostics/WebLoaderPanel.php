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
class WebLoaderPanel implements IBarPanel
{
	
	/** @var Compiler[] */
	private $compilers = array();
	
	/** @var array */
	private $size;
	
	/** @var array */
	private $files;
	
	/** @var array */
	private $sizes;
	
	/** @var array */
	private static $types = array(
		'css' => 'CSS files',
		'js' => 'JavaScript files',
		'less' => 'Less files',
		'scss' => 'Sass files',
		'coffee' => 'CoffeeScript files'
	);
	
	/** @var string */
	private $root;
	
	/**
	 * @param string
	 */
	public function __construct($appDir = NULL)
	{
		$this->root = $appDir ? realpath(dirname($appDir)) : '';
		Debugger::getBar()->addPanel($this);
	}
	
	/**
	 * Registers a compiler.
	 * @param string
	 * @param Compiler
	 * @return self
	 */
	public function addLoader($name, Compiler $compiler)
	{	
		$this->compilers[$name] = $compiler;
		return $this;	
	}
	
	/**
	 * Computes the info.
	 * @return void
	 */
	private function compute()
	{
		
		if ($this->size !== NULL) {
			return;
		}
		
		$this->size = array(
			'total' => 0,
			'combined' => 0
		);
		$this->files = array();
		$this->sizes = array();
		
		foreach ($this->compilers as $name => $compiler) {
			
			$this->size['combined'] += $combinedSize = strlen($compiler->getContent());
			$group = lcfirst(substr($name, $name[0] === 'c' ? 3 : 2));
			
			foreach ($compiler->getFileCollection()->getFiles() as $file) {
				
				$file = str_replace('\\', '/', realpath($file)); // woknajz
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

				if (!isset($this->files[$group])) {
					$this->files[$group] = array(
						$extension => array()
					);
				} elseif(!isset($this->files[$group][$extension])) {
					$this->files[$group][$extension] = array();
				}
				
				$this->files[$group][$extension][] = array(
					'name' => substr($file, strlen($this->root)),
					'full' => $file,
					'size' => $size = filesize($file)
				);

				$this->size['total'] += $size;
				
				if (!isset($this->sizes[$group])) {
					$this->sizes[$group] = array(
						$extension => array(
							'total' => 0,
							'combined' => $combinedSize
						),
						'.' => array(
							'total' => 0,
							'combined' => 0
						)
					);
				} elseif(!isset($this->sizes[$group][$extension])) {
					$this->sizes[$group][$extension] = array(
						'total' => 0,
						'combined' => $combinedSize
					);
				}
				
				$this->sizes[$group][$extension]['total'] += $size;
				$this->sizes[$group]['.']['total'] += $size;
				$this->sizes[$group]['.']['combined'] += $combinedSize;

			}
			
		}
	}
	
	/**
	 * Renders loaded files table.
	 * @return string
	 */
	private function getTable()
	{	
		$latte = new Latte\Engine;

		$latte->addFilter('extension', function($extension) {
			return isset(static::$types[$extension]) ? static::$types[$extension] : $extension;
		});

		return $latte->renderToString(__DIR__ . '/WebLoaderPanel.latte', [
			'files' => $this->files,
			'sizes' =>$this->sizes,
			'size' => $this->size
		]);
	}
	
	/**
	 * Returns panel content.
	 * @return string
	 */
	public function getPanel()
	{	
		$this->compute();
		return $this->size ? $this->getTable() : '';
	}

	/**
	 * Returns panel tab.
	 * @return string
	 */
	public function getTab()
	{
		$this->compute();
		
		return '<span title="WebLoader">'
		. '<img src="data:image/png;base64,'
		. base64_encode(file_get_contents(__DIR__ . '/icon.png'))
		. '" /> '
		. Filters::bytes($this->size['combined'])
		. '</span>';
	}

}
