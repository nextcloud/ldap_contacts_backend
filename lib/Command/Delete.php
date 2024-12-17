<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Command;

use OC\Core\Command\Base;
use OCA\LDAPContactsBackend\Service\Configuration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Delete extends Base {
	public function __construct(
		private readonly Configuration $configurationService,
	) {
		parent::__construct();
	}

	protected function configure() {
		parent::configure();
		$this
			->setName('ldap_contacts:delete')
			->setDescription('Delete an LDAP contacts backend configuration')
			->addArgument(
				'id',
				InputArgument::REQUIRED,
				'Address book id'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = (int)$input->getArgument('id');
		$this->configurationService->delete($id);

		return 0;
	}
}
