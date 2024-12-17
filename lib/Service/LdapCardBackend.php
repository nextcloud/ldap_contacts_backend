<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Model\Card;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use Symfony\Component\Ldap\Entry;
use function base64_decode;

class LdapCardBackend implements ICardBackend {
	public function __construct(
		private readonly LdapQuerent $ldapQuerent,
		private readonly ConfigurationModel $configuration,
	) {
	}

	public function getURI(): string {
		return (string)$this->configuration->getId();
	}

	public function getDisplayName(): string {
		return $this->configuration->getAddressBookDisplayName();
	}

	/**
	 * @throws RecordNotFound
	 */
	public function getCard(string $name): Card {
		$record = $this->ldapQuerent->fetchOne(base64_decode($name));
		return $this->entryToCard($record);
	}

	public function searchCards(string $pattern, int $limit = 0): array {
		$records = $this->ldapQuerent->find($pattern, $limit);
		$vCards = [];
		foreach ($records as $record) {
			$vCards[] = $this->entryToCard($record);
			unset($record);
		}

		return $vCards;
	}

	public function getCards(): array {
		// to appear in the contacts app, this must really return everything
		// as search is only by client in the presented contacts
		$records = $this->ldapQuerent->fetchAll();
		$vCards = [];
		foreach ($records as $record) {
			$vCards[] = $this->entryToCard($record);
			unset($record);
		}

		return $vCards;
	}

	protected function entryToCard(Entry $record): Card {
		$vCardData = LdapEntryToVcard::convert($record, $this->configuration);
		return new Card($vCardData);
	}
}
