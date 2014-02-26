<?php

namespace WebLoader;


/**
 * SCSS imported file modification checker
 *
 * @author Erik Pöhm
 * @license MIT
 */
class ScssModifyChecker
{
	/**
	 * @var int Last modified file
	 */
	private $lastModifiedScss = 0;

	/**
	 * @var array All scss files
	 */
	private $scssFiles;

	/**
	 * @var array All imported files from scss file
	 */
	private $scssImportFiles;

	/**
	 * @param array $files All webloaded files
	 */
	public function __construct(array $files)
	{
		$this->scssFiles = self::findScssFiles($files);
		if (count($this->scssFiles)) {
			$this->scssImportFiles = self::parseScssImportFiles();
			$this->lastModifiedScss = self::getLastImportedFileModification();
		}
	}

	/**
	 * @param $files All loaded webloaded files
	 * @return array Only scss files
	 */
	private static function findScssFiles($files)
	{
		$scssFiles = array();

		foreach ($files as $file)
		{
			if (pathinfo($file, PATHINFO_EXTENSION) === 'scss') {
				$scssFiles[] = $file;
			}
		}

		return $scssFiles;
	}

	/**
	 * @return array Searches the Scss file for imports
	 */
	private function parseScssImportFiles()
	{
		$importFiles = array();
		foreach ($this->scssFiles as $file) {
			$filePath = dirname($file);
			$sourceFile = fopen($file, "r");
			if ($sourceFile) {
				while (!feof($sourceFile)) {
					$line = fgets($sourceFile);
					preg_match("/\s*@import\s*\"([\w|\-|\/]*)\"/", $line, $match);
					if (!empty($match)) {
						$scssFile = $filePath . '/' . $match[1] . '.scss';
						if (file_exists($scssFile)) {
							$importFiles[] = $scssFile;
						}
					}
				}
			}
		}

		return $importFiles;
	}

	/**
	 * @return int
	 */
	private function getLastImportedFileModification()
	{
		$lastModified = 0;
		foreach ($this->scssImportFiles as $file) {
			$lastModified = filemtime($file) > $lastModified ? filemtime($file) : $lastModified;
		}

		return $lastModified;
	}

	/**
	 * @return int Last modified
	 */
	public function getLastModification()
	{
		return $this->lastModifiedScss;
	}

}

