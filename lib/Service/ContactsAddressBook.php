<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
	/** @var ICardBackend */
	private $cardBackend;
	/** @var string */
	private $principalURI;
	/** @var IConfig */
	private $config;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var CsrfTokenManager */
	private $tokenManager;

	public const DAV_PROPERTY_SOURCE = 'X-NC_LDAP_CONTACTS_ID';
	/** @var PhotoService */
	private $photoService;

	public function __construct(
		ICardBackend $cardBackend,
		IConfig $config,
		IURLGenerator $urlGenerator,
		CsrfTokenManager $tokenManager,
		PhotoService $photoService,
		?string $principalURI = null
	) {
		$this->cardBackend = $cardBackend;
		$this->principalURI = $principalURI;
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->tokenManager = $tokenManager;
		$this->photoService = $photoService;
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
		if(isset($options['offset'])) {
			$vCards = array_slice($vCards, (int)$options['offset']);
		}
		if (isset($options['limit'])) {
			$vCards = array_slice($vCards,0, (int)$options['limit']);
		}

		$result = [];
		foreach ($vCards as $card) {
			$record = $card->getData();
			//FN field must be flattened for contacts menu
			$record['FN'] = array_pop($record['FN']);
			if($record['PHOTO']) {
				try {
					// "data:image/<submime>;base64," is prefixed
					$imageData = substr($record['PHOTO'][0], strpos($record['PHOTO'][0], ','));
					$this->photoService->store($this->cardBackend->getURI(), $record['URI'], $imageData);
					$photoUrl = $this->urlGenerator->linkToRouteAbsolute(Application::APPID . '.contacts.photo',
						[
							'sourceId' => $this->cardBackend->getURI(),
							'contactId' => $record['URI'],
							'requesttoken' => $this->tokenManager->getToken()->getEncryptedValue()
						]);
					$record['PHOTO'] = 'VALUE=uri:' . $photoUrl;
				} catch (PhotoServiceUnavailable $e) {

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
