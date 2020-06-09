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

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\PhotoServiceUnavailable;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\Image;

class PhotoService {

	/** @var ICacheFactory */
	private $cacheFactory;

	public function __construct(ICacheFactory $cacheFactory) {
		$this->cacheFactory = $cacheFactory;
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	public function store(string $sourceId, string $entryId, string $imageData): bool {
		$key = $this->getCacheKey($sourceId, $entryId);
		$cache = $this->getCache();

		$knownImage = $cache->get($key);
		if(is_string($knownImage)) {
			$this->getCache()->set($key, $knownImage, 3600);
			return  true;
		}

		$image = $this->prepareImage($imageData);
		$cache->set($key, base64_encode($image->data()), 3600);
		return true;
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	public function retrieve(string $sourceId, string $entryId): Image {
		$key = $this->getCacheKey($sourceId, $entryId);
		$cache = $this->getCache();

		$knownImage = $cache->get($key);
		if(is_string($knownImage)) {
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
		if($this->cacheFactory->isAvailable()) {
			return $this->cacheFactory->createDistributed(Application::APPID . '_PhotoCache');
		}
		if($this->cacheFactory->isLocalCacheAvailable()) {
			return $this->cacheFactory->createLocal(Application::APPID . '_PhotoCache');
		}
		throw new PhotoServiceUnavailable('No cache available');
	}

	/**
	 * @throws PhotoServiceUnavailable
	 */
	protected function prepareImage(string $imageData): Image {
		$image = new Image();
		if(!$image->loadFromBase64($imageData) || !$image->centerCrop(64)) {
			throw new PhotoServiceUnavailable('Image data invalid');
		}
		return $image;
	}

	protected function getCacheKey(string $sourceId, string $entryId): string {
		return hash('sha256', $sourceId . '|' . $entryId);
	}

}
