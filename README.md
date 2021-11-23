# FQORM 

Very simple ORM layer, based on the Data Mapper pattern. 

Some key design principles: 

- There are two types of classes: Mapper and Entity
- Entities contain data; they have properties, may have 
  formatting methods, validation, etc. 
- Mapper can read and write Entities from the database
- There are
  two abstract classes AbstractMapper and AbstractMapperId that can be used to 
  create you own Mappers quickly. 
- You can create any methods for Mapper, but typically we use `save`, `delete`, 
  `findByID`, `findByWhere`, etc. 

### Not a rules 

These are not rules, but things are very simple when it happens this way:

- Each Entity has its own Mapper
- Each Mapper maps Entity to one specific database table
- Entity has a structure similar to specific database table

If you do things differently, existing abstract classes won't help you. 

### How to create Entity and Mapper

See [ExampleIdEntity.php](./tests/ExampleIdEntity.php)
and [ExampleIdMapper.php](./tests/ExampleIdMapper.php).

- Entities just have some properties
- Mappers extend `AbstractMapper` or `AbstractMapperID` and override methods
  `mapperTableName`, `mapperEntityClass`
- In another override method `createFieldMapping` you have to describe 
  mapping for all fields
- For `AbstractMapper` subclasses you have to describe unique (key) 
  fields in `createFieldMappingUnique`. Class `AbstractMapperID` already does this for `id`.  
- Implement a save, delete, find... methods annotated with your specific Entity types.
  You may use existing untyped* methods of parent classes.


