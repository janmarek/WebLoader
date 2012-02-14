<?php

namespace WebLoader;

/**
 * Compiler
 *
 * @author Jan Marek
 */
class Compiler
{

	/** @var string */
	private $outputDir;

	/** @var bool */
	private $joinFiles = true;

	/** @var array */
	public $filters = array();

	/** @var array */
	public $fileFilters = array();

	/** @var IFileCollection */
	private $collection;

	/** @var IOutputNamingConvention */
	private $namingConvention;

	public function __construct(IFileCollection $files, IOutputNamingConvention $convention, $outputDir)
	{
		$this->collection = $files;
		$this->namingConvention = $convention;
		$this->setOutputDir($outputDir);
	}

	/**
	 * Get temp path
	 * @return string
	 */
	public function getOutputDir()
	{
		return $this->outputDir;
	}

	/**
	 * Set temp path
	 * @param string $tempPath
	 * @return WebLoader
	 */
	public function setOutputDir($tempPath)
	{
		$tempPath = realpath($tempPath);

		if ($tempPath === false) {
			throw new FileNotFoundException("Temp path does not exist.");
		}

		if (!is_writable($tempPath)) {
			throw new \InvalidArgumentException("Directory '$tempPath' is not writeable.");
		}

		$this->outputDir = $tempPath;

		return $this;
	}

	/**
	 * Get join files
	 * @return bool
	 */
	public function getJoinFiles()
	{
		return $this->joinFiles;
	}

	/**
	 * Set join files
	 * @param bool $joinFiles
	 * @return WebLoader
	 */
	public function setJoinFiles($joinFiles)
	{
		$this->joinFiles = (bool) $joinFiles;
		return $this;
	}

	/**
	 * Get last modified timestamp of newest file
	 * @param array $files
	 * @return int
	 */
	public function getLastModified(array $files = null)
	{
		if ($files === null) {
			$files = $this->collection->getFiles();
		}

		$modified = 0;

		foreach ($files as $file) {
			$modified = max($modified, filemtime($file));
		}

		return $modified;
	}

	/**
	 * Get joined content of all files
	 * @param array $files
	 * @return string
	 */
	public function getContent(array $files = null)
	{
		if ($files === null) {
			$files = $this->collection->getFiles();
		}

		// load content
		$content = "";
		foreach ($files as $file) {
			$content .= $this->loadFile($file);
		}

		// apply filters
		foreach ($this->collection->getFilters() as $filter) {
			$content = call_user_func($filter, $content, $this);
		}

		return $content;
	}

	/**
	 * Load content and save file
	 * @param array $files
	 * @param bool $ifModified
	 * @return string filename of generated file
	 */
	public function generate($files = NULL, $ifModified = TRUE)
	{
		$name = $this->namingConvention->getFilename($files, $this);
		$path = $this->outputDir . "/" . $name;
		$lastModified = $this->getLastModified($files);

		if (!$ifModified || !file_exists($path) || $lastModified > filemtime($path)) {
			file_put_contents("safe://" . $path, $this->getContent($files));
		}

		return $name . "?" . $lastModified;
	}

	/**
	 * Load file
	 * @param string $file path
	 * @return string
	 */
	protected function loadFile($file)
	{
		$content = file_get_contents($file);

		foreach ($this->collection->getFileFilters() as $filter) {
			$content = call_user_func($filter, $content, $this, $file);
		}

		return $content;
	}

	/**
	 * @return \WebLoader\IFileCollection
	 */
	public function getFileCollection()
	{
		return $this->collection;
	}

	/**
	 * @return \WebLoader\IOutputNamingConvention
	 */
	public function getOutputNamingConvention()
	{
		return $this->namingConvention;
	}

	/**
	 * @param \WebLoader\IFileCollection $collection
	 */
	public function setFileCollection(IFileCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @param \WebLoader\IOutputNamingConvention $namingConvention
	 */
	public function setOutputNamingConvention(IOutputNamingConvention $namingConvention)
	{
		$this->namingConvention = $namingConvention;
	}

	/**
	 * @param callback $filter
	 * @throws \InvalidArgumentException
	 */
	public function addFilter($filter)
	{
		if (!is_callable($filter)) {
			throw new \InvalidArgumentException('Filter is not callable.');
		}

		$this->filters[] = $filter;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param callback $filter
	 * @throws \InvalidArgumentException
	 */
	public function addFileFilter($filter)
	{
		if (!is_callable($filter)) {
			throw new \InvalidArgumentException('Filter is not callable.');
		}

		$this->fileFilters[] = $filter;
	}

	/**
	 * @return array
	 */
	public function getFileFilters()
	{
		return $this->fileFilters;
	}

}
