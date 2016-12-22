<?php

namespace WebLoader\Nette;

use WebLoader\Compiler;
use WebLoader\FileCollection;

/**
 * Web loader
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class WebLoader extends \Nette\Application\UI\Control
{

	/** @var \WebLoader\Compiler */
	private $compiler;

	/** @var string */
	private $tempPath;

	public function __construct(Compiler $compiler, $tempPath)
	{
		parent::__construct();
		$this->compiler = $compiler;
		$this->tempPath = $tempPath;
	}

	/**
	 * @return \WebLoader\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

	/**
	 * @param \WebLoader\Compiler
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
	}

	/**
	 * @return string
	 */
	public function getTempPath()
	{
		return $this->tempPath;
	}

	/**
	 * @param string
	 */
	public function setTempPath($tempPath)
	{
		$this->tempPath = $tempPath;
	}

	/**
	 * Get html element including generated content
	 * @param string $source
	 * @return \Nette\Utils\Html
	 */
	abstract public function getElement($source);

	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$hasArgs = func_num_args() > 0;

		if ($hasArgs) {
			$backup = $this->compiler->getFileCollection();
			$newFiles = new FileCollection($backup->getRoot());
			$newFiles->addFiles(func_get_args());
			$this->compiler->setFileCollection($newFiles);
		}

		// remote files
		foreach ($this->compiler->getFileCollection()->getRemoteFiles() as $file) {
			echo $this->getElement($file), PHP_EOL;
		}

		foreach ($this->compiler->generate() as $file) {
			echo $this->getElement($this->getGeneratedFilePath($file)), PHP_EOL;
		}

		if ($hasArgs) {
			$this->compiler->setFileCollection($backup);
		}
	}

	/**
	 * Get content of a compiled file by its URL path
	 *
	 * @param string $source
	 * @return string
	 */
	protected function getCompiledFileContent($source)
	{
		$outputDir = $this->compiler->getOutputDir();
		$urlPath = parse_url($source, PHP_URL_PATH);
		$fileName = basename($urlPath);
		$filePath = $outputDir . '/' . $fileName;
		$content = file_get_contents($filePath);

		return $content;
	}

	protected function getGeneratedFilePath($file)
	{
		return $this->tempPath . '/' . $file->file . '?' . $file->lastModified;
	}

	/**
	 * Generate Subresource Integrity checksums for all set hashing algorithms
	 *
	 * @param string $fileContent
	 * @return string
	 */
	protected function getSriChecksums($fileContent)
	{
		$checksums = [];

		foreach ($this->compiler->getSriHashingAlgorithms() as $algorithm) {
			$checksums[] = $this->getOneSriChecksum($algorithm, $fileContent);
		}

		return implode(' ', $checksums);
	}

	/**
	 * Generate Subresource Integrity checksum
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity
	 * @param string $hashingAlgorithm
	 * @param string $fileContent
	 * @return string
	 */
	private function getOneSriChecksum($hashingAlgorithm, $fileContent)
	{
		$hash = hash($hashingAlgorithm, $fileContent, true);
		$hashBase64 = base64_encode($hash);

		return $hashingAlgorithm . '-' . $hashBase64;
	}

}
