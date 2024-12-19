<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

require_once __DIR__ . '/../../../lib/base.php';
\OC::$loader->addValidRoot(\OC::$SERVERROOT . '/tests');
if (!class_exists(TestCase::class)) {
	require_once('PHPUnit/Autoload.php');
}

\OC_App::loadApp('ldap_contacts_backend');

OC_Hook::clear();
