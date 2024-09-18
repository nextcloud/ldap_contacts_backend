<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/appinfo',
		__DIR__ . '/lib',
		__DIR__ . '/tests',
	])
	// uncomment to reach your current PHP version
	// ->withPhpSets()
	->withTypeCoverageLevel(0);
