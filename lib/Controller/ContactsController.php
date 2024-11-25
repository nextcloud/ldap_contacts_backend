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
use OCA\LDAPContactsBackend\Exception\PhotoServiceUnavailable;
use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Http\InlineImageResponse;
use OCA\LDAPContactsBackend\Model\Card;
use OCA\LDAPContactsBackend\Service\AddressBookProvider;
use OCA\LDAPContactsBackend\Service\PhotoService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\Constants;
use OCP\Contacts\IManager;
use OCP\IAddressBook;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use Psr\Log\LoggerInterface;

class ContactsController extends Controller {
	public function __construct(
		IRequest $request,
		private readonly AddressBookProvider $addressBookProvider,
		private readonly IManager $contactsManager,
		private readonly IUserSession $userSession,
		private readonly LoggerInterface $logger,
		private readonly IURLGenerator $urlGenerator,
		private readonly PhotoService $photoService,
		private readonly IFactory $l10nFactory,
	) {
		parent::__construct(Application::APPID, $request);
	}

	public function import(int $sourceId = -1, string $contactId = ''): Response {
		try {
			$addressBook = $this->addressBookProvider->getAddressBookById($sourceId);
			$contact = $addressBook->getChild($contactId);

			$userAddressBooks = $this->contactsManager->getUserAddressBooks();
			/** @var IAddressBook $userAddressBook */
			$fallback = null;
			foreach ($userAddressBooks as $userAddressBook) {
				if (!($userAddressBook->getPermissions() & Constants::PERMISSION_CREATE)) {
					continue;
				}

				if ($fallback === null
					&& $userAddressBook instanceof IShareable
					&& $userAddressBook->getOwner() !== $this->userSession->getUser()->getUID()
				) {
					$fallback = $userAddressBook;
					continue;
				}

				if ($uri = $this->createCard($userAddressBook, $contact)) {
					return new RedirectResponse($this->getRedirectURL($uri));
				}
			}

			if ($fallback instanceof IAddressBook) {
				if ($uri = $this->createCard($fallback, $contact)) {
					return new RedirectResponse($this->getRedirectURL($uri));
				}
			}
		} catch (RecordNotFound) {
			$this->logger->info(
				'Record with ID {id} not found for importing',
				[
					'app' => Application::APPID,
					'id' => $contactId
				]
			);

			return new NotFoundResponse();
		} catch (ConfigurationNotFound) {
			$this->logger->info(
				'LDAP Contacts Backend with ID {id} not found',
				[
					'app' => Application::APPID,
					'id' => $sourceId
				]
			);

			return new NotFoundResponse();
		}

		// for the unlikely case reply with 4xx value
		$response = new Response();
		$response->setStatus(Http::STATUS_CONFLICT);

		return $response;
	}

	public function photo(int $sourceId = -1, string $contactId = ''): Response {
		try {
			$image = $this->photoService->retrieve((string)$sourceId, $contactId);
			return new InlineImageResponse($image);
		} catch (PhotoServiceUnavailable $e) {
			$this->logger->info(
				'Photo could not be retrieved, reason: {msg}',
				[
					'app' => Application::APPID,
					'msg' => $e->getMessage(),
				]
			);

			return new NotFoundResponse();
		}
	}

	protected function createCard(IAddressBook $addressBook, Card $card): ?string {
		$data = $card->getData();
		unset($data['URI']); // ensures a new card is created
		$newCard = $addressBook->createOrUpdate($data);
		if (!is_array($newCard)) {
			return null;
		}

		return $newCard['UID'] . '~' . $addressBook->getUri();
	}

	protected function getRedirectURL(string $uri): string {
		$contactsL10N = $this->l10nFactory->get('contacts');
		return $this->urlGenerator->linkToRoute('contacts.page.indexgroup.contact',
			[
				'group' => $contactsL10N->t('All contacts'),
				'contact' => $uri
			]
		);
	}
}
