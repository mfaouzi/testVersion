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

## Update your AppKernel.php

- Add those two lines from your app/AppKernel.php file:
```
	$bundles[] = new Aliznet\WCSBundle\AliznetWCSBundle();
	$bundles[] = new new Aliznet\EnrichBundle\AliznetEnrichBundle();
```

## Update your config.yml

- Add these lines from your app/config/config.yml file after this line **akeneo_storage_utils:** :
```
	mapping_overrides:
		-
			original: Pim\Bundle\CatalogBundle\Entity\Category
			override: Aliznet\WCSBundle\Entity\Category
		-
			original: Pim\Bundle\CatalogBundle\Entity\CategoryTranslation
			override: Aliznet\WCSBundle\Entity\CategoryTranslation
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
> WCSBundle\Resources\fixtures\wcs-family.csv in Collect > import profiles > csv_family_import
> WCSBundle\Resources\fixtures\wcs-attributes.csv in Collect > import profiles > csv_attribute_import
> WCSBundle\Resources\fixtures\wcs-variant_group.csv in Collect > import profiles > csv_variant_group_import
> WCSBundle\Resources\fixtures\wcs-product-ecommerce.csv in Collect > import profiles > csv_product_import	**for ecommerce channel**
> WCSBundle\Resources\fixtures\wcs-product-print.csv in Collect > import profiles > csv_product_import	**for print channel**
> WCSBundle\Resources\fixtures\wcs-product-mobile.csv in Collect > import profiles > csv_product_import	**for mobile channel**

