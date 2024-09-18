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
	private IActionFactory $actionFactory;
	private IURLGenerator $urlGenerator;
	private IL10N $l;
	private CsrfTokenManager $tokenManager;
	private IAppManager $appManager;

	public function __construct(
		IActionFactory $actionFactory,
		IURLGenerator $urlGenerator,
		IL10N $l,
		CsrfTokenManager $tokenManager,
		IAppManager $appManager,
	) {
		$this->actionFactory = $actionFactory;
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
		$this->tokenManager = $tokenManager;
		$this->appManager = $appManager;
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
