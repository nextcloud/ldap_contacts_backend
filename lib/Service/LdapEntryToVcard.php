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
use OCP\Image;
use RuntimeException;
use Symfony\Component\Ldap\Entry;

class LdapEntryToVcard {
	private const DEFAULT_MAPPING = [
		'FN' => 'displayName',
		'EMAIL' => 'mail',
		'PHOTO' => 'jpegPhoto',
		'ADR' => 'registeredAddress',
		'TEL' => 'telephoneNumber', // mobile??
		'TITLE' => 'title',
		'ORG' => 'ou',
	];

	public static function convert(Entry $record, ConfigurationModel $configuration): array {
		$vCardData = ['VERSION' => '4.0'];
		$mappings = array_merge(self::DEFAULT_MAPPING, $configuration->getAttributeMapping());
		foreach ($mappings as $vcProperty => $lAttributes) {
			$propertyName = strtoupper($vcProperty);
			$lAttributes = explode(',', $lAttributes);
			foreach ($lAttributes as $lAttribute) {
				$lAttribute = trim($lAttribute);
				if ($lAttribute === 'dn') {
					$vCardData[$propertyName] = base64_encode($record->getDn());
				} elseif ($record->hasAttribute($lAttribute)) {
					$vCardData[$propertyName] = [];
					foreach ($record->getAttribute($lAttribute) as $value) {
						if ($propertyName === 'PHOTO') {
							$value = self::buildPhotoValue($value);
						}
						$vCardData[$propertyName][] = $value;
					}
				}
			}
		}
		if (!isset($vCardData['FN'])) {
			throw new RuntimeException('Invalid record or configuration for vcard');
		}
		$vCardData['URI'] = base64_encode($record->getDn());
		// $vCardData['UID'] = $vCardData['URI'];
		return $vCardData;
	}

	protected static function buildPhotoValue(string $rawData): string {
		$image = new Image();
		$image->loadFromData($rawData);
		if (!$image->valid()) {
			return  '';
		}
		$valType = 'data:' . $image->dataMimeType() . ';base64';
		unset($image);
		return $valType . ',' . base64_encode($rawData);
	}
}
