<?php

namespace WebLoader\Nette\Diagnostics;
use Latte\Runtime\Filters;
use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;

/**
 * Debugger panel.
 * @author Adam Klvač
 */
class WebLoaderPanel implements IBarPanel
{
	
	/** @var Compiler[] */
	private $compilers = array();
	
	/** @var int */
	private $size;
	
	/** @var array */
	private $files;
	
	/** @var array */
	private $remoteFiles;
	
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
		
		$this->size = 0;
		$this->files = array();
		$this->remoteFiles = array();
		$this->sizes = array();
		
		foreach ($this->compilers as $name => $compiler) {
			
			$this->size += $combinedSize = strlen($compiler->getContent());
			
			foreach ($compiler->getFileCollection()->getFiles() as $file) {
				
				$file = realpath($file);
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				
				if (!isset($this->files[$extension])) {
					$this->files[$extension] = array();
				}
				
				$this->files[$extension][] = array(
					'name' => substr($file, strlen($this->root)),
					'full' => $file,
					'size' => $size = filesize($file)
				);
				
				if (!isset($this->sizes[$extension])) {
					$this->sizes[$extension] = array(
						'total' => 0,
						'combined' => $combinedSize
					);
				}
				
				$this->sizes[$extension]['total'] += $size;
				
			}
			
			foreach ($compiler->getFileCollection()->getRemoteFiles() as $file) {
				
				$url = parse_url($file);
				$extension = strtolower(pathinfo($url['path'], PATHINFO_EXTENSION));
				
				if (!$extension) {
					$extension = strtolower(trim($url['path'], '/'));
				}

				if (!isset($this->remoteFiles[$extension])) {
					$this->remoteFiles[$extension] = array();
				}
				
				$this->remoteFiles[$extension][] = array(
					'url' => (isset($url['scheme']) ? $url['scheme'] . ':' : '') . '//' . $url['host'] . $url['path'],
					'full' => $file
				);
				
			}
			
		}
	}
	
	/**
	 * Renders loaded files table.
	 * @return string
	 */
	private function getTable()
	{	
		$table = '';
		
		foreach ($this->files as $extension => $files) {
			
			$type = isset(static::$types[$extension]) ? static::$types[$extension] : ('.' . $extension . ' files');
			
			$table .= '<h2>' . $type . ' (' . Filters::bytes($this->sizes[$extension]['total']) . ' total, ' . Filters::bytes($this->sizes[$extension]['combined']) . ' combined)</h2>';
			$table .= '<table style="width: 100%;"><tr><th>File</th><th>Size</th></tr>';
			
			foreach ($files as $file) {
				$table .= '<tr><td title="' . htmlspecialchars($file['full']) . '">' . htmlspecialchars($file['name']) . '</td><td>' . Filters::bytes($file['size']) . '</td></tr>';
			}
			
			$table .= '</table>';
			
		}
		
		foreach ($this->remoteFiles as $extension => $files) {
			
			$type = 'Remote ' . (isset(static::$types[$extension]) ? static::$types[$extension] : ('.' . $extension . ' files'));
			
			$table .= '<h2>' . $type . '</h2>';
			$table .= '<table style="width: 100%;"><tr><th>File URL</th></tr>';
			
			foreach ($files as $file) {
				$table .= '<tr><td title="' . htmlspecialchars($file['full']) . '">' . htmlspecialchars($file['url']) . '</td></tr>';
			}
			
			$table .= '</table>';
			
		}
		
		return $table;
	}
	
	/**
	 * Returns panel content.
	 * @return string
	 */
	public function getPanel()
	{	
		$this->compute();
		
		return $this->size ?
			'<h1>WebLoader</h1>'
			. '<div class="tracy-inner">'
			. $this->getTable()
			. '</div>'
			: '';	
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
		. Filters::bytes($this->size)
		. '</span>';
	}

}
