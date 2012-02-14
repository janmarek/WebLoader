<?php

namespace WebLoader;

/**
 * IFileCollection
 *
 * @author Jan Marek
 */
interface IFileCollection
{

	/**
	 * @return array
	 */
	public function getFiles();

	/**
	 * @return array
	 */
	public function getRemoteFiles();

}
