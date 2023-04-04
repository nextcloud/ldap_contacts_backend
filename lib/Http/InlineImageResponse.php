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

namespace OCA\LDAPContactsBackend\Http;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ICallbackResponse;
use OCP\AppFramework\Http\IOutput;
use OCP\AppFramework\Http\Response;
use OCP\Image;

class InlineImageResponse extends Response implements ICallbackResponse {
	protected Image $image;

	public function __construct(Image $image) {
		parent::__construct();

		$etag = md5($image->data());
		$ext = '.' . explode('/', $image->dataMimeType())[1];

		$this->setETag($etag);
		$this->setStatus(Http::STATUS_OK);
		$this->addHeader('Content-Disposition', 'inline; filename="' . $etag . $ext . '"');

		$this->image = $image;
	}

	/**
	 * @inheritDoc
	 */
	public function callback(IOutput $output) {
		if ($output->getHttpResponseCode() !== Http::STATUS_NOT_MODIFIED) {
			$output->setHeader('Content-Length: ' . strlen($this->image->data()));
			$output->setOutput($this->image->data());
		}
	}
}
