Charcoal Sitemap
================

[![License][badge-license]](LICENSE)
[![Build Status][badge-github]][github-actions]
[![Latest Release][badge-release]][github-latest]
[![Supported PHP Version][badge-php]](composer.json)

A [Charcoal][charcoal/charcoal] package for generating a sitemap.

## Installation

```shell
composer require charcoal/contrib-sitemap
```

### Setup

For Charcoal projects, the package can be registered in two ways.

#### Charcoal Module

The Sitemap's module will register the service provider (see below) and
the route (`/sitemap.xml`).

The module can be registered from your configuration file:

```json
"modules": {
    "charcoal/sitemap/sitemap": {}
}
```

#### Charcoal Service Provider

The Sitemap's service provider will register the necessary services (see below)
for building a sitemap.

The service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/view/service-provider/view": {}
    }
}
```

To register a route from your configuration file:

```json
{
    "routes": {
        "actions": {
            "sitemap.xml": {
                "route": "/sitemap.xml",
                "methods": [ "GET" ],
                "controller": "charcoal/sitemap/action/sitemap",
                "action_data": {
                    "sitemap_ident": "xml"
                }
            }
        }
    }
}
```

By default, the action controller will look for a sitemap hierarchy named `xml`
which can be changed via the `sitemap_ident` controller setting.

## Overview

### Routes

* **`GET /sitemap.xml`** — A route assigned to `Charcoal\Sitemap\Action\SitemapAction`.  
  Used to serve the XML document.

### Services

* **`charcoal/sitemap/builder`** — Instance of `Charcoal\Sitemap\Service\Builder`.  
  Used to generate the collections of links from the configured models.
* **`sitemap/formatter/xml`** — Instance of `Charcoal\Sitemap\Service\XmlFormatter`.  
  Used to generate the XML from one or more collections of links from the `Builder`.
* **`sitemap/presenter`** — Instance of `Charcoal\Sitemap\Service\SitemapPresenter`.  
  Used to resolve model transformations.
* **`sitemap/transformer/factory`** — Instance of `Charcoal\Factory\GenericFactory`
  ([charcoal/factory]).  
  Used to resolve object transformers from object types.

## Configuration

The Sitemap can be configured from the application configset under the
`sitemap` key. You can setup which objects to be included and available
translations (l10n).

Most options are renderable by objects using your application's chosen
template syntax (Mustache used in examples below).

### Default Options

```jsonc
{
    /**
     * The service's configuration point.
     */
    "sitemap": {
        /**
         * One or more groups to customize how objects should be processed.
         *
         * The array key is an arbitrary identifier for the grouping of models.
         */
        "<group-name>": {
            /**
             * Whether or not to include links to translations.
             *
             * - `true` — Multilingual. Include all translations
             *   (see `locales.languages`).
             * - `false` — Unilingual. Include only the default language
             *   (see `locales.default_language`).
             */
            "l10n": false,
            /**
             * The language to include a link to if group is unilingual.
             *
             * If `l10n` is `true`, this option is ignored.
             *
             * Defaults to the application's current language.
             */
            "locale": "<current-language>",
            /**
             * Whether or not to check if the routable object
             * has an active route (`RoutableInterface#isActiveRoute()`)
             *
             * - `true` — Include only routable objects with active routes.
             * - `false` — Ignore if a routable object's route is active.
             */
            "check_active_routes": false,
            /**
             * Whether or not to prepend relative URIs with
             * the application's base URI (see `base_url`).
             *
             * - `true` — Use only the object's URI (see `sitemap.*.objects.*.url`).
             * - `false` — Prepend the base URI if object's URI is relative.
             */
            "relative_urls": false,
            /**
             * The transformer to parse each model included in `objects`.
             *
             * Either a PHP FQCN or snake-case equivalent.
             */
            "transformer": "<class-string>",
            /**
             * Map of models to include in the sitemap.
             */
            "objects": {
                /**
                 * One or more models to customize and include in the sitemap.
                 *
                 * The array key must be the model's object type,
                 * like `app/model/foo-bar`, or fully-qualified name (FQN),
                 * like `App\Model\FooBar`.
                 */
                "<object-type>": {
                    /**
                     * The transformer to parse the object.
                     *
                     * Either a PHP FQCN or snake-case equivalent.
                     */
                    "transformer": "<class-string>",
                    /**
                     * The URI of the object for the `<loc>` element.
                     */
                    "url": "{{ url }}",
                    /**
                     * The name of the object. Can be used in a
                     * custom sitemap builder or XML generator.
                     */
                    "label": "{{ title }}",
                    /**
                     * Map of arbitrary object data that can be used
                     * in a custom sitemap builder or XML generator.
                     */
                    "data": {},
                    /**
                     * List or map of collection filters of which objects
                     * to include in the sitemap.
                     *
                     * ```json
                     * "<filter-name>": {
                     *     "property": "active",
                     *     "value": true
                     * }
                     * ```
                     */
                    "filters": [],
                    /**
                     * List or map of collection orders to sort the objects
                     * in the sitemap.
                     *
                     * ```json
                     * "<order-name>": {
                     *     "property": "position",
                     *     "direction": "ASC"
                     * }
                     * ```
                     */
                    "orders": [],
                    /**
                     * Map of models to include in the sitemap
                     * below this model.
                     *
                     * Practical to group related models.
                     */
                    "children": {
                        /**
                         * One or more models to customize and include in the sitemap.
                         */
                        "<object-type>": {
                            /**
                             * A constraint on the parent object to determine
                             * if the child model's objects should be included
                             * in the sitemap.
                             */
                            "condition": null
                        }
                    }
                }
            }
        }
    }
}
```

Each model can override the following options of their group:
`l10n`, `locale`, `check_active_routes`, `relative_urls`.


### Example

The example below, identified as `footer_sitemap`, is marked as multilingual
using the `l10n` option which will include all translations.

```json
{
    "sitemap": {
        "footer_sitemap": {
            "l10n": true,
            "check_active_routes": true,
            "relative_urls": false,
            "transformer": "charcoal/sitemap/transformer/routable",
            "objects": {
                "app/object/section": {
                    "transformer": "\\App\\Transformer\\Sitemap\\Section",
                    "label": "{{ title }}",
                    "url": "{{ url }}",
                    "filters": {
                        "active": {
                            "property": "active",
                            "value": true
                        }
                    },
                    "data": {
                        "id": "{{ id }}",
                        "metaTitle": "{{ metaTitle }}"
                    },
                    "children": {
                        "app/object/section-children": {
                            "condition": "{{ isAnObjectParent }}"
                        }
                    }
                }
            }
        }
    }
}
```

## Usage

### Using the builder

The builder returns only an array. You need to make your own converter
if you need another format.

Given the settings above:

```php
$builder = $container['charcoal/sitemap/builder'];
// 'footer_sitemap' is the ident of the settings you want.
$links = $builder->build('footer_sitemap');
```

You can also use the `SitemapBuilderAwareTrait`, which includes the setter and
getter for the sitemap builder, in order to use it with minimal code in every
necessary class.

### XML Formatter

The XML formatter generates a valid XML sitemap from the array returned
by the builder.

```php
$builder = $container['charcoal/sitemap/builder'];
$links   = $builder->build('footer_sitemap');

$formatter = $container['sitemap/formatter/xml'];
$sitemap   = $formatter->createXmlFromCollections($links);
```

## Development

To install the development environment:

```shell
composer install
```

To run the scripts (PHP lint, PHPCS, PHPStan, and PHPUnit):

```shell
composer lint
composer test
```

## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

[charcoal/charcoal]: https://github.com/charcoalphp/charcoal
[charcoal/factory]:  https://github.com/charcoalphp/factory

[badge-github]:      https://img.shields.io/github/actions/workflow/status/charcoalphp/contrib-sitemap/ci.yml?branch=main
[badge-license]:     https://poser.pugx.org/charcoal/contrib-sitemap/license
[badge-php]:         https://img.shields.io/packagist/php-v/charcoal/charcoal?style=flat-square&logo=php
[badge-release]:     https://img.shields.io/github/tag/charcoalphp/contrib-sitemap.svg

[github-actions]:    https://github.com/charcoalphp/contrib-sitemap/actions
[github-latest]:     https://github.com/charcoalphp/contrib-sitemap/releases/latest
