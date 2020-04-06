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

use OCA\LDAPContactsBackend\Model\Card;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use Sabre\VObject\Component\VCard;
use Symfony\Component\Ldap\Entry;

class LdapCardBackend implements ICardBackend {
	/** @var LdapQuerent */
	private $ldapQuerent;
	/** @var ConfigurationModel */
	private $configuration;

	public function __construct(LdapQuerent $ldapQuerent, ConfigurationModel $configuration) {
		$this->ldapQuerent = $ldapQuerent;
		$this->configuration = $configuration;
	}

	public function getURI(): string {
		return (string)$this->configuration->getId();
	}

	public function getDisplayName(): string {
		return $this->configuration->getAddressBookDisplayName();
	}

	public function getCard($name): Card {
		$record = $this->ldapQuerent->fetchOne($name);
		return $this->entryToCard($record);
	}

	public function getCards(): array {
		$records = $this->ldapQuerent->fetchAll();
		$vCards = [];
		foreach ($records as $record) {
			 $vCards[] = $this->entryToCard($record);
		}
		return $vCards;
	}

	protected function entryToCard(Entry $record): Card {
		$vCardData = LdapEntryToVcard::convert($record, $this->configuration);
		return new Card($vCardData);
	}

}
