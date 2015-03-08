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
	 * @param string $cmd
	 * @param string|NULL $stdin
	 * @param string|NULL $cwd
	 * @param array|NULL $env
	 * @return string
	 * @throws \RuntimeExeption
	 */
	public static function run($cmd, $stdin = NULL, $cwd = NULL, array $env = NULL)
	{
		$descriptorspec = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w'), // stderr
		);

		$pipes = array();
		$proc = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

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
