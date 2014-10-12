<?php

$rootDir = __DIR__ . '/../..';
$travisDir = __DIR__;

if (getenv('NETTE') !== 'default') {
	$composerFile = $travisDir . '/composer-' . getenv('NETTE') . '.json';

	unlink($rootDir . '/composer.json');
	copy($composerFile, $rootDir . '/composer.json');

	echo "Using tests/", basename($composerFile);

} else {
	echo "Using default composer.json";
}
