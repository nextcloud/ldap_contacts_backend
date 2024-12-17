<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use Exception;
use Generator;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\RecordNotFound;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Ldap;

class LdapQuerent {
	protected ?Ldap $ldap = null;

	public function __construct(
		private readonly ConfigurationModel $configuration,
		private readonly LoggerInterface $logger,
	) {
	}

	/**
	 * @throws RecordNotFound
	 */
	public function fetchOne(string $dn): Entry {
		$ldap = $this->getClient();
		$query = $ldap->query($dn, $this->configuration->getFilter(), ['scope' => QueryInterface::SCOPE_BASE]);
		$collection = $query->execute();
		try {
			foreach ($collection->getIterator() as $record) {
				return $record;
			}
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), [
				'exception' => $e,
				'app' => Application::APPID,
			]);
		}

		throw new RecordNotFound();
	}

	public function fetchAll(?string $filter = null, int $limit = 0): Generator {
		$ldap = $this->getClient();
		$filter ??= $this->configuration->getFilter();
		$options = ['maxItems' => $limit, 'timeout' => 0];
		if ($limit === 0 || $limit > 500) {
			$options['pageSize'] = 500;
		}

		foreach ($this->configuration->getBases() as $base) {
			$query = $ldap->query($base, $filter, $options);
			$subset = $query->execute()->toArray();
			foreach ($subset as $record) {
				yield $record;
			}
		}
	}

	public function find(string $search, int $limit = 0): Generator {
		try {
			$ldap = $this->getClient();
		} catch (ConnectionException) {
			$this->logger->warning(
				'LDAP server {host}:{port} is unavailable',
				[
					'app' => Application::APPID,
					'host' => $this->configuration->getHost(),
					'port' => $this->configuration->getPort(),
				]
			);

			return;
		}

		$search = $ldap->escape($search);

		$searchFilter = '(|';
		foreach ($this->configuration->getSearchAttributes() as $attribute) {
			$searchFilter .= '(' . $attribute . '=' . $search . '*)';
		}

		$searchFilter .= ')';
		$filter = '(&' . $this->configuration->getFilter() . $searchFilter . ')';

		foreach ($this->fetchAll($filter, $limit) as $record) {
			yield $record;
		}
	}

	/**
	 * @throws Exception
	 */
	protected function getClient(): Ldap {
		if ($this->ldap instanceof Ldap) {
			return $this->ldap;
		}

		$enc = 'none';
		if (in_array($this->configuration->getTEnc(), ['tls', 'ssl'])) {
			$enc = $this->configuration->getTEnc();
		}

		$this->ldap = Ldap::create(
			'ext_ldap',
			[
				'host' => $this->configuration->getHost(),
				'port' => $this->configuration->getPort(),
				'version' => 3,
				'encryption' => $enc
			]
		);

		try {
			$this->ldap->bind(
				$this->configuration->getAgentDn(),
				$this->configuration->getAgentPassword()
			);
		} catch (Exception $e) {
			$this->ldap = null;
			throw $e;
		}

		return $this->ldap;
	}
}
