# FluidHydrator

*This package is based on [thecodingmachine/metahydrator](https://packagist.org/packages/thecodingmachine/metahydrator), and
aims to ease instantiation code (ie when not using dependency injection). In order to do so, it follows fluent design pattern.*

## How to use

As you have probably deduced by now, the main class of this package is `FluidHydrator`. It implements interface `Hydrator`
(refer to [mouf/tdbm-hydrator](https://packagist.org/packages/mouf/tdbm-hydrator) for more details).

```php
$hydrator = new FluidHydrator;
```

### Primitive types
Use method `field` to declare a primitive field. Then, declare its type using `int()`, `bool()`, `string()`, etc.
```php
$hydrator->field('foo')->int();
```
To declare an unstructured array field (typically some decoded JSON), use the `simpleArray` function.
```php
$hydrator->field('foo')->simpleArray();
```

As we are these can be chained as following:
```php
$hydrator
    ->field('foo')->string()->then()
    ->field('bar')->int() // Note that call o method then() is optional!
    ->field('baz')->float()
;
```
A type method leads to a state where you may add options to the field, mostly validators.
```php
$hydrator
    ->field('foo')->string()->required()->maxLength(55)
;
```

### Array
Option `array()` allows to change the current type from T to array<T>, where current validators are used for validating
each entry. These can be used multiple times.
```php
$hydrator
    // 'foo' must be an array of non-empty arrays containing non-empty strings of length inferior to 55
    ->field('foo')->string()->required()->maxLength(55)->array()->required()->array()
;
```



### Object types
You may also use a non-primitive type (ie a class) for a `field()`. Method `object()` then needs you to specify
the hydrator used to parse the sub-data.
You may pass an existant hydrator using method `hydrator()`
```php
$hydrator
    ->field('garply')->object(Garply::class)->hydrator($garplyHydrator)
;
```
or even use default hydrator (being TDBMHydrator), if you do not wish to check the data sanity
```php
$hydrator
    ->field('garply')->object(Garply::class)->hydrator()
;
```
If you want to declare the sub-hydrator on the fly, you can: declare it between a `begin()` and a `end()`
```php
$hydrator
    ->field('garply')->object(Garply::class)
    ->begin()
        ->field('qux')->string()
        ->field('quux')->bool()
    ->end()
;
```
Note that after `end()` or `hydrator()`, you are in the same state as when typing a primitive field. Therefore, you can
add validators, and even switch from T to T[] with `array()`!

### Sub-Object
In some cases, you will want to access sub-objects from the top-level value being hydrated. This way, the existing value
will not be replaced, but directly hydrated, respecting references and allowing partial edition.

The field type you're looking for here is `subobject()`; the writing is substantially similar to `object()`, except for
the option `array()` that you should not used, since it's not (yet) supported.
```php
$hydrator
    ->field('garply')->subobject(Garply::class)
    ->begin()
        ->field('qux')->string()
        ->field('quux')->bool()
    ->end()->required()
;
```
