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

use OCP\Constants;
use OCP\IAddressBook;

class ContactsAddressBook implements IAddressBook {
	/** @var ICardBackend */
	private $cardBackend;

	public function __construct(ICardBackend $cardBackend) {
		$this->cardBackend = $cardBackend;
	}

	public function getKey() {
		return $this->cardBackend->getURI();
	}

	public function getUri(): string {
		return $this->cardBackend->getURI();
	}

	public function getDisplayName() {
		return $this->cardBackend->getDisplayName();
	}

	public function search($pattern, $searchProperties, $options) {
		// searchProperties are ignore as we follow search attributes
		// options worth considering: types
		$vCards = $this->cardBackend->searchCards($pattern);
		if(isset($options['offset'])) {
			$vCards = array_slice($vCards, (int)$options['offset']);
		}
		if (isset($options['limit'])) {
			$vCards = array_slice($vCards,0, (int)$options['limit']);
		}

		$result = [];
		foreach ($vCards as $card) {
			$record = $card->getData();
			$record['FN'] = array_pop($record['FN']);
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
}
