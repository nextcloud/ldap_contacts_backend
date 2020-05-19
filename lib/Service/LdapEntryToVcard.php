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

use OCA\LDAPContactsBackend\Model\Configuration as ConfigurationModel;
use Symfony\Component\Ldap\Entry;

class LdapEntryToVcard {
	private const DEFAULT_MAPPING = [
		'FN' => 'displayName',
		'URI' => 'dn',
		'EMAIL' => 'mail',
		//'PHOTO:data:image/jpeg;base64,' => 'jpegPhoto',
		'ADR' => 'registeredAddress',
		'TEL' => 'telephoneNumber', // mobile??
		'TITLE' => 'title',
		'ORG' => 'ou',
	];

	static public function convert(Entry $record, ConfigurationModel $configuration): array {
		$vCardData = ['VERSION' => 4.0];
		$mappings = array_merge(self::DEFAULT_MAPPING, $configuration->getAttributeMapping());
		foreach ($mappings as $vcProperty => $lAttributes) {
			$lAttributes = explode(',', $lAttributes);
			foreach ($lAttributes  as $lAttribute) {
				$lAttribute = trim($lAttribute);
				if($lAttribute === 'dn') {
					$vCardData[strtoupper($vcProperty)] = [];
					$vCardData[strtoupper($vcProperty)] = base64_encode($record->getDn());
				} else if ($record->hasAttribute($lAttribute)) {
					$vCardData[strtoupper($vcProperty)] = [];
					foreach ($record->getAttribute($lAttribute) as $value) {
						if(strpos($vcProperty, 'base64') !== false) {
							$value = base64_encode($value);
						}
						$vCardData[strtoupper($vcProperty)][] = $value;
					}
				}
			}
		}
		if(!isset($vCardData['FN'])) {
			throw new \RuntimeException('Invalid record or configuration for vcard');
		}
		// $vCardData['UID'] = $vCardData['URI'];
		return $vCardData;
	}
}
