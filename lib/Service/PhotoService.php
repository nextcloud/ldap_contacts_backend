<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\PhotoServiceUnavailable;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\Image;

class PhotoService {
	public function __construct(
		private readonly ICacheFactory $cacheFactory,
	) {
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	public function store(string $sourceId, string $entryId, string $imageData): bool {
		$key = $this->getCacheKey($sourceId, $entryId);
		$cache = $this->getCache();

		$knownImage = $cache->get($key);
		if (is_string($knownImage)) {
			$this->getCache()->set($key, $knownImage, 3600);
			return  true;
		}

		$image = $this->prepareImage($imageData);
		$cache->set($key, base64_encode((string)$image->data()), 3600);

		return true;
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	public function retrieve(string $sourceId, string $entryId): Image {
		$key = $this->getCacheKey($sourceId, $entryId);
		$cache = $this->getCache();

		$knownImage = $cache->get($key);
		if (is_string($knownImage)) {
			$image = new Image();
			$image->loadFromBase64($knownImage);

			return $image;
		}

		throw new PhotoServiceUnavailable('Photo not cached');
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	protected function getCache(): ICache {
		if ($this->cacheFactory->isAvailable()) {
			return $this->cacheFactory->createDistributed(Application::APPID . '_PhotoCache');
		}

		if ($this->cacheFactory->isLocalCacheAvailable()) {
			return $this->cacheFactory->createLocal(Application::APPID . '_PhotoCache');
		}

		throw new PhotoServiceUnavailable('No cache available');
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	protected function prepareImage(string $imageData): Image {
		$image = new Image();
		if (!$image->loadFromBase64($imageData) || !$image->centerCrop(64)) {
			throw new PhotoServiceUnavailable('Image data invalid');
		}

		return $image;
	}

	protected function getCacheKey(string $sourceId, string $entryId): string {
		return hash('sha256', $sourceId . '|' . $entryId);
	}
}
