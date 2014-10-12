<?php

$rootDir = __DIR__ . '/../..';
$travisDir = __DIR__;

if (getenv('NETTE') !== 'default') {
	$composerFile = $travisDir . '/composer-' . getenv('NETTE') . '.json';

	unlink($rootDir . '/composer.json');
	if (!copy($composerFile, $rootDir . '/composer.json')) {
		exit(1);
	}

	echo "Using tests/", basename($composerFile);

} else {
	echo "Using default composer.json";
}

exit(0);
