<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OC\Security\CSRF\CsrfTokenManager;
use OCP\IConfig;
use OCP\IURLGenerator;

class ContactsAddressBookFactory {
	private readonly CsrfTokenManager $tokenManager;

	public function __construct(
		private readonly IConfig $config,
		private readonly IURLGenerator $urlGenerator,
		CsrfTokenManager $tokenManager,
		private readonly PhotoService $photoService,
	) {
		$this->tokenManager = $tokenManager;
	}

	public function get(ICardBackend $cardBackend): ContactsAddressBook {
		return new ContactsAddressBook(
			$cardBackend,
			$this->config,
			$this->urlGenerator,
			$this->tokenManager,
			$this->photoService
		);
	}
}
