<?php

if (!isset($_SERVER['argv'][1])) {
	echo "Missing arg\n";
	exit(1);
}
$pkgs = file_get_contents($_SERVER['argv'][1]);
if (!$pkgs) {
	echo "can't read json file\n";
	exit(2);
}

$pkgs = json_decode($pkgs, true);
if (!is_array($pkgs)) {
	echo "can't decode json file\n";
	exit(3);
}

$lic = [];
if (isset($pkgs['packages'])) {
	foreach ($pkgs['packages'] as $pkg) {
		printf("Provides:  bundled(php-%s) = %s\n", str_replace(['/', '_'], ['-', '-'], $pkg['name']), $pkg['version']);
		$lic = array_merge($lic, $pkg['license']);
	}
} elseif (isset($pkgs['dependencies'])) {
	foreach ($pkgs['dependencies'] as $pkg) {
		$n = strtolower($pkg['name'] ?? $pkg['lib']);
		$n = str_replace('.js', '', $n);
		printf("Provides:  bundled(js-%s) = %s\n", $n, $pkg['version']);
		if (isset($pkg['license'])) {
			$lic[] = $pkg['license'];
		}
	}
} else {
	echo "unkown content\n";
	exit(4);
}
sort($lic);
printf("\nLicense: %s\n", implode(' and ', array_unique($lic)));
