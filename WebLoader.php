<?php

/**
 * WebLoader
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class WebLoader extends Control
{
	/** @var string */
	public $sourcePath;

	/** @var string */
	public $sourceUri;

	/** @var string */
	public $tempPath;

	/** @var string */
	public $tempUri;

	/** @var bool */
	public $joinFiles = true;

	/** @var string */
	public $generatedFileNamePrefix = "webloader-";

	/** @var string */
	public $generatedFileNameSuffix = "";

	/** @var bool */
	public $throwExceptions = false;

	/** @var array */
	public $filters = array();

	/** @var array */
	private $files = array();

	/**
	 * Get html element including generated content
	 * @param string $source
	 * @return Html
	 */
	abstract public function getElement($source);

	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$hasArgs = func_num_args() > 0;

		if ($hasArgs) {
			$backup = $this->files;
			$this->clear();
			$this->addFiles(func_get_args());
		}

		// joined files
		if ($this->joinFiles) {
			$file = $this->generate($this->files);
			echo $this->getElement($this->tempUri . "/" . $file);

		// separated files
		} else {
			foreach ($this->files as $file) {
				$filename = $this->generate(array($file));
				echo $this->getElement($this->tempUri . "/" . $filename);
			}
		}

		if ($hasArgs) {
			$this->files = $backup;
		}
	}

	/**
	 * Get file list
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}

	/**
	 * Add file
	 * @param string $file filename
	 */
	public function addFile($file)
	{
		if (in_array($file, $this->files)) {
			return;
		}

		if (!file_exists($this->sourcePath . "/" . $file)) {
			if ($this->throwExceptions) {
				throw new FileNotFoundException("File '$this->sourcePath/$file' does not exist.");
			} else {
				return;
			}
		}

		$this->files[] = $file;
	}

	/**
	 * Add files
	 * @param array $files list of files
	 */
	public function addFiles(array $files)
	{
		foreach ($files as $file) {
			$this->addFile($file);
		}
	}

	/**
	 * Remove file
	 * @param string $file filename
	 */
	public function removeFile($file)
	{
		$this->removeFiles(array($file));
	}

	/**
	 * Remove files
	 * @param array $files list of files
	 */
	public function removeFiles(array $files)
	{
		$this->files = array_diff($this->files, $files);
	}

	/**
	 * Remove all files
	 */
	public function clear() {
		$this->files = array();
	}

	/**
	 * Get last modified timestamp of newest file
	 * @param array $files
	 * @return int
	 */
	public function getLastModified(array $files = null)
	{
		if ($files === null) {
			$files = $this->files;
		}

		$modified = 0;

		foreach ($files as $file) {
			$modified = max($modified, filemtime($this->sourcePath . "/" . $file));
		}

		return $modified;
	}

	/**
	 * Filename of generated file
	 * @param array $files
	 * @return string
	 */
	public function getGeneratedFilename(array $files = null)
	{
		if ($files === null) {
			$files = $this->files;
		}

		$hash = md5(implode("|", $files) . "|" . $this->getLastModified($files) . "|" . $this->sourcePath . "|" . $this->tempUri);
		$origFilenamePart = count($files) === 1 ? String::webalize($files[0]) . "-" : "";

		return $this->generatedFileNamePrefix . $origFilenamePart . $hash . $this->generatedFileNameSuffix;
	}

	/**
	 * Get joined content of all files
	 * @param array $files
	 * @return string
	 */
	public function getContent(array $files = null)
	{
		if ($files === null) {
			$files = $this->files;
		}

		$content = "";

		foreach ($files as $file) {
			$content .= $this->loadFile($file);
		}

		return $this->applyFilters($content);
	}

	/**
	 * Load content and save file
	 * @param array $files
	 * @return string filename of generated file
	 */
	protected function generate($files)
	{
		$name = $this->getGeneratedFilename($files);

		$path = $this->tempPath . "/" . $name;

		if (!file_exists($path)) {
			if (!in_array(SafeStream::PROTOCOL, stream_get_wrappers())) {
				SafeStream::register();
			}

			if (is_writable($this->tempPath)) {
				file_put_contents("safe://" . $path, $this->getContent($files));
			} else {
				throw new InvalidStateException("Directory '$this->tempPath' is not writeable.");
			}
		}

		return $name;
	}

	/**
	 * Apply filters to a string
	 * @param string $s
	 * @return string
	 */
	protected function applyFilters($s)
	{
		foreach ($this->filters as $filter) {
			$s = call_user_func($filter, $s);
		}

		return $s;
	}

	/**
	 * Load file
	 * @param string $path
	 * @return string
	 */
	protected function loadFile($file)
	{
		return file_get_contents($this->sourcePath . "/" . $file);
	}
}