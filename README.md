# Yet Another Simple PDO Class
Database persistence made it very simple task

#### Require the class in your project
```php
<?php
require("Db.class.php");
```
#### Create the instance
```php
<?php
// The instance
$db = new Db();
```
#### Select all from table
```
<?php
$db->setTable('example_table');
return $db->findAll();
```
#### Select all from table with search parameters
```
<?php
$db->setTable('example_table');
return $db->findAll(array('column1' => 'column1 value', 'column2' => 'column2 value'));
```
#### Select object
```
$db->setTable('example_table');
return $db->findOne(array('column1' => 'column1 value'));
```
#### Insert into database
```
<?php
$db->setTable('example_table');
$db->insert(
    array(
            'column1' => 'column1 value',
            'column2' => 'column2 value',
            'column3' => $object->property,
        )
    );
```
#### Update
```
<?php
$db->setTable('example_table');
$db->update(
    array(
            'column1' => 'column1 value',
            'column2' => 'column2 value',
            'column3' => $object->property,
        ),
        array('id' => $id)
    );
```
#### Delete
```
$db->setTable('example_table');
$db->delete(array('column1' => 'column1 value'));
```
#### Custom Query
```
$result = $db->->getInstance()->prepare('Custom Query like JOIN WHERE column = ?');
$result->execute(array('value'));
return $result->fetchAll(PDO::FETCH_OBJ);
```