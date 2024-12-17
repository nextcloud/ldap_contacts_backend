<!--
  - SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# LDAP Contacts Backend

**LDAP backend for Nextcloud Contacts**

![image](https://github.com/nextcloud/ldap_contacts_backend/assets/1731941/1666c7bd-ec11-4448-a7fa-6fbfef2ff6a6)

Adds a virtual address book and enables importing contacts to a user's individual address book.

You and your users will be able to search through the LDAP contacts via the global contacts menu. An import 
action allows to copy the contact over to the best fitting existing addressbook. A redirect takes you the 
contacts app with the newly created card open.

Note: The [Contacts app for Nextcloud](https://apps.nextcloud.com/apps/contacts) should also be installed.

<!-- ## Features -->

<!-- ## Status -->

<!-- See [#1](https://github.com/nextcloud/ldap_contacts_backend/issues/1). -->

## Configuring

In order to configure an LDAP backend, run:

`./occ ldap_contacts:add --interactive <ADDRESS_BOOK_NAME>`

Where `<ADDRESSBOOK_NAME>` is a name you like to identify the virtual addressbook with. The interactive mode leads you 
through the configuration, but you can also use the --help flag to see all the options.

## Commands

`./occ ldap_contacts:add`

Add an LDAP contacts backend configuration

`./occ ldap_contacts:edit`

Edit an LDAP contacts backend configuration

`./occ ldap_contacts:list`

Lists all LDAP contacts backend configurations

`./occ ldap_contacts:delete`

Delete an LDAP contacts backend configuration

## Example configuration

```
occ ldap_contacts:add test \
  --host=localhost \
  --port=389 \
  --trans_enc=tls \
  --bindDN='cn=admin,dc=...' \
  --bindPwd=****** \
  --filter='(objectClass=inetOrgPerson)' \
  --base='ou=users,dc=...' \
  --attrs=cn \
  --attrs=mail \
  --attrs=telephoneNumber \
  --mapping=EMAIL:mail \
  --mapping=FN:cn \
  --mapping=TEL:telephoneNumber
```

## Additional documentation

Hints about what should go in some of the fields can be found by looking at the 
[User authentication with LDAP](https://docs.nextcloud.com/server/latest/admin_manual/configuration_user/user_auth_ldap.html) 
documentation since many of the fields are similar.
