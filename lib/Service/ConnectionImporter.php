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

use OCA\LDAPContactsBackend\Model\LDAPBaseConfiguration;
use OCA\User_LDAP\Configuration;
use OCA\User_LDAP\Helper;
use OutOfBoundsException;

class ConnectionImporter {
	private Helper $ldapHelper;

	public function __construct(Helper $ldapHelper) {
		$this->ldapHelper = $ldapHelper;
	}

	public function getConnection(string $prefix): LDAPBaseConfiguration {
		$prefixes = $this->ldapHelper->getServerConfigurationPrefixes();
		if (!in_array($prefix, $prefixes)) {
			throw new OutOfBoundsException('Specified configuration not available');
		}

		$c = new Configuration($prefix);
		$m = new LDAPBaseConfiguration();
		$m
			->setPort($c->ldapPort)
			->setBindDn($c->ldapAgentName)
			->setBindPwd($c->ldapAgentPassword)
			->setHost($this->extractHost($c->ldapHost))
			->setTlsMode($this->extractTlsMode($c->ldapHost, $c->ldapPort, $c->ldapTLS));

		return $m;
	}

	/**
	 * @return LDAPBaseConfiguration[]
	 */
	public function getAvailableConnections(): array {
		$connections = [];
		$prefixes = $this->ldapHelper->getServerConfigurationPrefixes();
		$i = 0;
		foreach ($prefixes as $prefix) {
			$c = new Configuration($prefix);
			$m = new LDAPBaseConfiguration();
			$m
				->setPrefix($prefix)
				->setPort($c->ldapPort)
				->setBindDn($c->ldapAgentName)
				->setBindPwd($c->ldapAgentPassword)
				->setHost($this->extractHost($c->ldapHost))
				->setTlsMode($this->extractTlsMode($c->ldapHost, $c->ldapPort, $c->ldapTLS));
			// give disabled configurations a high key, so they will be sorted to the end
			$connections[$i + ((int)!$c->ldapConfigurationActive * 100)] = $m;
			$i++;
		}

		ksort($connections);

		return array_values($connections);
	}

	protected function extractHost(string $host): string {
		return preg_replace('/^ldap[si]?:\/\//', '', $host);
	}

	protected function extractTlsMode(string $host, string $port, string $startTls): string {
		if ($startTls === '1') {
			return 'tls';
		}

		if (str_starts_with($host, 'ldaps://') || $port === '636') {
			return 'ssl';
		}

		return 'none';
	}
}
