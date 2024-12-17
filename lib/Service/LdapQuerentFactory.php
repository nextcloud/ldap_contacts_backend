<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use Psr\Log\LoggerInterface;

class LdapQuerentFactory {
	public function __construct(
		private readonly LoggerInterface $logger,
	) {
	}

	public function get(ConfigurationModel $model): LdapQuerent {
		return new LdapQuerent($model, $this->logger);
	}
}
