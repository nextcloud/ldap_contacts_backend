<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Model;

use Sabre\CardDAV\ICard;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\VObject\Component\VCard;

class Card implements ICard {
	public function __construct(
		private array $vCardData,
	) {
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
		return \strlen((string)$this->get());
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
