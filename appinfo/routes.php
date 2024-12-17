<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'routes' => [
		['name' => 'contacts#import', 'url' => '/import/{sourceId}/{contactId}', 'verb' => 'GET'],
		['name' => 'contacts#photo', 'url' => '/photo/{sourceId}/{contactId}', 'verb' => 'GET'],
	],
];
