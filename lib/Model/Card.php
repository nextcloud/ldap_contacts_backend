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
	#[\Override]
	public function put($data): never {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function get() {
		return (new VCard($this->vCardData))->serialize();
	}

	public function getData(): array {
		return $this->vCardData;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getContentType() {
		return 'text/vcard; charset=utf-8';
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getETag() {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getSize() {
		return \strlen((string)$this->get());
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function delete(): never {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getName() {
		return $this->vCardData['URI'];
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function setName($name): never {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getLastModified() {
		return null;
	}
}
