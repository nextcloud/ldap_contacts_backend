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
	public function __construct(
		protected Image $image,
	) {
		parent::__construct();

		$etag = md5((string)$this->image->data());
		$ext = '.' . explode('/', (string)$this->image->dataMimeType())[1];

		$this->setETag($etag);
		$this->setStatus(Http::STATUS_OK);
		$this->addHeader('Content-Disposition', 'inline; filename="' . $etag . $ext . '"');
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function callback(IOutput $output): void {
		if ($output->getHttpResponseCode() !== Http::STATUS_NOT_MODIFIED) {
			$data = (string)$this->image->data();
			$output->setHeader('Content-Length: ' . strlen($data));
			$output->setOutput($data);
		}
	}
}
