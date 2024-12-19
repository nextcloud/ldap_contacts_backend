<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
			$lAttributes = explode(',', (string)$lAttributes);
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
