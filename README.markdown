# Log Bundle

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Latest Stable Version](https://img.shields.io/packagist/v/yipikai/log-bundle.svg)](https://packagist.org/packages/yipikai/log-bundle)
[![Total Downloads](https://poser.pugx.org/yipikai/log-bundle/downloads.svg)](https://packagist.org/packages/yipikai/log-bundle)
[![License](https://poser.pugx.org/yipikai/log-bundle/license.svg)](https://packagist.org/packages/yipikai/log-bundle)

## Install bundle

You can install it with Composer:

```
composer require yipikai/log-bundle
```

## Documentation

```yaml
yipikai_log:
  uri:                        "URI"
  token:
    public:                   "PUBLIC_KEY"
    private:                  "PRIVATE_KEY"
    salt:                     "SALT"

yipikai_log:
  uri:                        "URI"
  token:
    public:                   "PUBLIC_KEY"
    private:                  "PRIVATE_KEY"
    salt:                     "SALT"
  domain:                     ""
  enabled:
    exception:                false
    doctrine:                 false
  async:                      false
  excludes:
    PATH\CLASSNAME:
      all:                    false
      fields:
        - fieldname


```




## Commit Messages

The commit message must follow the [Conventional Commits specification](https://www.conventionalcommits.org/).
The following types are allowed:

* `update`: Update
* `fix`: Bug fix
* `feat`: New feature
* `docs`: Change in the documentation
* `spec`: Spec change
* `test`: Test-related change
* `perf`: Performance optimization

Examples:

    update : Something

    fix: Fix something

    feat: Introduce X

    docs: Add docs for X

    spec: Z disambiguation

## License and Copyright
See licence file

## Credits
Created by [Matthieu Beurel](https://www.mbeurel.com). Sponsored by [Yipikai Studio](https://yipikai.studio).