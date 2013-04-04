<pre>
     _                 _                   _ _     
 ___(_)_ __ ___  _ __ | | ___  _ __     __| | |__  
/ __| | '_ ` _ \| '_ \| |/ _ \| '_ \   / _` | '_ \ 
\__ \ | | | | | | |_) | | (_) | | | | | (_| | |_) |
|___/_|_| |_| |_| .__/|_|\___/|_| |_|  \__,_|_.__/ 
                |_|                                
</pre>

# Simplon/Db 

Version 1.1.0

## Intro

Most of my projects require data from at least one type of database. In order to handle all communications I wrote some interface libraries which help me to deal with my daily coding fun. I worked with all supported databases hence all these interfaces were written.

For the last months I am running mostly with a MySQL setup supported with Redis as a cache store. I worked with Memcached before which I believe is stable on what its supposed to do. I closed the Couchbase chapter for now although I believe its a great idea. However, I wasnt really happy with its performance respectively its immature behaviour compared to e.g. Redis.

### Supported databases

- [MySQL](http://mysql.com/) >= 5.1
- [Redis](http://redis.io/) >= 2.4
- [Couchbase](http://www.couchbase.com/) >= 2.0
- [Memcached](http://memcached.org/) >= 1.4

### Dependecies

Big parts of all libraries will work with PHP 5.3. However since I am transitioning to PHP 5.4 you will find partly PHP 5.4 only code. This will grow depending how much time I find. Find all dependencies below:

- PHP >= 5.4.8
- MySQL: [EasyPDO](https://github.com/fightbulc/easy_pdo)
- Redis: [phpiredis](https://github.com/nrk/phpiredis) (PHP bindings) for [hiredis](https://github.com/redis/hiredis) (C-Client for Redis)
- Couchbase: [PHP Client Library](http://www.couchbase.com/develop/php/current) with [C-Client library](https://github.com/couchbase/libcouchbase)
- Memcached: [libevent](http://libevent.org/)

## Installing

You can install Simplon/Db either via package download from github or via composer install. I encourage you to do the latter:

```json
{
  "require": {
    "simplon/db": "1.1.0"
  }
}
```

Depending on which database you would like to use pay attention to the above listed dependencies.

## 1. Usage MySQL

Lets do some coding given that all desired databases and its dependencies were installed.

### 1.1 MySQL connection

Lets create a MySQL connection instance:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = Mysql::Instance('localhost', 'test', 'rootuser', 'rootuser');
```

Another way to create a mysql instance is via the ```DbInstance class```. This class creates the instance and holds it as Singleton throughout runtime within a pool of other connections - in case you have to keep more than one connection:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');
```

### 1.2 MySQL query

When querying a database we have again two options. The first option is to access the database directly via [EasyPDO](https://github.com/fightbulc/easy_pdo) which is a PDO wrapper:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// query
$results = $dbInstance->FetchAll('SELECT * FROM foobar WHERE ekey = :key', ['key' => 'BB']);

// dumps assoc. array of FALSE when fails
var_dump($results);
```

The other option requires the use of the ```SqlManager class```. In order to use this class we need to pass a builder pattern class, ```SqlQueryBuilder```, to communicate with our database. What advantage does that offer? Well in case that we want to do more things with our query before sending it off we encapsule it as an object within the ```SqlQueryBuilder```. From there on we could pass it throughout our application to add more data or alike before sending the query finally to the database:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// create SqlManager instance
$sqlManager = new \Simplon\Db\SqlManager($dbInstance);

// query builder
$sqlQuery = (new \Simplon\Db\SqlQueryBuilder())
    ->setQuery('SELECT * FROM foobar WHERE ekey = :key')
    ->setConditions(['key' => 'BB']);

// query
$results = $sqlManager->fetchAll($sqlQuery);

// dumps assoc. array of FALSE when fails
var_dump($results);
```

What both options have in common are the named parameters ```ekey = :key``` which are identified by the conditions- / data-array keys.

### 1.3 MySQL insert/update

The way how to insert/update datasets differs for both options. Again see the following examples for better understanding:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// query: inserts one new row
$data = ['id' => NULL, 'ekey' => 'DD'];
$dbInstance->ExecuteSQL('INSERT INTO foobar VALUES (:id, :ekey)', $data);

// ##############################################

// query update
$data = ['id' => 5, 'ekey' => 'FF'];
$dbInstance->ExecuteSQL('UPDATE INTO foobar VALUES (:ekey) WHERE id = :id', $data);
```

Here goes our SqlManager solution with SqlQueryBuilder:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// query: inserts one new row
$data = ['id' => NULL, 'ekey' => 'DD'];

$sqlQuery = (new SqlQueryBuilder())
->setTableName('foobar')    // define the table name
->setData($data);           // set data (keys = database column name)

$sqlManager->insert($sqlQuery);

// ##############################################

// query update
$conds = ['id' => 5];
$data = ['ekey' => 'FF'];

$sqlQuery = (new SqlQueryBuilder())
->setTableName('foobar')    // define the table name
->setConditions($conds)     // set conditions
->setData($data);           // set data (keys = database column name)

$sqlManager->update($sqlQuery);
```

Difference is that for the latter method we don't need to write any repetitive SQL which in turn results in better maintenance and general code overview.

### 1.4 MySQL remove datasets

From time to time we also need to remove a couple of datasets. Again, two examples:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// query
$dbInstance->ExecuteSQL('DELETE FROM foobar WHERE id = :id', ['id' => 5]);
```

SqlManager with SqlQueryBuilder:

```php
require __DIR__ . '/../vendor/autoload.php';

// connect to server "localhost", db "test" with user/pass "rootuser/rootuser"
$dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');

// ##############################################

// query
$sqlQuery = (new SqlQueryBuilder())
->setTableName('foobar')        // define the table name
->setConditions(['id' => 5]);   // set conditions

$sqlManager->remove($sqlQuery);
```

### 1.5 MySQL summary: direct access

- Connect (both options are valid):
    - ```Mysql::Instance(HOST, DB, USER, PASSWORD)```
    - ```\Simplon\Db\DbInstance::MySQL(HOST, DB, USER, PASSWORD)```
        - Returns: DbInstance
- Fetch all found data:
    - ```DbInstance->FetchAll(QUERY, CONDS)```
        - Returns an assoc. array
    - ```DbInstance->FetchArray(QUERY, CONDS)```
        - Returns an array
    - ```DbInstance->FetchObject(QUERY, CONDS)```
        - Returns an object
- Fetch by steps:
    - ```DbInstance->Fetch(QUERY, CONDS)```
        - Returns an iterator pointer which is essential for very big result sets
- Fetch one column value:
    - ```DbInstance->FetchValue(QUERY, CONDS)```
        - Returns the first selected column
- Insert data:
    - ```DbInstance->ExecuteSql(INSERT-QUERY, DATA)```
        - Returns insert-id or null. FALSE when failed
- Update data:
    - ```DbInstance->ExecuteSql(UPDATE-QUERY, DATA)```
        - Returns FALSE when failed
- Remove data:
    - ```DbInstance->ExecuteSql(DELETE-QUERY, DATA)```
        - Returns FALSE when failed

### 1.6 MySQL summary: access via SqlManager with SqlQueryBuilder

- Connect (both options are valid):
    - ```Mysql::Instance(HOST, DB, USER, PASSWORD)```
    - ```\Simplon\Db\DbInstance::MySQL(HOST, DB, USER, PASSWORD)```
        - Returns: DbInstance
- SqlManager instance:
    - ```SqlManager = new \Simplon\Db\SqlManager(DbInstance)```
- Fetch all found data:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setQuery(QUERY)->setConditions(CONDS)```
    - ```SqlManager->fetchAll(SqlQueryBuilder)```
        - Returns an assoc. array
- Fetch by steps:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setQuery(QUERY)->setConditions(CONDS)```
    - ```SqlManager->fetchCursor(SqlQueryBuilder)```
        - Returns an iterator pointer which is essential for very big result sets
- Fetch one column value:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setQuery(QUERY)->setConditions(CONDS)```
    - ```SqlManager->fetchColumn(SqlQueryBuilder)```
        - Returns the first selected column
- Insert data:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setTableName(TABLENAME)->setData(DATA)```
    - ```SqlManager->insert(SqlQueryBuilder)```
        - Returns insert-id or null. FALSE when failed
- Update data:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setTableName(TABLENAME)->setConditions(CONDS)->setData(DATA)```
    - ```SqlManager->update(SqlQueryBuilder)```
        - Returns FALSE when failed
- Remove data:
    - ```SqlQueryBuilder = (new SqlQueryBuilder)->setTableName(TABLENAME)->setConditions(CONDS)```
    - ```SqlManager->remove(SqlQueryBuilder)```
        - Returns FALSE when failed

## 2. Usage Redis

Work in progress ...

# Changelog

## Version 1.1.0
- Refactored Redis library since it had >3000 LOC
- Redis library has been seperated by its commands
- RedisManager offers references to all command classes
