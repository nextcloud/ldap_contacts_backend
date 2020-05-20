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

namespace OCA\LDAPContactsBackend\Controller;

use OCA\DAV\DAV\Sharing\IShareable;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\ConfigurationNotFound;
use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Model\Card;
use OCA\LDAPContactsBackend\Service\AddressBookProvider;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Constants;
use OCP\Contacts\IManager;
use OCP\IAddressBook;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

class ContactsController extends Controller {

	/** @var AddressBookProvider */
	private $addressBookProvider;
	/** @var IManager */
	private $contactsManager;
	/** @var IUserSession */
	private $userSession;
	/** @var ILogger */
	private $logger;
	/** @var IL10N */
	private $l;
	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(
		IRequest $request,
		AddressBookProvider $addressBookProvider,
		IManager $contactsManager,
		IUserSession $userSession,
		ILogger $logger,
		IL10N $l,
		IURLGenerator $urlGenerator
	) {
		parent::__construct(Application::APPID, $request);
		$this->addressBookProvider = $addressBookProvider;
		$this->contactsManager = $contactsManager;
		$this->userSession = $userSession;
		$this->logger = $logger;
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function import(int $sourceId = -1, string $contactId = '') {
		try {
			$addressBook = $this->addressBookProvider->getAddressBookById($sourceId);
			$contact = $addressBook->getChild($contactId);

			$userAddressBooks = $this->contactsManager->getUserAddressBooks();
			/** @var IAddressBook $userAddressBook */
			$fallback = null;
			foreach ($userAddressBooks as $userAddressBook) {
				if(!($userAddressBook->getPermissions() & Constants::PERMISSION_CREATE)) {
					continue;
				}
				if($fallback === null
					&& $userAddressBook instanceof IShareable
					&& $userAddressBook->getOwner() !== $this->userSession->getUser()->getUID()
				) {
					$fallback = $userAddressBook;
					continue;
				}
				if($uri = $this->createCard($userAddressBook, $contact)) {
					return new RedirectResponse($this->getRedirectURL($uri));
				}
			}

			if($fallback instanceof IAddressBook) {
				if($uri = $this->createCard($fallback, $contact)) {
					return new RedirectResponse($this->getRedirectURL($uri));
				}
			}
		} catch (RecordNotFound $e) {
			$this->logger->info(
				'Record with ID {id} not found for importing',
				[
					'app' => Application::APPID,
					'id' => $contactId
				]
			);
			return new NotFoundResponse();
		} catch (ConfigurationNotFound $e) {
			$this->logger->info(
				'LDAP Contacts Backend with ID {id} not found',
				[
					'app' => Application::APPID,
					'id' => $sourceId
				]
			);
			return new NotFoundResponse();
		}
	}

	protected function createCard(IAddressBook $addressBook, Card $card): ?string {
		$data = $card->getData();
		unset($data['URI']); // ensures a new card is created
		$newCard = $addressBook->createOrUpdate($data);
		if(!is_array($newCard)) {
			return null;
		}
		return $newCard['UID'] . '~' . $addressBook->getUri();
	}

	protected function getRedirectURL(string $uri): string {
		return $this->urlGenerator->linkToRoute('contacts.page.indexgroup.contact',
			[
				'group' => $this->l->t('All contacts'),
				'contact' => $uri
			]
		);
	}
}
