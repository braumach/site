[braumach.com.au](https://braumach.com.au) (Magento 2).

## How to deploy the static content
```posh
bin/magento cache:clean
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US en_AU
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme TemplateMonster/theme007 \
	-f en_AU
bin/magento cache:clean
```