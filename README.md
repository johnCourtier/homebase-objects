# Homebase

## Objects

Package provides basic objects with magic property parsing out of annotation, access control, setter type comparison and simple getter.

- type is optional and is checked when available
- objects, scalars, array and combination of those are supported
- you can define your own unique scalar type by defining is_<your scalar type> function
- access is checked
- properties are inherited by extended objects

### Example of usage

```
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
class MyClass extends Homebase\Model\AbstractObject
{

}
```

### Custom type definition

```
/**
 * @param string
 * @return bool
 */
function is_date($date)
{
  return strtotime($date);
}

/**
 * @property date $myDate
 */
class MyClass extends Homebase\Model\AbstractObject
{
}
```
