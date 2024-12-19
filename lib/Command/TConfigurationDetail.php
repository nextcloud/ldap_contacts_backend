<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Command;

use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

trait TConfigurationDetail {
	public function configureOptions(): void {
		if (!$this instanceof Command) {
			throw new LogicException('Trait applied on wrong base class');
		}

		$this
			->addOption(
				'host',
				null,
				InputOption::VALUE_REQUIRED,
				'LDAP host name'
			)
			->addOption(
				'port',
				null,
				InputOption::VALUE_REQUIRED,
				'LDAP Port'
			)
			->addOption(
				'trans_enc',
				null,
				InputOption::VALUE_REQUIRED,
				'Transport encryption'
			)
			->addOption(
				'bindDN',
				null,
				InputOption::VALUE_REQUIRED,
				'LDAP Bind DN'
			)
			->addOption(
				'bindPwd',
				null,
				InputOption::VALUE_REQUIRED,
				'LDAP Bind Password'
			)
			->addOption(
				'filter',
				null,
				InputOption::VALUE_REQUIRED,
				'LDAP filter'
			)
			->addOption(
				'base',
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'LDAP contacts bases'
			)
			->addOption(
				'attrs',
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'LDAP Search Attributes',
				null
			)
			->addOption(
				'mapping',
				null,
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'LDAP to VCard mapping (e.g. --mapping=EMAIL:mail --mapping=TEL:mobile,homePhone)',
				null
			)
			->addOption(
				'disable',
				null,
				InputOption::VALUE_NONE,
				'keep configuration disabled'
			)
			->addOption(
				'interactive',
				'i',
				InputOption::VALUE_NONE,
				'Interactive mode'
			);
	}
}
