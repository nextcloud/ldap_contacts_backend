<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/appinfo',
		__DIR__ . '/lib',
		__DIR__ . '/tests',
	])
	->withPhpSets(php81: true)
	->withTypeCoverageLevel(10)
	->withImportNames(importShortClasses: false)
	->withSets([
		NextcloudSets::NEXTCLOUD_25,
		NextcloudSets::NEXTCLOUD_26,
		NextcloudSets::NEXTCLOUD_27,
		NextcloudSets::NEXTCLOUD_ALL,
	]);