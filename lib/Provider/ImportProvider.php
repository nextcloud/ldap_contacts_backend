<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Provider;

use OC\Security\CSRF\CsrfTokenManager;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Service\ContactsAddressBook;
use OCP\App\IAppManager;
use OCP\Contacts\ContactsMenu\IActionFactory;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\Contacts\ContactsMenu\IProvider;
use OCP\IL10N;
use OCP\IURLGenerator;

class ImportProvider implements IProvider {
	private readonly CsrfTokenManager $tokenManager;

	public function __construct(
		private readonly IActionFactory $actionFactory,
		private readonly IURLGenerator $urlGenerator,
		private readonly IL10N $l,
		CsrfTokenManager $tokenManager,
		private readonly IAppManager $appManager,
	) {
		$this->tokenManager = $tokenManager;
	}

	public function process(IEntry $entry): void {
		if (!$this->appManager->isEnabledForUser('contacts')) {
			return;
		}

		$configId = $entry->getProperty(ContactsAddressBook::DAV_PROPERTY_SOURCE);
		if ($configId === null) {
			return;
		}

		$action = $this->actionFactory->newLinkAction(
			$this->urlGenerator->imagePath('core', 'places/contacts.svg'),
			$this->l->t('Copy to address book'),
			$this->urlGenerator->linkToRoute(Application::APPID . '.contacts.import',
				[
					'sourceId' => (int)$configId,
					'contactId' => $entry->getProperty('URI'),
					'requesttoken' => $this->tokenManager->getToken()->getEncryptedValue()
				])
		);

		$entry->addAction($action);
	}
}
