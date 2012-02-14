<?php

namespace WebLoader\Nette;

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

	/**
	 * Get html element including generated content
	 * @param string source
	 * @return Html
	 */
	abstract public function getElement($source);


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$hasArgs = func_num_args() > 0;

		$fileCollection = $this->compiler->getFileCollection();

		if ($hasArgs) {
			$backup = $this->compiler->getFileCollection();

			$backup = $fileCollection->getFiles();
			$backupRemote = $fileCollection->getRemoteFiles();
			$fileCollection->clear();
			$fileCollection->addFiles(func_get_args());
		}

		// remote files
		foreach ($fileCollection->getRemoteFiles() as $file) {
			echo $this->getElement($file);
		}

		foreach ($this->compiler->generate() as $fileName) {

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
}
