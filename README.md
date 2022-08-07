# Magento Product Importer Sample Code for Magento 2 >= 2.3.x (In Development)

This is a Magento 2 module for importing products

## Installation
### Composer
```
    composer require the-tech-makers/m2-product-import
```
### ZIP file
Download the module and unzip it under the folder app/code/Ttm/HelloPrint.  

## How to use it

### Create a CSV file with the product information

Mandatory columns:

```
sku
```

Extra columns (you can add as many as you want as long as they are product attributes). Note that the first row column values will be used for setting the product data:

```
name
description
url_key
url_path
...
```

Sample file:
```
sku,url_key,url_path
1000,white-mug,white-mug.html
1119,mug-with-calendar,mug-with-calendar.html
1001,mug-with-inner-colour,mug-with-inner-colour.html
```

