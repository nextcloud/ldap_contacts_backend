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

namespace OCA\LDAPContactsBackend\Model;

use InvalidArgumentException;
use JsonSerializable;
use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\InvalidConfiguration;
use OCP\Server;
use Psr\Log\LoggerInterface;

class Configuration implements JsonSerializable {
	public const PROPERTIES = [
		'id',
		'addressBookDisplayName',
		'host',
		'port',
		'agentDn',
		'agentPassword',
		'bases',
		'searchAttributes',
		'attributeMapping',
		'enabled',
		'tEnc',
		'filter',
	];

	protected array $data = [];

	public function getHost(): string {
		return $this->data['host'] ?? '';
	}

	public function setHost(string $host): Configuration {
		$this->data['host'] = preg_replace('/^ldap(s)?:\/\//', '', $host);
		return $this;
	}

	public function getPort(): int {
		return $this->data['port'] ?? 0;
	}

	public function setPort(int $port): Configuration {
		$this->data['port'] = $port;
		return $this;
	}

	public function getAgentDn(): string {
		return $this->data['agentDn'] ?? '';
	}

	public function setAgentDn(string $agentDn): Configuration {
		$this->data['agentDn'] = $agentDn;
		return $this;
	}

	public function getAgentPassword(): string {
		return $this->data['agentPassword'] ?? '';
	}

	public function setAgentPassword(string $agentPassword): Configuration {
		$this->data['agentPassword'] = $agentPassword;
		return $this;
	}

	public function getBases(): array {
		return $this->data['bases'] ?? [];
	}

	public function setBases(array $bases): Configuration {
		$this->data['bases'] = $bases;
		return $this;
	}

	public function getSearchAttributes(): array {
		return $this->data['searchAttributes'] ?? [];
	}

	public function setSearchAttributes(array $searchAttributes): Configuration {
		$this->data['searchAttributes'] = $searchAttributes;
		return $this;
	}

	public function getAttributeMapping(): array {
		return $this->data['attributeMapping'] ?? [];
	}

	public function setAttributeMapping(array $attributeMapping): Configuration {
		$this->data['attributeMapping'] = $attributeMapping;
		return $this;
	}

	public function setEnabled(bool $enabled): Configuration {
		$this->data['enabled'] = $enabled;
		return $this;
	}

	public function isEnabled(): bool {
		return $this->data['enabled'] ?? false;
	}

	public function getId(): ?int {
		return $this->data['id'] ?? null;
	}

	public function setId(int $id): Configuration {
		$this->data['id'] = $id;
		return $this;
	}

	public function setTEnc(string $tEnc): Configuration {
		$this->data['tEnc'] = $tEnc;
		return $this;
	}

	public function getTEnc(): string {
		return $this->data['tEnc'] ?? '';
	}

	public function getFilter(): string {
		return $this->data['filter'] ?? '';
	}

	public function setFilter(string $filter): Configuration {
		$this->data['filter'] = $filter;
		return $this;
	}

	public function getAddressBookDisplayName(): string {
		return $this->data['addressBookDisplayName'] ?? '';
	}

	public function setAddressBookDisplayName(string $name): Configuration {
		$this->data['addressBookDisplayName'] = trim($name);
		return $this;
	}

	public function jsonSerialize(): array {
		$serializable = $this->data;

		// serialization is for writing into DB in plain text,
		// thus we take out credentials.
		unset($serializable['agentPassword']);
		unset($serializable['agentDn']);

		return $serializable;
	}

	/**
	 * @throws InvalidConfiguration
	 */
	public static function fromArray(array $data): Configuration {
		if (!isset($data['id'])) {
			throw new InvalidConfiguration();
		}

		$model = new Configuration();

		foreach (self::PROPERTIES as $property) {
			try {
				if (!isset($data[$property])) {
					continue;
				}

				$setter = 'set' . ucfirst($property);
				$model->$setter($data[$property]);
			} catch (InvalidArgumentException $e) {
				Server::get(LoggerInterface::class)->info(
					'Ignoring invalid value for {property}, ID {id}',
					[
						'app' => Application::APPID,
						'property' => $property,
						'id' => $data['id'],
					]
				);
			}
		}

		return $model;
	}
}
