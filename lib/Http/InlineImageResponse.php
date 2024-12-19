<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Http;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ICallbackResponse;
use OCP\AppFramework\Http\IOutput;
use OCP\AppFramework\Http\Response;
use OCP\Image;

/**
 * @template-extends Response<Http::STATUS_OK,array{}>
 */
class InlineImageResponse extends Response implements ICallbackResponse {
	protected Image $image;

	public function __construct(Image $image) {
		parent::__construct();

		$etag = md5((string)$image->data());
		$ext = '.' . explode('/', (string)$image->dataMimeType())[1];

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
			$output->setHeader('Content-Length: ' . strlen((string)$this->image->data()));
			$output->setOutput($this->image->data());
		}
	}
}
