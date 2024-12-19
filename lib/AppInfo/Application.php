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

	private function registerAddressBook(IManager $cm) {
		/** @var AddressBookProvider $provider */
		$provider = $this->getContainer()->get(AddressBookProvider::class);
		foreach ($provider->fetchAllForContactsStore() as $ab) {
			$cm->registerAddressBook($ab);
		}
	}

	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
		$cm = $context->getServerContainer()->get(IManager::class);
		$cm->register(function () use ($cm): void {
			$this->registerAddressBook($cm);
		});
	}
}
