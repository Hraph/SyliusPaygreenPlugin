# Sylius Paygreen Plugin
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)

Sylius plugin for [Paygreen.io](https://paygreen.io/) payment provider

## Installation

### Requirements

| Package | Version |
| --- | --- |
| PHP |  ^7.4 |
| sylius/sylius |  ^1.8 |

### Instructions

1. Install plugin using `composer`:
    ```bash
    $ composer require hraph/sylius-paygreen-plugin 
    ```

2. Import config:
    ```yaml
    # config/packages/_sylius.yaml
    imports:
        # ...
        - { resource: "@SyliusPaygreenPlugin/Resources/config/app/config.yaml" }
    ```

3. Import routing:
    ```yaml
    # config/routes.yaml
    hraph_sylius_paygreen_plugin_admin:
        resource: "@SyliusPaygreenPlugin/Resources/config/admin_routing.yaml"
        prefix: /admin
    
    ```

4. Update your schema (for existing project):

    ```bash
    # Generate and edit migration
    bin/console doctrine:migrations:diff
    
    # Then apply migration
    bin/console doctrine:migrations:migrate
    ```

## Plugin configuration

```yaml
# config/packages/sylius_paygreen.yaml
sylius_paygreen:
    api:
        username: PaygreenUsername
        api_key: API_KEY
        sandbox: true
    force_use_authorize: true # All payments will be executed using authorize (fingerprint)
    use_insite_mode: true # Use iframe mode
```

### Views customization

You can customize the payment view by creating a custom file in `templates/bundles/SyliusPaygreenPlugin/Checkout/payment.html.twig`:
```html
{% block content %}
    <iframe src="{{ execute_url ~ "?display=insite" }}" style="border: 0;width: 100%; height: 600px"></iframe>
{% endblock %}
```

### Plugin extension

The following plugin entities can be extended using [Sylius documentation guidelines](https://docs.sylius.com/en/latest/customization/model.html):
* `paygreen_shop`
* `paygreen_transfer`

Example:
```yaml
sylius_paygreen:
    resources:
        paygreen_shop:
            classes:
                interface: App\Entity\PaymentProvider\CustomPaymentProviderShopInterface
                model: App\Entity\PaymentProvider\CustomPaymentProviderShop
                factory: App\Entity\PaymentProvider\CustomPaymentProviderShopFactory
```

## Contribution

### Installation:

```bash
$ (cd tests/Application && yarn install)
$ (cd tests/Application && yarn build)
$ (cd tests/Application && APP_ENV=test bin/console assets:install public)

$ (cd tests/Application && APP_ENV=test bin/console doctrine:database:create)
$ (cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)
```

To be able to setup a plugin's database, remember to configure you database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

### Running plugin tests

- PHPSpec

  ```bash
  $ composer phpspec
  ```

- Behat

  ```bash
  $ composer behat
  ```

- All tests (phpspec & behat)

  ```bash
  $ composer test
  ```

[ico-version]: https://img.shields.io/packagist/v/hraph/sylius-paygreen-plugin.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg?style=flat-square3

[link-packagist]: https://packagist.org/packages/hraph/sylius-paygreen-plugin
