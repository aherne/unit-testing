# PHP Unit Testing API

Table of contents:

- [About](#about)
	- [Why Not PHPUnit](#why-not-phpunit)
- [Configuration](#configuration)
- [Implementation](#implementation)
    - [Initialization](#initialization)
    - [Development](#development)
    - [Execution](#execution)
- [Installation](#installation)
- [Unit Tests](#unit-tests)
- [Assertions](#assertions)
- [Examples](#examples)

## About 

This library was in part created out of frustration while working with PHPUnit, the standard solution used by over 99% of PHP applications that feature unit testing.  This current software aims at building something that PHPUnit is not: a cleanly coded, zero dependencies API! 

![diagram](https://www.lucinda-framework.com/public/images/svg/unit-testing-api.svg)

It only requires developer to follow these steps:

- [configuration](#configuration): setting up an XML file where unit testing is configured
- [initialization](#initialization): automated creation of unit testing architecture (classes and methods) for target API under testing
- [development](#development): user development of one or more unit tests for each class method created above
- [execution](#execution): automated execution of unit tests on above foundations and display of unit test results on console or as JSON

API is fully PSR-4 compliant, only requiring PHP8.1+ interpreter, SimpleXML + cURL + PDO extensions (latter for URI and SQL testing) and [Console Table API](https://github.com/aherne/console_table) (for displaying unit test results). To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[assertions](#assertions)**: describes how to make assertions using this API
- **[examples](https://github.com/aherne/oauth2client/tree/master#unit-tests)**: shows real life unit tests for [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/master)

### Why Not PHPUnit

Everything about that PHPUnit reminds of bygone ages when developers built huge classes that do "everything" and knew nothing about encapsulation except keyword "extends" (doubters should check [https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php](https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php) all PHPUnit tests must extend!). 

Can something better be done? Should unit testing APIs abide to good principles of object oriented programming or only the code that is being tested? IMHO, as long as a developer feels confortable working with a mess, it will become a bad precedent to build something similar later on. **Something much better MUST be done!**

## Configuration

Similar to PHPUnit, configuration of unit tests is done via an XML file with following syntax:

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xml>
<xml>
    <unit_tests>
        <unit_test>
            <sources path="PATH" namespace="NAMESPACE"/>
            <tests path="PATH" namespace="NAMESPACE"/>
        </unit_test>
        ...
    </unit_tests>
    <servers>
        <sql>
            <ENVIRONMENT>
                <server driver="DRIVER" host="HOSTNAME" port="PORT" username="USERNAME" password="PASSWORD" schema="SCHEMA" charset="CHARSET"/>
            </ENVIRONMENT>
            ...
        </sql>
    </servers>
</xml>
```

Mandatory tag **unit_tests** stores the suite of unit tests to be executed. Each API under testing is identified by a **unit_test** tag which has following subtags:

- **sources**: configures base path of sources *path* (eg: src) and base API *namespace* (eg: Lucinda\Logging\) via namesake attributes
- **tests**: configures base path of tests *path* (eg: tests) and base API *namespace* (eg: Test\Lucinda\Logging\) via namesake attributes

These settings will be used to autoload classes test/sources classes whenever used: like in composer's case, you are required to fully qualify namespaces and end them with a backslash.

Optional tag **servers** stores connection settings for SQL servers that are going to be used in the unit tests, broken by **ENVIRONMENT** (value of ```php getenv("ENVIRONMENT") ```). Each server must reflect into a **server** subtag where connection is configured by following attributes:  

- *DRIVER*: (mandatory) name of SQL vendor, as recognized by PDO. Example: *mysql*
- *HOSTNAME*: (mandatory) current database server host name. Example: *127.0.0.1*
- *PORT*: (optional) current database server port number. Example: *3306*
- *USERNAME*: (mandatory) database server user name. Example: *root*
- *PASSWORD*: (mandatory) database server use password. Example: *my-password*
- *SCHEMA*: (optional) name of schema unit tests will run on. Example: *test_schema*
- *CHARSET*: (optional) default character set to use in connection. Example: *utf8*

Example: [unit tests](https://github.com/aherne/oauth2client/blob/master/unit-tests.xml) @ [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/master)

## Implementation

### Initialization

By simply running a [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/v2.0/src/Controller.php) implementation (see [installation](#installation) section), classes in *sources* folder are mirrored into *tests* folder according to following rules:

- original folder structure is preserved, only that classes are renamed (see below)
- original class and file name is preserved, only it has "Test" appended. So *MyClass* and *MyClass.php* is mirrored to *MyClassTest* and *MyClassTest.php*
- original namespace is preserved, only it has "Test" namespace prepended. So *Foo\Bar* is mirrored to *Test\Foo\Bar*
- only public methods of source classes are mirrored
- arguments and return type of source methods are ignored, so original *asd(string fgh): int* will be mirrored to *php asd()*
- all created methods will have empty bodies

This insures 100% coverage is maintained on every execution, leaving programmers to develop missing unit tests themselves

### Development

In order to be covered, each *tests* class public method MUST return either a single [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) instance or a list of former, depending on whether or not you desire one or more tests. Each test has a status (passed or not) and an optional message (containing details that identify test against siblings).

Example:

```php
namespace Test\Foo; // mirrors source namespace: Foo

class BarTest { // mirrors class: Bar
    public function asd(): Lucinda\UnitTest\Result // mirrors method: asd @ Bar 
    {
        $object = new \Foo\Bar(...);
        $data = $object->asd(...);
        // makes a single numeric assertion
        return (new Lucinda\UnitTest\Validator\Integers($data))->assertEquals(12);
    }

    public function fgh(): array // mirrors method: fgh @ Bar 
    {
        $results = [];
        $object = new \Foo\Bar(...);
        $data = $object->fgh(...);
        // makes multiple assertions on same value
        $test = new Lucinda\UnitTest\Validator\Arrays($data);
        $results[] = $test->assertNotEmpty("is it empty");
        $results[] = $test->assertContainsValue("qwerty");
        return $results;
    }
}
```

### Execution

By simply running a [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/v2.0/src/Controller.php) (see [installation](#installation) section) classes in *tests* folder are instanced, their public methods executed in the order they are set and [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) instances are collected. The logic is as following:

- if a class @ *src* has no mirror class @ *tests*, unit test is marked as failed for respective class!
- if a class @ *src* has public methods not present in mirror class @ *tests*, unit test is marked as failed for respective method!
- if any of methods of mirror class do not return a [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) or a list of latter, unit test is marked as failed for respective method with message that method is not covered
- results of unit tests are collected into a list of [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php)

This abstract class comes with following methods of interest:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | string $xmlFilePath, string $developmentEnvironment | void | Reads xml based on development environment, creates missing unit tests and executes them all for each API referenced |
| abstract protected handle | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php)[] | void | Handles unit test results by storing or displaying them. |

API comes already with two with two [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/v2.0/src/Controller.php) implementations:

- [Lucinda\UnitTest\ConsoleController](https://github.com/aherne/unit-testing/blob/v2.0/src/ConsoleController.php): displays unit test results in a table on console
- [Lucinda\UnitTest\JsonController](https://github.com/aherne/unit-testing/blob/v2.0/src/JsonController.php): displays unit test results as a json

Developers can build their own extensions that also save results somewhere...

## Installation

In folder where your API under testing resides, run this command in console:

```console
composer require lucinda/unit-testing
```

Then create a *unit-tests.xml* file holding configuration settings (see [configuration](#configuration) above) and a *test.php* file with following code:

```php
require(__DIR__."/vendor/autoload.php");
try {
	new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
	// handle exceptions
}
```

To see a live example of usage, check [unit tests](https://github.com/aherne/oauth2client/tree/master#unit-tests) for [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/master)!

## Assertions

### Assertions on Primitive Values

API allows you to make assertions on all PHP primitive data types:

- *integer*: via [Lucinda\UnitTest\Validator\Integers](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Integers.php)
- *float*: via [Lucinda\UnitTest\Validator\Floats](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Floats.php)
- *string*: via [Lucinda\UnitTest\Validator\Strings](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Strings.php)
- *boolean*: via [Lucinda\UnitTest\Validator\Booleans](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Booleans.php)
- *array*: via [Lucinda\UnitTest\Validator\Arrays](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Arrays.php)
- *object*: via [Lucinda\UnitTest\Validator\Objects](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Objects.php)

Each of these classes has a constructor in which a value of respective type is injected then a number of methods that make assertions on that value. In real life, you will only use those classes to make single assertions. 

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\Arrays($data);
return $test->assertNotEmpty("is it empty");
```

### Assertions on SQL Queries Results

Sometimes it is necessary to test information in database as well. For this you can use [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL.php) class provided by API, which has four public methods:


| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| static setDataSource | [Lucinda\UnitTest\Validator\SQL\DataSource](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL/DataSource.php) | void | Sets a data source encapsulating settings to use in connection to server later on |
| static getInstance | void | [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL.php) | Opens single connection to SQL server using PDO based on data source injected beforehand then starts a transaction |
| __destruct | void | void | rolls back transaction and closes connection to SQL server |
| assertStatement | string $query, [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL/ResultValidator.php) $validator | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Executes a SQL statement and asserts result by delegating to validator received as argument |
| assertPreparedStatement | string $query, array $boundParameters, [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL/ResultValidator.php) $validator | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Executes a SQL prepared statement and asserts result by delegating to validator received as argument |

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\SQL($dataSource);
$test->assertStatement("SELECT COUNT(id) AS nr FROM users", new class extends Lucinda\UnitTest\Validator\SQL\ResultValidator() {
    public function validate(\PDOStatement $statementResults): Result {
        $test = new Lucinda\UnitTest\Validator\Integer((integer) $statementResults->fetchColumn());
        return $test->assertEquals(8);
    }
});
```

Above mechanism allows you to develop MULTIPLE assertions on a single [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/SQL.php) instance, which in turn corresponds to a single SQL connection.

### Assertions on URL Execution Results

Sometimes it is necessary to test results of URL execution. For this you can use [Lucinda\UnitTest\Validator\URL](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL.php) class provided by API, which has two public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | [Lucinda\UnitTest\Validator\URL\DataSource](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL/DataSource.php) $dataSource | void | Opens connection to an URL using Lucinda\UnitTest\Validator\URL\Request based on information encapsulated by [Lucinda\UnitTest\Validator\URL\DataSource](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL/DataSource.php) then collects results into a [Lucinda\UnitTest\Validator\URL\Response](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL/Response.php) instance. |
| assert | [Lucinda\UnitTest\Validator\URL\ResultValidator](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL/ResultValidator.php) $validator | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Asserts response instance above by delegating to validator received as argument |

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\URL(new Lucinda\UnitTest\Validator\URL\DataSource("https://www.google.com"));
$test->assert(new class extends Lucinda\UnitTest\Validator\URL\ResultValidator() {
    public function validate(Lucinda\UnitTest\Validator\URL\Response $response): Result {
        $test = new Lucinda\UnitTest\Validator\Strings($response->getBody());
        return $test->assertContains("google");
    }
});
```

Above mechanism allows you to develop MULTIPLE assertions on same URL execution result via a single [Lucinda\UnitTest\Validator\URL](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/URL.php) instance.

### Assertions on Files

One can perform assertions on files by using [Lucinda\UnitTest\Validator\Files](https://github.com/aherne/unit-testing/blob/v2.0/src/Validator/Files.php) class, which comes with following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | string $path | void | Records path to file under testing |
| assertExists | string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Asserts if file exists |
| assertNotExists | string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Asserts if file not exists |
| assertContains | string $expected, string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Asserts if file contains expected string |
| assertNotContains | string $expected, string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Asserts if file doesn't contain expected string |
| assertSize | int $count, string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Assert if file is of expected size |
| assertNotSize | int $count, string $message="" | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/v2.0/src/Result.php) | Assert if file is not of expected size |

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\Files("foo/bar.php");
return $test->assertExists();
```

## Examples

This [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/master) is among others that use this API for unit testing, so check:

- [unit-tests.xml](https://github.com/aherne/oauth2client/blob/master/unit-tests.xml): for an example of configuration
- [test.php](https://github.com/aherne/oauth2client/blob/master/test.php): for an example of test suite executor
- [tests](https://github.com/aherne/oauth2client/tree/master/tests): for examples of unit tests of classes from [src](https://github.com/aherne/oauth2client/tree/master/src)
- [tests_drivers](https://github.com/aherne/oauth2client/tree/master/tests): for examples of unit tests of classes from [drivers](https://github.com/aherne/oauth2client/tree/master/drivers)
