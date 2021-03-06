# Homebase

## Installation

### Composer

```bash
composer require homebase/objects
```

## Objects

Package provides basic objects with magic property parsing out of annotation, access control, setter type comparison and simple getter.

- type is optional and is checked when available
- objects, scalars, array and combination of those are supported
- you can define your own unique scalar type by defining is_<your scalar type> function
- access is checked
- properties are inherited by extended objects

### Example of usage

```php
/**
 * @property-read string $stringPropertyRead
 * @property-write string $stringPropertyWrite
 * @property string $stringProperty
 * @property int $intProperty
 * @property numeric $numericProperty
 * @property null $null
 * @property string|null $stringNullProperty
 * @property DateTime $dateTime
 * @property $mixed
 * @property string[] $stringsProperty
 * @property DateTime[] $dateTimes
 */
class MyClass extends Homebase\Model\PropertyContainer
{

}
```

### Custom type definition

```php
/**
 * @param string
 * @return bool
 */
function is_date($date)
{
  return (bool) strtotime($date);
}

/**
 * @property date $myDate
 */
class MyClass extends Homebase\Model\PropertyContainer
{
}
```
