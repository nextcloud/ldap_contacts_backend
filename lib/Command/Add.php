<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Command;

use OC\Core\Command\Base;
use OCA\LDAPContactsBackend\Service\Configuration;
use OCA\LDAPContactsBackend\Service\ConnectionImporter;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Add extends Base {
	public function __construct(
		private readonly Configuration $configurationService,
		private readonly ?ConnectionImporter $connectionImporter = null,
	) {
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('ldap_contacts:add')
			->setDescription('Add an LDAP contacts backend configuration')
			->addArgument(
				'addressBookName',
				InputArgument::REQUIRED,
				'Address book display name'
			)
			->addOption(
				'ldapConfiguration',
				null,
				InputOption::VALUE_REQUIRED,
				'Read connections details from a specified LDAP backend'
			)
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

	protected function interact(InputInterface $input, OutputInterface $output) {
		if (!$input->getOption('interactive')) {
			return;
		}

		if ($input->getOption('ldapConfiguration') !== null) {
			$this->importConnection($input);
		}

		if ($input->getArgument('addressBookName') === null) {
			/** @var QuestionHelper $helper */
			$helper = $this->getHelper('question');

			$q = new Question('Address book display name: ');
			$q->setNormalizer(fn (string $input): string => $this->stringNormalizer($input));

			$input->setArgument('addressBookName', $helper->ask($input, $output, $q));
		}

		if ($input->getOption('ldapConfiguration') === null) {
			$this->askImport($input, $output);
		}

		if ($input->getOption('host') === null) {
			$this->askString('host', 'LDAP hostname: ', $input, $output);
		}

		if ($input->getOption('port') === null) {
			$this->askUnsignedInt('port', 'LDAP port: ', $input, $output);
		}

		if ($input->getOption('trans_enc') === null) {
			/** @var QuestionHelper $helper */
			$helper = $this->getHelper('question');

			$q = new Question('Transport encryption: ');
			$q->setAutocompleterValues(['StartTLS', 'LDAPS', 'none']);

			$v = match ($helper->ask($input, $output, $q)) {
				'StartTLS' => 'tls',
				'LDAPS' => 'ssl',
				default => 'none',
			};

			$input->setOption('trans_enc', $v);
		}

		if ($input->getOption('bindDN') === null) {
			$this->askString('bindDN', 'LDAP Bind DN: ', $input, $output);
		}

		if ($input->getOption('bindPwd') === null) {
			$this->askPassword('bindPwd', 'LDAP Bind Password: ', $input, $output);
		}

		if ($input->getOption('filter') === null) {
			$this->askString('filter', 'LDAP contacts filter: ', $input, $output);
		}

		if (empty($input->getOption('base'))) {
			$this->askStrings(
				'base',
				'LDAP contacts base: ',
				'  additional base (leave empty to continue): ',
				$input,
				$output
			);
		}

		if (empty($input->getOption('attrs'))) {
			$this->askStringToArray('attrs', 'LDAP search attributes (comma separated): ', $input, $output);
		}

		if (empty($input->getOption('mapping'))) {
			$this->askStrings(
				'mapping',
				'LDAP CardDAV mapping(example: TEL:mobile,telephoneNumber): ',
				'  additional base (leave empty to continue): ',
				$input,
				$output
			);
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$config = $this->configurationService->add();
		$config->setAddressBookDisplayName($input->getArgument('addressBookName'));

		if ($input->getOption('ldapConfiguration') !== null) {
			$this->importConnection($input);
		}

		$hostSet = false;
		$host = (string)$input->getOption('host');
		if ($host !== '') {
			$config->setHost($host);
			$hostSet = true;
		}

		$port = (int)$input->getOption('port');
		if ($port !== 0) {
			$config->setPort($port);
		}

		if ($input->getOption('trans_enc') !== null) {
			$config->setTEnc($input->getOption('trans_enc'));
		}

		if ($input->getOption('bindDN') !== null) {
			$config->setAgentDn($input->getOption('bindDN'));
		}

		if ($input->getOption('bindPwd') !== null) {
			$config->setAgentPassword($input->getOption('bindPwd'));
		}

		if ($input->getOption('filter') !== null) {
			$config->setFilter($input->getOption('filter'));
		}

		if ($input->getOption('attrs') !== null) {
			$config->setSearchAttributes($input->getOption('attrs'));
		}

		if ($input->getOption('base') !== null) {
			$config->setBases($input->getOption('base'));
		}

		if (is_array($input->getOption('mapping'))) {
			$mappings = [];
			foreach ($input->getOption('mapping') as $pair) {
				[$property, $attributes] = explode(':', $pair);
				$mappings[$property] = $attributes;
			}

			$config->setAttributeMapping($mappings);
		}

		if (!$input->hasOption('disabled') && $hostSet) {
			$config->setEnabled(true);
		}

		$this->configurationService->update($config);

		return 0;
	}

	protected function importConnection(InputInterface $input) {
		static $wasRun = false;
		if ($wasRun || !$this->connectionImporter instanceof ConnectionImporter) {
			// avoid running twice during interact && executed
			return;
		}

		$connection = $this->connectionImporter->getConnection($input->getOption('ldapConfiguration'));
		if ($input->getOption('host') === null) {
			$input->setOption('host', $connection->getHost());
		}

		if ($input->getOption('port') === null) {
			$input->setOption('port', $connection->getPort());
		}

		if ($input->getOption('trans_enc') === null) {
			$input->setOption('trans_enc', $connection->getTlsMode());
		}

		if ($input->getOption('bindDN') === null) {
			$input->setOption('bindDN', $connection->getBindDn());
		}

		if ($input->getOption('bindPwd') === null) {
			$input->setOption('bindPwd', $connection->getBindPwd());
		}

		$wasRun = true;
	}

	protected function askImport(InputInterface $input, OutputInterface $output): void {
		$availableConnections = $this->connectionImporter ? $this->connectionImporter->getAvailableConnections() : [];
		if (count($availableConnections) === 0) {
			return;
		}

		$list = [];
		foreach ($availableConnections as $connection) {
			$list[] = $connection->getPrefix() . ' ' . $connection->getHost() . ' (Bind with: ' . $connection->getBindDn() . ')';
		}

		$list[] = 'None';

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');
		$question = new ChoiceQuestion(
			'Would you like to use data from an existing connection? (Default: none)',
			$list,
			count($list) - 1
		);
		$choice = (string)$helper->ask($input, $output, $question);
		if ($choice === 'None') {
			return;
		}

		$chosenPrefix = substr($choice, 0, strpos($choice, ' '));
		$input->setOption('ldapConfiguration', $chosenPrefix);
		$this->importConnection($input);
	}

	protected function askString(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setNormalizer(fn ($input): string => $this->stringNormalizer($input));

		$input->setOption($subject, $helper->ask($input, $output, $q));
	}

	protected function askStringToArray(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setNormalizer(fn ($input): string => $this->stringNormalizer($input));
		$values = array_map('trim', explode(',', (string)$helper->ask($input, $output, $q)));

		$input->setOption($subject, $values);
	}

	protected function askPassword(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setHidden(true);

		$input->setOption($subject, $helper->ask($input, $output, $q));
	}

	protected function askUnsignedInt(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setNormalizer(fn ($input): ?int => $this->posNumberNormalizer($input));

		$input->setOption($subject, $helper->ask($input, $output, $q));
	}

	protected function stringNormalizer(?string $input): string {
		return ($input !== null) ? trim($input) : '';
	}

	protected function askStrings(string $subject, string $label, string $followUpLabel, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$values = [];
		$isFollowUp = false;

		$q = new Question($label);
		$q->setNormalizer(fn ($input): string => $this->stringNormalizer($input));

		while (($value = $helper->ask($input, $output, $q)) !== '') {
			$values[] = $value;
			if (!$isFollowUp) {
				$q = new Question($followUpLabel);
				$q->setNormalizer(fn ($input): string => $this->stringNormalizer($input));
				$isFollowUp = true;
			}
		}

		$input->setOption($subject, $values);
	}

	protected function posNumberNormalizer(?string $input): ?int {
		if (is_string($input)) {
			$input = (int)$input;
		}

		if (is_int($input) && $input < 0) {
			throw new RuntimeException('Port must not be negative');
		}

		return $input;
	}
}
