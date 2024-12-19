<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LDAPContactsBackend\Model;

class LDAPBaseConfiguration {
	protected string $prefix;
	protected string $host;
	protected string $port;
	protected string $tlsMode;
	protected string $bindDn;
	protected string $bindPwd;


	public function getBindPwd(): string {
		return $this->bindPwd;
	}

	public function setBindPwd(string $bindPwd): LDAPBaseConfiguration {
		$this->bindPwd = $bindPwd;
		return $this;
	}

	public function getBindDn(): string {
		return $this->bindDn;
	}

	public function setBindDn(string $bindDn): LDAPBaseConfiguration {
		$this->bindDn = $bindDn;
		return $this;
	}

	public function getTlsMode(): string {
		return $this->tlsMode;
	}

	public function setTlsMode(string $tlsMode): LDAPBaseConfiguration {
		$this->tlsMode = $tlsMode;
		return $this;
	}

	public function getPort(): string {
		return $this->port;
	}

	public function setPort(string $port): LDAPBaseConfiguration {
		$this->port = $port;
		return $this;
	}

	public function getHost(): string {
		return $this->host;
	}

	public function setHost(string $host): LDAPBaseConfiguration {
		$this->host = $host;
		return $this;
	}

	public function setPrefix(string $prefix): LDAPBaseConfiguration {
		$this->prefix = $prefix;
		return $this;
	}

	public function getPrefix(): string {
		return $this->prefix;
	}
}
