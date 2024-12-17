<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Model\Card;

interface ICardBackend {
	public function getURI(): string;

	public function getDisplayName(): string;

	/**
	 * @throws RecordNotFound
	 */
	public function getCard(string $name): Card;

	/**
	 * @return Card[]
	 */
	public function searchCards(string $pattern, int $limit = 0): array;

	/**
	 * @return Card[]
	 */
	public function getCards(): array;
}
