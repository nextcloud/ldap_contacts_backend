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

use OC\Core\Command\Base;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCA\LDAPContactsBackend\Service\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListConfigs extends Base {
	private Configuration $configurationService;

	public function __construct(Configuration $configurationService) {
		parent::__construct();
		$this->configurationService = $configurationService;
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
