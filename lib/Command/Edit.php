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

use Generator;
use OC\Core\Command\Base;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCA\LDAPContactsBackend\Service\Configuration;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Edit extends Base {
	use TConfigurationDetail;

	private Configuration $configurationService;

	public function __construct(Configuration $configurationService) {
		parent::__construct();
		$this->configurationService = $configurationService;
	}

	protected function configure() {
		$this
			->setName('ldap_contacts:edit')
			->setDescription('Edit an LDAP contacts backend configuration')
			->addArgument(
				'id',
				InputArgument::REQUIRED,
				'Address book configuration id'
			)
			->addOption(
				'addressBookName',
				null,
				InputOption::VALUE_REQUIRED,
				'Address book display name'
			);
		$this->configureOptions();
	}

	protected function getListOfOptions(ConfigurationModel $model): Generator {
		yield [
			'key' => 'addressBookName',
			'type' => 'string',
			'currentLabel' => sprintf('Address book display name: %s.', $model->getAddressBookDisplayName()),
			'newLabel' => '  New address book display name: ',
			'setter' => function ($v) use ($model) {
				return $model->setAddressBookDisplayName($v);
			},
		];
		yield [
			'key' => 'host',
			'type' => 'string',
			'currentLabel' => sprintf('LDAP hostname: %s.', $model->getHost()),
			'newLabel' => '  New LDAP hostname: ',
			'setter' => function ($v) use ($model) {
				return $model->setHost($v);
			},
		];
		yield [
			'key' => 'port',
			'type' => 'uint',
			'currentLabel' => sprintf('LDAP port: %u.', $model->getPort()),
			'newLabel' => '  New LDAP port: ',
			'setter' => function ($v) use ($model) {
				return $model->setPort($v);
			},
		];
		yield [
			'key' => 'trans_enc',
			'type' => 'string',
			'currentLabel' => sprintf('Transport encryption: %s.', $model->getTEnc()),
			'newLabel' => '  New transport encryption (StartTLS, LDAPS, none): ',
			'autoComplete' => ['tls' => 'StartTLS', 'ssl' => 'LDAPS', 'none' => 'none'],
			'setter' => function ($v) use ($model) {
				return $model->setTEnc($v);
			},
		];
		yield [
			'key' => 'bindDN',
			'type' => 'string',
			'currentLabel' => sprintf('LDAP bind DN: %s.', $model->getAgentDn()),
			'newLabel' => '  New LDAP bind DN: ',
			'setter' => function ($v) use ($model) {
				return $model->setAgentDn($v);
			},
		];
		yield [
			'key' => 'bindPwd',
			'type' => 'string',
			'currentLabel' => 'LDAP bind password.',
			'newLabel' => '  New LDAP bind password: ',
			'setter' => function ($v) use ($model) {
				return $model->setAgentPassword($v);
			},
		];
		yield [
			'key' => 'filter',
			'type' => 'string',
			'currentLabel' => sprintf('LDAP contacts filter: %s.', $model->getFilter()),
			'newLabel' => '  New LDAP contacts filter: ',
			'setter' => function ($v) use ($model) {
				return $model->setFilter($v);
			},
		];
		yield [
			'key' => 'base',
			'type' => 'array-string',
			'currentLabel' => sprintf('LDAP contacts bases: %s.', implode('; ', $model->getBases())),
			'newLabel' => '  New LDAP contacts bases: ',
			'followUpLabel' => '  additional base (leave empty to continue): ',
			'setter' => function ($v) use ($model) {
				return $model->setBases($v);
			},
		];
		yield [
			'key' => 'attrs',
			'type' => 'cs-string',
			'currentLabel' => sprintf('LDAP contacts search attributes: %s.', implode(', ', $model->getSearchAttributes())),
			'newLabel' => '  New LDAP search attributes (comma separated): ',
			'setter' => function ($v) use ($model) {
				return $model->setSearchAttributes($v);
			},
		];
		yield [
			'key' => 'mapping',
			'type' => 'array-string',
			'currentLabel' => sprintf('LDAP CardDAV mapping: %s.', implode('; ', $model->getAttributeMapping())),
			'newLabel' => '  New mapping (example: TEL:mobile,telephoneNumber): ',
			'followUpLabel' => '  additional mapping (leave empty to continue): ',
			'setter' => function ($v) use ($model) {
				$mappings = [];
				foreach ($v as $pair) {
					list($property, $attributes) = explode(':', $pair);
					$mappings[$property] = $attributes;
				}
				return $model->setAttributeMapping($mappings);
			},
		];
	}

	protected function interact(InputInterface $input, OutputInterface $output) {
		if (!$input->getOption('interactive')) {
			return;
		}

		$model = $this->configurationService->get((int)$input->getArgument('id'));
		foreach ($this->getListOfOptions($model) as $questionData) {
			if (empty($input->getOption($questionData['key']))) {
				$wantEdit = $this->askWantChangeField($questionData['currentLabel'], $input, $output);
				if ($wantEdit) {
					switch ($questionData['type']) {
						case 'string':
							$this->askString($questionData['key'], $questionData['newLabel'], $input, $output, $questionData['autoComplete'] ?? null);
							continue 2;
						case 'uint':
							$this->askUInt($questionData['key'], $questionData['newLabel'], $input, $output);
							continue 2;
						case 'array-string':
							$this->askStrings($questionData['key'], $questionData['newLabel'], $questionData['followUpLabel'], $input, $output);
							continue 2;
						case 'cs-string':
							$this->askStringToArray($questionData['key'], $questionData['newLabel'], $input, $output);
							continue 2;
					}
				}
			}
		}
		//TODO FIXME: mappings are not being asked for
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$config = $this->configurationService->get((int)$input->getArgument('id'));

		foreach ($this->getListOfOptions($config) as $optionData) {
			if (!empty($input->getOption($optionData['key']))) {
				$v = match ($optionData['type']) {
					'uint' => max((int)$input->getOption($optionData['key']), 0),
					default => $input->getOption($optionData['key']),
				};
				$optionData['setter']($v);
			}
		}

		if (!$input->hasOption('disabled')) {
			$config->setEnabled(true);
		}

		$this->configurationService->update($config);
		return 0;
	}

	private function yesOrNoNormalizer(string $input): ?bool {
		$input = strtolower($input);
		if ($input === 'y') {
			return true;
		}
		if ($input === 'n' || $input === '') {
			return false;
		}
		return null;
	}

	private function stringNormalizer(?string $input): string {
		return $input ? trim($input) : '';
	}

	private function autoCompleteNormalizer($input, array $autoComplete) {
		return array_change_key_case(array_flip($autoComplete))[strtolower($input)] ?? array_pop($autoComplete);
	}

	private function uIntNormalizer(?string $input): ?int {
		if (is_string($input)) {
			$input = (int)$input;
		}
		if (is_int($input) && $input < 0) {
			throw new RuntimeException('Port must not be negative');
		}
		return $input;
	}

	private function askString(string $subject, string $label, InputInterface $input, OutputInterface $output, array $autoComplete = null): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		if (is_array($autoComplete)) {
			$q->setAutocompleterValues(array_values($autoComplete));
			$q->setNormalizer(function ($input) use ($autoComplete) {
				return $this->autoCompleteNormalizer($input, $autoComplete);
			});
		} else {
			$q->setNormalizer(function ($input) {
				return $this->stringNormalizer($input);
			});
		}

		$input->setOption($subject, $helper->ask($input, $output, $q));
	}

	private function askUInt(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setNormalizer(function ($input) {
			return $this->uIntNormalizer($input);
		});

		$input->setOption($subject, $helper->ask($input, $output, $q));
	}

	private function askStrings(string $subject, string $label, string $followUpLabel, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$values = [];
		$isFollowUp = false;

		$q = new Question($label);
		$q->setNormalizer(function ($input) {
			return $this->stringNormalizer($input);
		});

		while (($value = $helper->ask($input, $output, $q)) !== '') {
			$values[] = $value;
			if (!$isFollowUp) {
				$q = new Question($followUpLabel);
				$q->setNormalizer(function ($input) {
					return $this->stringNormalizer($input);
				});
				$isFollowUp = true;
			}
		}

		$input->setOption($subject, $values);
	}

	private function askStringToArray(string $subject, string $label, InputInterface $input, OutputInterface $output): void {
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$q = new Question($label);
		$q->setNormalizer(function ($input) {
			return $this->stringNormalizer($input);
		});
		$values = array_map('trim', explode(',', $helper->ask($input, $output, $q)));

		$input->setOption($subject, $values);
	}

	private function askWantChangeField(string $label, InputInterface $input, OutputInterface $output) {
		do {
			/** @var QuestionHelper $helper */
			$helper = $this->getHelper('question');

			$q = new Question($label . ' Modify (y/N)?  ');
			$q->setNormalizer(function ($input) {
				return $this->yesOrNoNormalizer($input ?? 'N');
			});

			$wantEdit = $helper->ask($input, $output, $q);
		} while ($wantEdit === null);
		return $wantEdit;
	}
}
