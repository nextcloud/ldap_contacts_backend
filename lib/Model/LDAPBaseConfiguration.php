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

class LDAPBaseConfiguration {
	protected $prefix;
	protected $host;
	protected $port;
	protected $tlsMode;
	protected $bindDn;
	protected $bindPwd;


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

	public function setPrefix($prefix): LDAPBaseConfiguration {
		$this->prefix = $prefix;
		return $this;
	}

	public function getPrefix(): string {
		return $this->prefix;
	}
}
