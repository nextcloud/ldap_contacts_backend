<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use Exception;
use OCA\DAV\CardDAV\Integration\ExternalAddressBook;
use OCA\DAV\DAV\Sharing\Plugin;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Model\Card;
use Sabre\DAV\PropPatch;

class AddressBook extends ExternalAddressBook {
	public function __construct(
		private readonly string $principalUri,
		private readonly ICardBackend $cardBackend,
	) {
		parent::__construct(Application::APPID, $cardBackend->getURI());
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function createFile($name, $data = null): never {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 * @throws RecordNotFound
	 */
	#[\Override]
	public function getChild($name): Card {
		return $this->cardBackend->getCard($name);
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getChildren() {
		return $this->cardBackend->getCards();
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function childExists($name) {
		try {
			$this->getChild($name);
			return true;
		} catch (RecordNotFound) {
			return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function delete(): never {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getLastModified() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function propPatch(PropPatch $propPatch): never {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getProperties($properties) {
		return [
			'principaluri' => $this->principalUri,
			'{DAV:}displayname' => $this->cardBackend->getDisplayName(),
			'{' . Plugin::NS_OWNCLOUD . '}read-only' => true,
		];
	}
}
