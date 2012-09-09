<?php

namespace WebLoader\Filter;

/**
 * JavaScript & CoffeeScript snockets filter
 *
 * @link https://github.com/TrevorBurnham/snockets
 *
 * @author Patrik Votoček
 * @license MIT
 */
class SnocketsFilter
{
	/** @var CoffeeScriptCompiler */
	private $coffee;

	/**
	 * @param CoffeeScriptCompiler
	 */
	public function setCoffee(CoffeeScriptCompiler $coffee)
	{
		$this->coffee = $coffee;
	}

	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	private function processJavaScriptFile($s, $baseDir)
	{
		$callback = function($input) use($baseDir) {
			$sanitizer = PHP_EOL . ';;;' . PHP_EOL;
			$req = '//= require ';
			$reqTree = '//= require_tree ';
			if (strncmp($input[0], $reqTree, strlen($reqTree)) === 0) { // require_tree
				$path = $baseDir . '/' . substr($input[0], strlen($reqTree));
				if (file_exists($path)) {
					$files = "";
					$iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
					foreach ($iterator as $item) {
						if ($item->isFile() && strlen('.js') === 0 || substr($item, -strlen('.js')) === '.js') {
							$files .= file_get_contents($item) . $sanitizer;
						}
					}
					return $files;
				}
				return "/** path '$path' not found */";
			} elseif (strncmp($input[0], $req, strlen($req)) === 0
			 && strlen('.js') === 0 || substr($input[0], -strlen('.js')) === '.js') { // require
			 	$path = $baseDir . '/' . substr($input[0], strlen($req));
				if (file_exists($path)) {
					return file_get_contents($path) . $sanitizer;
				}
				return "/** file '$path' not found */";
			}
			return $input[0];
		};

		$pattern = '~^//=\srequire(_tree)?\s[a-z0-9-_/.]+(\.js)?~im';
		return preg_replace_callback($pattern, $callback, $s);
	}

	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	private function processCoffeeScriptFile($s, $baseDir)
	{
		$coffee = $this->coffee;
		$callback = function($input) use($baseDir, $coffee) {
			$sanitizer = PHP_EOL . ';' . PHP_EOL;
			$req = '#= require ';
			$reqTree = '#= require_tree ';
			if (strncmp($input[0], $reqTree, strlen($reqTree)) === 0) { // require_tree
				$path = $baseDir . '/' . substr($input[0], strlen($reqTree));
				if (file_exists($path)) {
					$files = "";
					$iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
					foreach ($iterator as $item) {
						if ($item->isFile() && strlen('.js') === 0 || substr($item, -strlen('.js')) === '.js') {
							$files .= file_get_contents($item) . $sanitizer;
						} elseif ($item->isFile() && strlen('.coffee') === 0 || substr($item, -strlen('.coffee')) === '.coffee') {
							$files .= $coffee->compile(file_get_contents($item)) . $sanitizer;
						}
					}
					return '`'.$files.'`';
				}
				return "### path '$path' not found ###";
			} elseif (strncmp($input[0], $req, strlen($req)) === 0
			 && strlen('.js') === 0 || substr($input[0], -strlen('.js')) === '.js') { // require
			 	$path = $baseDir . '/' . substr($input[0], strlen($req));
				if (file_exists($path)) {
					return '`'.file_get_contents($path).'`';
				}
				return "### file '$path' not found ###";
			} elseif (strncmp($input[0], $req, strlen($req)) === 0
			 && strlen('.coffee') === 0 || substr($input[0], -strlen('.coffee')) === '.coffee') { // require
			 	$path = $baseDir . '/' . substr($input[0], strlen($req));
				if (file_exists($path)) {
					return '`'.$coffee->compile(file_get_contents($path)).'`';
				}
				return "### file '$path' not found ###";
			}
			return $input[0];
		};

		$pattern = '~^\#=\srequire(_tree)?\s[a-z0-9-_/.]+(\.js)?~im';
		return preg_replace_callback($pattern, $callback, $s);
	}

	/**
	 * Invoke filter
	 *
	 * @param string
	 * @param \WebLoader\Compiler
	 * @param string
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file = NULL)
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
			$baseDir = pathinfo($file, PATHINFO_DIRNAME);
			$code = $this->processJavaScriptFile($code, $baseDir);
		} else if (pathinfo($file, PATHINFO_EXTENSION) === 'coffee' && isset($this->coffee)) {
			$baseDir = pathinfo($file, PATHINFO_DIRNAME);
			$code = $this->processCoffeeScriptFile($code, $baseDir);
		}

		return $code;
	}
}