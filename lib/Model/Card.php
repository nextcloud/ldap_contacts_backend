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

namespace OCA\LDAPContactsBackend\Model;

use Sabre\CardDAV\ICard;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\VObject\Component\VCard;

class Card implements ICard {

	/** @var array */
	private $vCardData;

	public function __construct(array $vCardData) {
		$this->vCardData = $vCardData;
	}

	/**
	 * @inheritDoc
	 */
	public function put($data) {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	public function get() {
		return (new VCard($this->vCardData))->serialize();
	}

	public function getData(): array {
		return $this->vCardData;
	}

	/**
	 * @inheritDoc
	 */
	public function getContentType() {
		return 'text/vcard; charset=utf-8';
	}

	/**
	 * @inheritDoc
	 */
	public function getETag() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getSize() {
		return \strlen($this->get());
	}

	/**
	 * @inheritDoc
	 */
	public function delete() {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->vCardData['URI'];
	}

	/**
	 * @inheritDoc
	 */
	public function setName($name) {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	public function getLastModified() {
		return null;
	}
}
