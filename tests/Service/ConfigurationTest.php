<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Tests\phpunit\Service;

use OCA\LDAPContactsBackend\Exception\ConfigurationNotFound;
use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use OCA\LDAPContactsBackend\Service\Configuration;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Security\ICredentialsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ConfigurationTest extends TestCase {
	private Configuration $configurationService;

	private IAppConfig&MockObject $appConfigMock;
	private ICredentialsManager&MockObject $credentialsManagerMock;

	public function setUp(): void {
		parent::setUp();

		$this->appConfigMock = $this->createMock(IAppConfig::class);
		$this->credentialsManagerMock = $this->createMock(ICredentialsManager::class);

		$this->configurationService = new Configuration($this->appConfigMock, $this->credentialsManagerMock);
	}

	protected function prePopulate(&$configs = null): void {
		$c1 = new ConfigurationModel();
		$c2 = new ConfigurationModel();

		$c1->setId(1)->setAgentPassword('')->setAgentDn('');
		$c2->setId(2)->setAgentPassword('')->setAgentDn('');

		$configs = [$c1, $c2];

		$arrays = \json_decode(\json_encode($configs), associative:true);

		$this->appConfigMock->expects($this->once())
			->method('getAppValueArray')
			->with('connections')
			->willReturn($arrays);
	}

	public function testCreate() {
		$this->appConfigMock->expects($this->once())
			->method('getAppValueArray')
			->with('connections')
			->willReturn([]);

		$config = $this->configurationService->add();
		$this->assertInstanceOf(ConfigurationModel::class, $config);
		$this->assertIsInt($config->getId());

		$config2 = $this->configurationService->add();
		$this->assertInstanceOf(ConfigurationModel::class, $config2);
		$this->assertIsInt($config2->getId());
		$this->assertTrue($config2->getId() === ($config->getId() + 1));
	}

	public function testGet() {
		$this->prePopulate($configs);

		// loading both AgentDN and Password for each valid config
		$this->credentialsManagerMock->expects($this->exactly(4))
			->method('retrieve');

		$maxId = 0;
		foreach ($configs as $config) {
			/** @var ConfigurationModel $config */
			$this->assertEquals($config, $this->configurationService->get($config->getId()));
			$maxId = max($maxId, $config->getId());
		}

		$this->expectException(ConfigurationNotFound::class);
		$this->configurationService->get($maxId + 1);
	}

	public function testGetAll() {
		$this->prePopulate($configs);

		// loading both AgentDN and Password for each valid config
		$this->credentialsManagerMock->expects($this->exactly(4))
			->method('retrieve');

		$allConfigs = $this->configurationService->getAll();

		foreach ($configs as $config) {
			/** @var ConfigurationModel $config */
			$this->assertArrayHasKey($config->getId(), $allConfigs);
			$this->assertEquals($config, $allConfigs[$config->getId()]);
		}

		$this->assertNotSame($configs[0], $allConfigs[$configs[1]->getId()]);
		$this->assertNotSame($configs[1], $allConfigs[$configs[0]->getId()]);
	}

	public function testDelete() {
		$this->appConfigMock->expects($this->once())
			->method('getAppValueArray')
			->with('connections')
			->willReturn([]);

		$config = $this->configurationService->add();
		$config2 = $this->configurationService->add();

		$allConfigs = $this->configurationService->getAll();
		$this->assertCount(2, $allConfigs);

		// deleting both AgentDN and Password
		$this->credentialsManagerMock->expects($this->exactly(2))
			->method('delete');

		$this->configurationService->delete($config->getId());
		$allConfigs = $this->configurationService->getAll();
		$this->assertCount(1, $allConfigs);
		$this->assertArrayNotHasKey($config->getId(), $allConfigs);

		$this->expectException(ConfigurationNotFound::class);
		$this->configurationService->delete($config2->getId() + 2);
	}

	public function testUpdate() {
		$this->prePopulate($configs);

		$this->appConfigMock->expects($this->atLeastOnce())
			->method('setAppValueArray')
			->with('connections', $this->anything());

		// updating both AgentDN and Password
		$this->credentialsManagerMock->expects($this->exactly(2))
			->method('store');

		/** @var ConfigurationModel $config */
		$config = array_shift($configs);
		$this->assertEmpty($config->getFilter());
		$config->setFilter('fancyAttribute=foobar');
		$config->setAgentDn('cn=Nxtcld Srvc,ou=Applications,dc=example,dc=io');
		$config->setAgentPassword('135711131719');
		$this->configurationService->update($config);

		$config2 = $this->configurationService->get($config->getId());
		$this->assertSame($config, $config2);
		$this->assertSame($config2->getFilter(), 'fancyAttribute=foobar');
		$this->assertSame($config2->getAgentDn(), 'cn=Nxtcld Srvc,ou=Applications,dc=example,dc=io');
		$this->assertSame($config2->getAgentPassword(), '135711131719');
	}
}
