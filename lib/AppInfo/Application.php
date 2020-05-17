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

namespace OCA\LDAPContactsBackend\AppInfo;

use OCA\LDAPContactsBackend\Service\AddressBookProvider;
use OCP\AppFramework\App;
use OCP\Contacts\IManager;

class Application extends App {
	public const APPID = 'ldap_contacts_backend';

	public function __construct() {
		parent::__construct(self::APPID);
		$this->registerListeners();
	}

	private function registerListeners(): void {
		$cm = $this->getContainer()->getServer()->getContactsManager();
		$cm->register(function() use ($cm) {
			$this->registerAddressBook($cm);
		});
	}

	private function registerAddressBook(IManager $cm) {
		/** @var AddressBookProvider $provider */
		$provider = $this->getContainer()->query(AddressBookProvider::class);
		foreach ($provider->fetchAllForContactsStore() as $ab) {
			$cm->registerAddressBook($ab);
		}
	}
}
