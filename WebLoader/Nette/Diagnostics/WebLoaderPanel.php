<?php

namespace WebLoader\Nette\Diagnostics;
use Nette\Object;
use Latte\Runtime\Filters;
use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;

/**
 * Debugger panel.
 * @author Adam Klvač
 */
class WebLoaderPanel extends Object implements IBarPanel {
	
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
		'less' => 'Less files'
	);
	
	/** @var string */
	private $root;
	
	/**
	 * @param string
	 */
	public function __construct($appDir = NULL) {
		
		$this->root = $appDir ? realpath(dirname($appDir)) : '';
		Debugger::getBar()->addPanel($this);
		
	}
	
	/**
	 * Registers a compiler.
	 * @param string
	 * @param Compiler
	 * @return self
	 */
	public function addLoader($name, Compiler $compiler) {
		
		$this->compilers[$name] = $compiler;
		return $this;
	
		
	}
	
	/**
	 * Computes the info.
	 * @return void
	 */
	private function compute() {
		
		if($this->size !== NULL) {
			return;
		}
		
		$this->size = 0;
		$this->files = array();
		$this->remoteFiles = array();
		$this->sizes = array();
		
		foreach($this->compilers as $name => $compiler) {
			
			$this->size += $combinedSize = strlen($compiler->getContent());
			
			foreach($compiler->getFileCollection()->getFiles() as $file) {
				
				$file = realpath($file);
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				
				if(!isset($this->files[$extension])) {
					$this->files[$extension] = array();
				}
				
				$this->files[$extension][] = array(
					'name' => substr($file, strlen($this->root)),
					'full' => $file,
					'size' => $size = filesize($file)
				);
				
				if(!isset($this->sizes[$extension])) {
					$this->sizes[$extension] = array(
						'total' => 0,
						'combined' => $combinedSize
					);
				}
				
				$this->sizes[$extension]['total'] += $size;
				
			}
			
			foreach($compiler->getFileCollection()->getRemoteFiles() as $file) {
				
				$url = parse_url($file);
				$extension = strtolower(pathinfo($url['path'], PATHINFO_EXTENSION));
				if(!isset($this->remoteFiles[$extension])) {
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
	private function getTable() {
		
		$table = '';
		
		foreach($this->files as $extension => $files) {
			
			$type = isset(static::$types[$extension]) ? static::$types[$extension] : ('.' . $extension . ' files');
			
			$table .= '<h2>' . $type . ' (' . Filters::bytes($this->sizes[$extension]['total']) . ' total, ' . Filters::bytes($this->sizes[$extension]['combined']) . ' combined)</h2>';
			$table .= '<table style="width: 100%;"><tr><th>File</th><th>Size</th></tr>';
			
			foreach($files as $file) {
				$table .= '<tr><td title="' . $file['full'] . '">' . $file['name'] . '</td><td>' . Filters::bytes($file['size']) . '</td></tr>';
			}
			
			$table .= '</table>';
			
		}
		
		foreach($this->remoteFiles as $extension => $files) {
			
			$type = 'Remote ' . (isset(static::$types[$extension]) ? static::$types[$extension] : ('.' . $extension . ' files'));
			
			$table .= '<h2>' . $type . '</h2>';
			$table .= '<table style="width: 100%;"><tr><th>File URL</th></tr>';
			
			foreach($files as $file) {
				$table .= '<tr><td title="' . $file['full'] . '">' . $file['url'] . '</td></tr>';
			}
			
			$table .= '</table>';
			
		}
		
		return $table;
		
	}
	
	/**
	 * Returns panel content.
	 * @return Html
	 */
	public function getPanel() {
		
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
	 * @return Html
	 */
	public function getTab() {
		
		$this->compute();
		
		return '<span title="WebLoader">'
		. static::getIcon()
		. ' '
		. Filters::bytes($this->size)
		. '</span>';
		
	}
	
	/**
	 * Returns icon as Base64.
	 * @return string
	 */
	private static function getIcon() {
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gYYACogqgixzwAAAwNJREFUOMuVk0toXHUUxn/3f++dRybzyKTJDJlOtTY1jQiSvhTUFsS6EWzBhYhIKIgoWCyotbuKLnTTurCtuAlYFSJRW1ELUQuCqSSVGJpHZ5JmGicxTUzmdZ2Ze+c+5l5XKVpE8bf6PjjncBbfB7fx7gfnb+mXjh5XPM+TNnx46+P8J0Nf/djbf/Tsl3sPveVdGs14PY+87nU/dKRx7M2z5yavTydvn5f/apZurpxIJTuGYql0zzc5nYuTa8ixDtLpzcr+nXfd1xoQr96z+8Ffv/v6wtWNnVvvZeZzJ+7sSrzxzHuj5FEIxsMIAapjEWg0eHFvistjc2Snx7h/d/rp4y88PwggRbqf5aMzh3vy6152oiwzJ/y0dG0iEA4B4NRrJF2TgYO9/F4o8cXwTyws5jDceqRc0KqyWZrk3j2PDYxk9B7/lk7MWJhQRww1oODzq55QFal/Rxt3hFSMRgNZcanWa8xeX/QNnj75rQA4fe6XJ7Z1d7JzaxvxsB9ZkZFlgRBIPlVmTrNQFYGEB0BLSxDbNA8BKGqsV1V9QQoFg3ceSHNEVng7ozFRd1GFhO1YDOc1biytszi2wJN7WrFtm6brbQNQ7EqmaevzdAb7KFT+oC0S5bUdUXI1m/dnitwoaVjlKj/ni5Rnf+OSK3N3WqCVtCaAAFynUbI/u3gF3ahTrFSo6zop1ePaRI7C1BLlqZsUr2ZxqqtUtTK6YWCY1rWNA7i2/qldX+XYyWFKlQrrxSIffz9DIbuKtVyhtrCAaxlYtkUsKlhZK7FWrA4ByNEtu3BMY1Ty3FcKms2jD29nPLvGqU+mkEyTRmEZ1zFwsfCkJomY4MpE3p0fGToAeIq2OA6wIkRfv2U3PxyfnGXgfAbHBtuzkVQF4Q+AL4CQwvww7dCsRfYD7t+SCBBJ9T3V1t4+6MpBLFfF1xpBhOKI1k1IoSTNYNR1iuV9y58/d/kfu2BWV2fqevMUXtPXkYgkdNONdyXbPSUYz0ipzWfmDx88UH15V/5f2xhMbOf/8CfwZlo/jq2yXAAAAABJRU5ErkJggg==" />';
	}

}