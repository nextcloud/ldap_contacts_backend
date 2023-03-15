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

use Exception;
use OCA\DAV\CardDAV\Integration\ExternalAddressBook;
use OCA\DAV\DAV\Sharing\Plugin;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use Sabre\DAV\PropPatch;

class AddressBook extends ExternalAddressBook {
	private ICardBackend $cardBackend;
	private string $principalUri;

	public function __construct(string $principalUri, ICardBackend $cardBackend) {
		parent::__construct(Application::APPID, $cardBackend->getURI());
		$this->cardBackend = $cardBackend;
		$this->principalUri = $principalUri;
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
		} catch (RecordNotFound $e) {
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
