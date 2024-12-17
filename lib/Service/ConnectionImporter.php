<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\Model\LDAPBaseConfiguration;
use OCA\User_LDAP\Configuration;
use OCA\User_LDAP\Helper;
use OutOfBoundsException;

class ConnectionImporter {
	private readonly Helper $ldapHelper;

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
