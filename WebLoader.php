<?php

namespace WebLoader;

/**
 * Web loader
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class WebLoader extends \Nette\Application\UI\Control {

	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var string */
	private $sourcePath;

	/** @var string */
	private $tempPath;

	/** @var string */
	private $tempUri;

	/** @var bool */
	private $joinFiles = true;

	/** @var string */
	private $generatedFileNamePrefix = "webloader-";

	/** @var string */
	private $generatedFileNameSuffix = "";

	/** @var bool */
	private $throwExceptions = false;

	/** @var array */
	public $filters = array();

	/** @var array */
	public $fileFilters = array();

	/** @var array */
	private $files = array();

	/** @var array */
	private $remoteFiles = array();

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getters & setters">

	/**
	 * Get source path
	 * @return string
	 */
	public function getSourcePath() {
		return $this->sourcePath;
	}


	/**
	 * Set source path
	 * @param string source path
	 * @return WebLoader
	 */
	public function setSourcePath($sourcePath) {
		$sourcePath = realpath($sourcePath);

		if ($sourcePath === false) {
			throw new \Nette\FileNotFoundException("Source path does not exist.");
		}

		$this->sourcePath = $sourcePath;

		return $this;
	}


	/**
	 * Get temp path
	 * @return string
	 */
	public function getTempPath() {
		return $this->tempPath;
	}


	/**
	 * Set temp path
	 * @param string temp path
	 * @return WebLoader
	 */
	public function setTempPath($tempPath) {
		$tempPath = realpath($tempPath);

		if ($tempPath === false) {
			throw new \Nette\FileNotFoundException("Temp path does not exist.");
		}

		if (!is_writable($tempPath)) {
			throw new \Nette\InvalidStateException("Directory '$tempPath' is not writeable.");
		}

		$this->tempPath = $tempPath;

		return $this;
	}


	/**
	 * Get temp uri
	 * @return string
	 */
	public function getTempUri() {
		return $this->tempUri;
	}


	/**
	 * Set temp uri
	 * @param string temp uri
	 * @return string
	 */
	public function setTempUri($tempUri) {
		$this->tempUri = (string) $tempUri;
		return $this;
	}


	/**
	 * Get join files
	 * @return bool
	 */
	public function getJoinFiles() {
		return $this->joinFiles;
	}


	/**
	 * Set join files
	 * @param bool join files
	 * @return WebLoader
	 */
	public function setJoinFiles($joinFiles) {
		$this->joinFiles = (bool) $joinFiles;
		return $this;
	}


	/**
	 * Get generated file name prefix
	 * @return string
	 */
	public function getGeneratedFileNamePrefix() {
		return $this->generatedFileNamePrefix;
	}


	/**
	 * Set generated file name prefix
	 * @param string generated file name prefix
	 * @return WebLoader
	 */
	public function setGeneratedFileNamePrefix($generatedFileNamePrefix) {
		$this->generatedFileNamePrefix = (string) $generatedFileNamePrefix;
		return $this;
	}


	/**
	 * Get generated file name suffix
	 * @return string
	 */
	public function getGeneratedFileNameSuffix() {
		return $this->generatedFileNameSuffix;
	}


	/**
	 * Set generated file name suffix
	 * @param string generated file name suffix
	 * @return WebLoader
	 */
	public function setGeneratedFileNameSuffix($generatedFileNameSuffix) {
		$this->generatedFileNameSuffix = (string) $generatedFileNameSuffix;
		return $this;
	}


	/**
	 * Throw exceptions?
	 * @return bool
	 */
	public function getThrowExceptions() {
		return $this->throwExceptions;
	}


	/**
	 * Set throw exceptions
	 * @param bool throw exceptions
	 * @return WebLoader
	 */
	public function setThrowExceptions($throwExceptions) {
		$this->throwExceptions = (bool) $throwExceptions;
		return $this;
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="files">

	/**
	 * Get file list
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}


	/**
	 * Make path absolute
	 * @param string path
	 * @throws \Nette\FileNotFoundException
	 * @return string
	 */
	public function cannonicalizePath($path) {
		$rel = realpath($this->sourcePath . "/" . $path);
		if ($rel !== false) return $rel;

		$abs = realpath($path);
		if ($abs !== false) return $abs;

		throw new \Nette\FileNotFoundException("File '$path' does not exist.");
	}


	/**
	 * Add file
	 * @param string filename
	 */
	public function addFile($file) {
		try {
			$file = $this->cannonicalizePath($file);

			if (in_array($file, $this->files)) {
				return;
			}

			$this->files[] = $file;

		} catch (\Nette\FileNotFoundException $e) {
			if ($this->throwExceptions) {
				throw $e;
			}
		}
	}


	/**
	 * Add files
	 * @param array list of files
	 */
	public function addFiles(array $files) {
		foreach ($files as $file) {
			$this->addFile($file);
		}
	}


	/**
	 * Remove file
	 * @param string filename
	 */
	public function removeFile($file) {
		$this->removeFiles(array($file));
	}


	/**
	 * Remove files
	 * @param array list of files
	 */
	public function removeFiles(array $files) {
		$files = array_map(array($this, "cannonicalizePath"), $files);
		$this->files = array_diff($this->files, $files);
	}


	/**
	 * Add file in remote repository (for example Google CDN).
	 * @param string URL address
	 */
	public function addRemoteFile($file) {
		if (in_array($file, $this->remoteFiles)) {
			return;
		}

		$this->remoteFiles[] = $file;
	}


	/**
	 * Remove all files
	 */
	public function clear() {
		$this->files = array();
		$this->remoteFiles = array();
	}

	// </editor-fold>


	/**
	 * Get html element including generated content
	 * @param string source
	 * @return Html
	 */
	abstract public function getElement($source);


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render() {
		$hasArgs = func_num_args() > 0;

		if ($hasArgs) {
			$backup = $this->files;
			$backupRemote = $this->remoteFiles;
			$this->clear();
			$this->addFiles(func_get_args());
		}

		// remote files
		foreach ($this->remoteFiles as $file) {
			echo $this->getElement($file);
		}

		// joined files
		if ($this->joinFiles) {
			$file = $this->generate($this->files);
			echo $this->getElement($this->tempUri . "/" . $file);

		// separated files
		} else {
			foreach ($this->files as $file) {
				$file = $this->generate(array($file));
				echo $this->getElement($this->tempUri . "/" . $file);
			}
		}

		if ($hasArgs) {
			$this->files = $backup;
			$this->remoteFiles = $backupRemote;
		}
	}


	/**
	 * Get last modified timestamp of newest file
	 * @param array files
	 * @return int
	 */
	public function getLastModified(array $files = null) {
		if ($files === null) {
			$files = $this->files;
		}

		$modified = 0;

		foreach ($files as $file) {
			$modified = max($modified, filemtime($file));
		}

		return $modified;
	}


	/**
	 * Filename of generated file
	 * @param array files
	 * @return string
	 */
	public function getGeneratedFilename(array $files = null) {
		if ($files === null) {
			$files = $this->files;
		}

		$name = substr(md5(implode("|", $files)), 0, 12);

		if (count($files) === 1) {
			$name .= "-" . pathinfo($files[0], PATHINFO_FILENAME);
		}

		return $this->generatedFileNamePrefix . $name . $this->generatedFileNameSuffix;
	}


	/**
	 * Get joined content of all files
	 * @param array files
	 * @return string
	 */
	public function getContent(array $files = null) {
		if ($files === null) {
			$files = $this->files;
		}

		// load content
		$content = "";
		foreach ($files as $file) {
			$content .= $this->loadFile($file);
		}

		// apply filters
		foreach ($this->filters as $filter) {
			$content = call_user_func($filter, $content, $this);
		}

		return $content;
	}


	/**
	 * Load content and save file
	 * @param array files
	 * @return string filename of generated file
	 */
	protected function generate($files) {
		$name = $this->getGeneratedFilename($files);
		$path = $this->tempPath . "/" . $name;
		$lastModified = $this->getLastModified($files);

		if (!file_exists($path) || $lastModified > filemtime($path)) {
			file_put_contents("safe://" . $path, $this->getContent($files));
		}

		return $name . "?" . $lastModified;
	}


	/**
	 * Load file
	 * @param string file path
	 * @return string
	 */
	protected function loadFile($file) {
		$content = file_get_contents($file);

		foreach ($this->fileFilters as $filter) {
			$content = call_user_func($filter, $content, $this, $file);
		}

		return $content;
	}

}
