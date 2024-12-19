<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\DAV\CardDAV\Integration\ExternalAddressBook;
use OCA\DAV\CardDAV\Integration\IAddressBookProvider;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\ConfigurationNotFound;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;

class AddressBookProvider implements IAddressBookProvider {
	public function __construct(
		private readonly Configuration $configurationService,
		private readonly LdapQuerentFactory $ldapQuerentFactory,
		private readonly ContactsAddressBookFactory $contactsAddressBookFactory,
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getAppId(): string {
		return Application::APPID;
	}

	/**
	 * @inheritDoc
	 */
	public function fetchAllForAddressBookHome(string $principalUri): array {
		$configs = array_filter(
			$this->configurationService->getAll(),
			fn (ConfigurationModel $config): bool => $config->isEnabled()
		);

		$addressBooks = [];
		foreach ($configs as $config) {
			/** @var ConfigurationModel $config */
			$cardBackend = new LdapCardBackend($this->ldapQuerentFactory->get($config), $config);
			$addressBooks[] = new AddressBook(Application::APPID, $cardBackend);
		}

		return $addressBooks;
	}

	/**
	 * @inheritDoc
	 */
	public function hasAddressBookInAddressBookHome(string $principalUri, string $uri): bool {
		foreach ($this->configurationService->getAll() as $config) {
			if ($config->isEnabled()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getAddressBookInAddressBookHome(string $principalUri, string $uri): ?ExternalAddressBook {
		foreach ($this->configurationService->getAll() as $config) {
			/** @var ConfigurationModel $config */
			if ($config->isEnabled() && (string)$config->getId() === $uri) {
				$cardBackend = new LdapCardBackend($this->ldapQuerentFactory->get($config), $config);
				return new AddressBook($principalUri, $cardBackend);
			}
		}

		return null;
	}

	/**
	 * @throws ConfigurationNotFound
	 */
	public function getAddressBookById(int $addressBookId): AddressBook {
		$config = $this->configurationService->get($addressBookId);
		$cardBackend = new LdapCardBackend($this->ldapQuerentFactory->get($config), $config);

		return new AddressBook(Application::APPID, $cardBackend);
	}

	/**
	 * @return ContactsAddressBook[]
	 */
	public function fetchAllForContactsStore(): array {
		$configs = array_filter(
			$this->configurationService->getAll(),
			fn (ConfigurationModel $config): bool => $config->isEnabled()
		);

		$addressBooks = [];
		foreach ($configs as $config) {
			/** @var ConfigurationModel $config */
			$cardBackend = new LdapCardBackend($this->ldapQuerentFactory->get($config), $config);
			$addressBooks[] = $this->contactsAddressBookFactory->get($cardBackend);
		}

		return $addressBooks;
	}
}
