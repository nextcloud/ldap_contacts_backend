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
