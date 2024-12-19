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
use Sabre\DAV\PropPatch;

class AddressBook extends ExternalAddressBook {
	private readonly ICardBackend $cardBackend;

	public function __construct(
		private readonly string $principalUri,
		ICardBackend $cardBackend,
	) {
		parent::__construct(Application::APPID, $cardBackend->getURI());
		$this->cardBackend = $cardBackend;
	}


	/**
	 * @inheritDoc
	 */
	public function createFile($name, $data = null) {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 * @throws RecordNotFound
	 */
	public function getChild($name) {
		return $this->cardBackend->getCard($name);
	}

	/**
	 * @inheritDoc
	 */
	public function getChildren() {
		return $this->cardBackend->getCards();
	}

	/**
	 * @inheritDoc
	 */
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
	public function delete() {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 */
	public function getLastModified() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function propPatch(PropPatch $propPatch) {
		throw new Exception('This addressbook is immutable');
	}

	/**
	 * @inheritDoc
	 */
	public function getProperties($properties) {
		return [
			'principaluri' => $this->principalUri,
			'{DAV:}displayname' => $this->cardBackend->getDisplayName(),
			'{' . Plugin::NS_OWNCLOUD . '}read-only' => true,
		];
	}
}
