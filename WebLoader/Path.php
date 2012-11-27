<?php

namespace WebLoader;

class Path
{

	public static function normalize($path)
	{
		$path = strtr($path, '\\', '/');
		$root = ($path[0] === '/') ? '/' : '';
		$pieces = explode('/', trim($path, '/'));
		$res = array();

		foreach ($pieces as $piece) {
			if ($piece === '.' || empty($piece)) {
				continue;
			}
			if ($piece === '..') {
				array_pop($res);
			} else {
				array_push($res, $piece);
			}
		}

		return $root . implode('/', $res);
	}

}
