<?php

namespace WebLoader;

/**
 * @author Jan Marek
 */
interface IFileCollection
{

	/**
	 * @return string
	 */
	public function getRoot();

	/**
	 * @return array
	 */
	public function getFiles();

	/**
	 * @return array
	 */
	public function getRemoteFiles();

}
