# Aliznet WCS Connector for Akeneo

![alt text][logo]
[logo]: http://www.aliznet.fr/wp-content/themes/aliznet/img/logo.png "AliznetConnector : "

Aliznet WCS Connector is extension that allows you to import your catalog from Akeneo CSV files into Websphere commerce.

## Installation

### Via composer : 

- Add this line to the `require` section in your composer.json:

`"aliznet/akeneo-websphere-connector":"dev-master"`

- Add this part to the `repositories` section:
```
	{
        "type": "vcs",
        "url": "https://github.com/hicham-sabihi/aliznet_wcs_connector.git",
        "branch": "master"
    }
```

- Execute update composer.phar :
```
	php composer.phar update  aliznet/akeneo-websphere-connector
```

## Update your AppKernel.php

- Add these bundles to your app/AppKernel.php file:
```
	$bundles = [
	        new Aliznet\WCSBundle\AliznetWCSBundle(),
            new Aliznet\EnrichBundle\AliznetEnrichBundle(),
        ];
```

## Update your config.yml

- Add these lines from your app/config/config.yml file right after this line **akeneo_storage_utils:** :
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

## Create these Attributes Groups into Akeneo interface
```    
    In order to export informations usable by websphere commerce, there are groups of attributes that must be added. 
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
 	WCSBundle\Resources\fixtures\wcs-attributes.csv in Collect > import profiles > csv_attribute_import
	WCSBundle\Resources\fixtures\wcs-options.csv in Collect > import profiles > csv_option_import
	WCSBundle\Resources\fixtures\wcs-family.csv in Collect > import profiles > csv_family_import
 	WCSBundle\Resources\fixtures\wcs-variant_group.csv in Collect > import profiles > csv_variant_group_import
 	WCSBundle\Resources\fixtures\wcs-product-ecommerce.csv in Collect > import profiles > csv_product_import	**for ecommerce channel**
 	WCSBundle\Resources\fixtures\wcs-product-print.csv in Collect > import profiles > csv_product_import	    **for print channel**
 	WCSBundle\Resources\fixtures\wcs-product-mobile.csv in Collect > import profiles > csv_product_import	    **for mobile channel**
```
