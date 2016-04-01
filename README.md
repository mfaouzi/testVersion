# Aliznet WCS Connector

![alt text][logo]
[logo]: http://www.aliznet.fr/wp-content/themes/aliznet/img/logo.png "AliznetConnector : "

Aliznet WCS Connector is extension that allows you to import your catalog from Akeneo CSV files into Websphere commerce.

## Installation

### Via composer

- Add into in the `require` section:

`"aliznet":"dev-master"`

- Add into the `repositories` section:
```
	{
        "type": "vcs",
        "url": "https://github.com/aliznet-labs/WCSConnector.git",
        "branch": "master"
    }
```

- Execute update composer.phar :
```
	php composer.phar update aliznet
```

## Update Akeneo database

- apply the script to your Akeneo database :
```
	WCSBundle/Resources/sql/mapping_migration.schema.sql
```

- run:
```
	php app/console doctrine:schema:update --force
	php app/console cache:clear --env=prod
```

## Create Attributes Groups into Akeneo interface
```
	Go into settings > Attribute groups > Create a new attribute group
	* General
	* Pricing
	* Custom
	* Publishing
	* Descriptif
	* Defining
	* Display
```

## Import these files into Akeneo interface
```
	import\wcs-family.csv in Collect > import profiles > csv_family_import
	import\wcs-attributes.csv in Collect > import profiles > csv_attribute_import
```
