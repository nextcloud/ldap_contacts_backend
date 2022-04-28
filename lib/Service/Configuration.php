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

use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\ConfigurationNotFound;
use OCA\LDAPContactsBackend\Exception\InvalidConfiguration;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCP\IConfig;
use OCP\Security\ICredentialsManager;

class Configuration {
	/** @var ConfigurationModel[] */
	protected $configurations = [];
	/** @var IConfig */
	private $config;
	/** @var ICredentialsManager */
	private $credentialsManager;

	public function __construct(IConfig $config, ICredentialsManager $credentialsManager) {
		$this->config = $config;
		$this->credentialsManager = $credentialsManager;
	}

	public function add(): ConfigurationModel {
		$this->ensureLoaded();

		$newId = !empty($this->configurations) ? max(array_keys($this->configurations)) + 1 : 0;
		$model = new ConfigurationModel();
		$model->setId($newId);

		$this->configurations[$newId] = $model;
		$this->save();

		return $model;
	}

	/**
	 * @throws ConfigurationNotFound
	 */
	public function get(int $id): ConfigurationModel {
		$this->ensureLoaded();

		if (isset($this->configurations[$id])) {
			return $this->configurations[$id];
		}

		throw new ConfigurationNotFound();
	}

	public function getAll(): array {
		$this->ensureLoaded();
		return $this->configurations;
	}

	/**
	 * @throws ConfigurationNotFound
	 */
	public function delete(int $id): void {
		$this->ensureLoaded();

		if (!isset($this->configurations[$id])) {
			throw new ConfigurationNotFound();
		}

		$this->deleteCredentials($this->configurations[$id]);
		unset($this->configurations[$id]);
		$this->save();
	}

	/**
	 * @throws ConfigurationNotFound
	 */
	public function update(ConfigurationModel $model): void {
		$this->ensureLoaded();

		if (!isset($this->configurations[$model->getId()])) {
			throw new ConfigurationNotFound();
		}

		$this->configurations[$model->getId()] = $model;
		$this->saveCredentials($model);
		$this->save();
	}

	protected function save(): void {
		$serialized = \json_encode($this->configurations);
		$this->config->setAppValue(Application::APPID, 'connections', $serialized);
	}

	protected function ensureLoaded(): void {
		if (empty($this->configurations)) {
			$connections = $this->config->getAppValue(Application::APPID, 'connections', '[]');
			$connections = \json_decode($connections, true);
			foreach ($connections as $connection) {
				try {
					$model = $this->modelFromArray($connection);
					$id = $model->getId();
					$this->configurations[$id] = $model;
					$this->loadCredentials($model);
				} catch (InvalidConfiguration $e) {
					continue;
				}
			}
		}
	}

	/**
	 * @throws InvalidConfiguration
	 */
	protected function modelFromArray(array $configArray): ConfigurationModel {
		return ConfigurationModel::fromArray($configArray);
	}

	private function loadCredentials(ConfigurationModel $model) {
		$model->setAgentDN((string)$this->credentialsManager->retrieve(
			'',
			$this->getCredentialsDNKey($model->getId())
		));
		$model->setAgentPassword((string)$this->credentialsManager->retrieve(
			'',
			$this->getCredentialsPwdKey($model->getId())
		));
	}

	private function saveCredentials(ConfigurationModel $model): void {
		$this->credentialsManager->store(
			'',
			$this->getCredentialsDNKey($model->getId()),
			$model->getAgentDN()
		);
		$this->credentialsManager->store(
			'',
			$this->getCredentialsPwdKey($model->getId()),
			$model->getAgentPassword()
		);
	}

	private function deleteCredentials(ConfigurationModel $model): void {
		$this->credentialsManager->delete('', $this->getCredentialsDNKey($model->getId()));
		$this->credentialsManager->delete('', $this->getCredentialsPwdKey($model->getId()));
	}

	private function getCredentialsDNKey(int $id): string {
		return Application::APPID . '::' . $id . '::AgentDN';
	}

	private function getCredentialsPwdKey(int $id): string {
		return Application::APPID . '::' . $id . '::AgentPassword';
	}
}
