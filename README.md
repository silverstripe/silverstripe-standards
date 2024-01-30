# Silverstripe standards

This repository contains some dependencies, custom rules, and predefined rulesets which are used to help maintain coding standards for Silverstripe CMS commercially supported modules.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev silverstripe/standards
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
<summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include rules.neon in your project's PHPStan config:

```neon
includes:
    - vendor/silverstripe/standards/rules.neon
```
</details>
