<?php

namespace WebLoader\Filter;

/**
 * Simple process wrapper
 *
 * @author Patrik Votoček
 * @license MIT
 */
class Process
{

	/**
	 * @param string
	 * @param string|NULL
	 * @return string
	 * @throws \RuntimeExeption
	 */
	public static function run($cmd, $stdin = NULL)
	{
		$descriptorspec = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w'), // stderr
		);

		$pipes = array();
		$proc = proc_open($cmd, $descriptorspec, $pipes);

		if (!empty($stdin)) {
			fwrite($pipes[0], $stdin . PHP_EOL);
		}
		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		$code = proc_close($proc);

		if ($code != 0) {
			throw new \RuntimeException($stderr, $code);
		}

		return $stdout;
	}

}