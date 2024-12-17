<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OC\Security\CSRF\CsrfTokenManager;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\PhotoServiceUnavailable;
use OCP\Constants;
use OCP\IAddressBook;
use OCP\IConfig;
use OCP\IURLGenerator;

class ContactsAddressBook implements IAddressBook {
	private readonly CsrfTokenManager $tokenManager;

	public const DAV_PROPERTY_SOURCE = 'X-NC_LDAP_CONTACTS_ID';

	public function __construct(
		private readonly ICardBackend $cardBackend,
		private readonly IConfig $config,
		private readonly IURLGenerator $urlGenerator,
		CsrfTokenManager $tokenManager,
		private readonly PhotoService $photoService,
		private readonly ?string $principalURI = null,
	) {
		$this->tokenManager = $tokenManager;
	}

	public function getKey() {
		return $this->cardBackend->getURI();
	}

	public function getUri(): string {
		// this will have the URL part to direct to the contacts app
		return $this->principalURI ?? $this->cardBackend->getURI();
	}

	public function getDisplayName() {
		return $this->cardBackend->getDisplayName();
	}

	public function search($pattern, $searchProperties, $options) {
		// searchProperties are ignore as we follow search attributes
		// options worth considering: types
		$limit = $this->config->getSystemValueInt('sharing.maxAutocompleteResults', 25);
		$vCards = $this->cardBackend->searchCards($pattern, $limit);
		if (isset($options['offset'])) {
			$vCards = array_slice($vCards, (int)$options['offset']);
		}

		if (isset($options['limit'])) {
			$vCards = array_slice($vCards, 0, (int)$options['limit']);
		}

		$result = [];
		foreach ($vCards as $card) {
			$record = $card->getData();
			//FN field must be flattened for contacts menu
			$record['FN'] = array_pop($record['FN']);
			if (isset($record['PHOTO'])) {
				try {
					// "data:image/<submime>;base64," is prefixed
					$imageData = substr((string)$record['PHOTO'][0], strpos((string)$record['PHOTO'][0], ','));
					$this->photoService->store($this->cardBackend->getURI(), $record['URI'], $imageData);
					$photoUrl = $this->urlGenerator->linkToRouteAbsolute(Application::APPID . '.contacts.photo',
						[
							'sourceId' => $this->cardBackend->getURI(),
							'contactId' => $record['URI'],
							'requesttoken' => $this->tokenManager->getToken()->getEncryptedValue()
						]);
					$record['PHOTO'] = 'VALUE=uri:' . $photoUrl;
				} catch (PhotoServiceUnavailable) {
				}
			}

			// prevents linking to contacts if UID is set
			$record['isLocalSystemBook'] = true;
			$record[self::DAV_PROPERTY_SOURCE] = $this->cardBackend->getURI();
			$result[] = $record;
		}

		return $result;
	}

	public function createOrUpdate($properties) {
		return [];
	}

	public function getPermissions() {
		return Constants::PERMISSION_READ;
	}

	public function delete($id) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function isShared(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function isSystemAddressBook(): bool {
		return true;
	}
}
