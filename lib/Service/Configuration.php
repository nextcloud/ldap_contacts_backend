<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Service;

use OCA\LDAPContactsBackend\AppInfo\Application;
use OCA\LDAPContactsBackend\Exception\ConfigurationNotFound;
use OCA\LDAPContactsBackend\Exception\InvalidConfiguration;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCP\IConfig;
use OCP\Security\ICredentialsManager;
use function json_decode;
use function json_encode;

class Configuration {
	/** @var array<int,ConfigurationModel> */
	protected array $configurations = [];

	public function __construct(
		private readonly IConfig $config,
		private readonly ICredentialsManager $credentialsManager,
	) {
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

		$id = $model->getId();
		if ($id === null || !isset($this->configurations[$id])) {
			throw new ConfigurationNotFound();
		}

		$this->configurations[$id] = $model;
		$this->saveCredentials($model);
		$this->save();
	}

	protected function save(): void {
		$serialized = json_encode($this->configurations);
		$this->config->setAppValue(Application::APPID, 'connections', $serialized);
	}

	protected function ensureLoaded(): void {
		if (empty($this->configurations)) {
			$connections = $this->config->getAppValue(Application::APPID, 'connections', '[]');
			$connections = json_decode($connections, true);
			foreach ($connections as $connection) {
				try {
					$model = $this->modelFromArray($connection);
					$id = $model->getId();
					if ($id === null) {
						continue;
					}

					$this->configurations[$id] = $model;
					$this->loadCredentials($model);
				} catch (InvalidConfiguration) {
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

	private function loadCredentials(ConfigurationModel $model): void {
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
