<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Command;

use OC\Core\Command\Base;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCA\LDAPContactsBackend\Service\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListConfigs extends Base {
	public function __construct(
		private readonly Configuration $configurationService,
	) {
		parent::__construct();
	}

	protected function configure() {
		parent::configure();
		$this
			->setName('ldap_contacts:list')
			->setDescription('Lists all LDAP contacts backend configurations');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$configs = $this->configurationService->getAll();
		foreach ($configs as $config) {
			/** @var ConfigurationModel $config */
			$cfgValues = [];
			foreach (ConfigurationModel::PROPERTIES as $property) {
				$getter = 'get' . ucfirst($property);
				if (method_exists($config, $getter)) {
					$cfgValues[$property] = $config->$getter();
				}
			}

			$this->writeMixedInOutputFormat($input, $output, $cfgValues);
		}

		return 0;
	}
}
