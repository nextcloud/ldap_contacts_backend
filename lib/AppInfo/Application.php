<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\AppInfo;

use OCA\LDAPContactsBackend\Service\AddressBookProvider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Contacts\IManager;

require_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {
	public const APPID = 'ldap_contacts_backend';

	public function __construct() {
		parent::__construct(self::APPID);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
	}

	#[\Override]
	public function boot(IBootContext $context): void {
		$context->injectFn(
			function (IManager $cm, AddressBookProvider $provider): void {
				$cm->register(function () use ($cm, $provider): void {
					foreach ($provider->fetchAllForContactsStore() as $ab) {
						$cm->registerAddressBook($ab);
					}
				});
			}
		);
	}
}
