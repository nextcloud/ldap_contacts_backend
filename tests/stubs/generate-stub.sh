#!/bin/sh
#
# SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: AGPL-3.0-or-later
#
generate-stubs \
	~/dev/nextcloud/server/apps/dav/lib/CardDAV/Integration/ExternalAddressBook.php \
	~/dev/nextcloud/server/apps/dav/lib/CardDAV/Integration/IAddressBookProvider.php \
	~/dev/nextcloud/server/apps/dav/lib/DAV/Sharing/IShareable.php \
	~/dev/nextcloud/server/apps/dav/lib/DAV/Sharing/Plugin.php \
	~/dev/nextcloud/server/apps/user_ldap/lib/Configuration.php \
	~/dev/nextcloud/server/apps/user_ldap/lib/Helper.php \
	~/dev/nextcloud/server/core/Command/Base.php \
	~/dev/nextcloud/server/3rdparty/stecman/symfony-console-completion/src/Completion/CompletionAwareInterface.php \
	~/dev/nextcloud/server/lib/private/Security/CSRF/CsrfTokenManager.php \
	~/dev/nextcloud/server/lib/private/Security/CSRF/CsrfToken.php \
	~/dev/nextcloud/server/lib/private/Image.php \
	> stub.phpstub

#     /**
#      * @property int $ldapPagingSize holds an integer
#      * @property string $ldapUserAvatarRule
# 	 * @property string $ldapPort
# 	 * @property string $ldapAgentName
# 	 * @property string $ldapAgentPassword
# 	 * @property string $ldapHost
# 	 * @property string $ldapTLS
# 	 * @property string $ldapConfigurationActive
#      */
