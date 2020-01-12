# PHP Unit Testing API

This library was in part created out of frustration while working with PHPUnit, the standard solution used by over 99% of PHP applications that feature unit testing. Everything about that old API reminds us of bygone ages when developers built huge classes that do "everything", knew nothing about encapsulation except keyword "extends". That is the fundamental "architecture" principle of PHPUnit\Framework\TestCase: [https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php](https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php), a monster that tries to do everything in ugliest way possible. Should unit testing logic abide to good principles of object oriented programming or only the code that is being tested? IMHO, as long as a developer feels confortable working with a mess, it will become a bad precedent to build something similar later on. 

This API aims at building something that PHPUnit is not: a cleanly coded, zero dependencies API requiring you to only follow these steps:

- [configuration](#configuration): setting up an XML file where unit testing is configured
- [initialization](#initialization): automated creation of unit testing architecture (classes and methods) for target API under testing
- [development](#development): user development of one or more unit tests for each class method created above
- [execution](#execution): automated execution of unit tests on above foundations and display of unit test results on console or as JSON

API is fully PSR-4 compliant, only requiring PHP7.1+ interpreter and SimpleXML + cURL + PDO extensions (latter for URI and SQL testing). To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[examples](https://github.com/aherne/oauth2client/tree/v3.0.0#unit-tests)**: shows real life unit tests for [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/v3.0.0)

## CONFIGURATION

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

Example: [unit tests](https://github.com/aherne/oauth2client/blob/v3.0.0/unit-tests.xml) @ [OAuth2 Client API](https://github.com/aherne/oauth2client/tree/v3.0.0)

## INITIALIZATION

By simply running a [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/master/src/Controller.php) implementation (see [installation](#installation) section), classes in *sources* folder are mirrored into *tests* folder according to following rules:

- original folder structure is preserved, only that classes are renamed (see below)
- original class and file name is preserved, only it has "Test" appended. So *MyClass* and *MyClass.php* is mirrored to *MyClassTest* and *MyClassTest.php*
- original namespace is preserved, only it has "Test" namespace prepended. So *Foo\Bar* is mirrored to *Test\Foo\Bar*
- only public methods of source classes are mirrored
- arguments and return type of source methods are ignored, so original *asd(string fgh): int* will be mirrored to *php asd()*
- all created methods will have empty bodies

This insures 100% coverage is maintained on every execution, leaving programmers to develop missing unit tests themselves

## DEVELOPMENT

In order to be covered, each *tests* class public method MUST return either a single [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php) instance or a list of former, depending on whether or not you desire one or more tests. Each test has a status (passed or not) and an optional message (containing details that identify test against siblings).

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

### ASSERTIONS ON PRIMITIVE VALUES

API allows you to make assertions on all PHP primitive data types:

- *integer*: via [Lucinda\UnitTest\Validator\Integers](https://github.com/aherne/unit-testing/blob/master/src/Validator/Integers.php)
- *float*: via [Lucinda\UnitTest\Validator\Floats](https://github.com/aherne/unit-testing/blob/master/src/Validator/Floats.php)
- *string*: via [Lucinda\UnitTest\Validator\Strings](https://github.com/aherne/unit-testing/blob/master/src/Validator/Strings.php)
- *boolean*: via [Lucinda\UnitTest\Validator\Booleans](https://github.com/aherne/unit-testing/blob/master/src/Validator/Booleans.php)
- *array*: via [Lucinda\UnitTest\Validator\Arrays](https://github.com/aherne/unit-testing/blob/master/src/Validator/Arrays.php)
- *object*: via [Lucinda\UnitTest\Validator\Objects](https://github.com/aherne/unit-testing/blob/master/src/Validator/Objects.php)

Each of these classes has a constructor in which a value of respective type is injected then a number of methods that make assertions on that value. In real life, you will only use those classes to make single assertions. 

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\Arrays($data);
return $test->assertNotEmpty("is it empty");
```

### ASSERTIONS ON SQL QUERIES RESULTS

Sometimes it is necessary to test information in database as well. For this you can use [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL.php) class provided by API, which has four public methods:


| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| static getInstance | void | [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL.php) | opens single connection to SQL server using PDO based on information encapsulated by [Lucinda\UnitTest\Validator\SQL\DataSource](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL/DataSource.php) injected beforehand then starts a transaction |
| __destruct | void | void | rolls back transaction and closes connection to SQL server |
| assertStatement | string $query, [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL/ResultValidator.php) $validator | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php) | executes a SQL statement and asserts result by delegating to a [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL/ResultValidator.php) instance implemented by developers |
| assertPreparedStatement | string $query, array $boundParameters, [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL/ResultValidator.php) $validator | [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php) | executes a SQL prepared statement and asserts result by delegating to a [Lucinda\UnitTest\Validator\SQL\ResultValidator](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL/ResultValidator.php) instance implemented by developers |

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

Above mechanism allows you to develop MULTIPLE assertions on a single [Lucinda\UnitTest\Validator\SQL](https://github.com/aherne/unit-testing/blob/master/src/Validator/SQL.php) instance, which in turn corresponds to a single SQL connection.

### ASSERTIONS ON URL EXECUTION RESULTS

Sometimes it is necessary to test results of URL execution. For this you can use [Lucinda\UnitTest\Validator\URL](https://github.com/aherne/unit-testing/blob/master/src/Validator/URL.php) class provided by API, which has two public methods:

- *__construct([Lucinda\UnitTest\Validator\URL\DataSource](https://github.com/aherne/unit-testing/blob/master/src/Validator/URL/DataSource.php) $dataSource)*: opens connection to an URL using Lucinda\UnitTest\Validator\URL\Request based on information encapsulated by **Lucinda\UnitTest\Validator\URL\DataSource** then collects results into a Lucinda\UnitTest\Validator\URL\Response instance.
- *assert([Lucinda\UnitTest\Validator\URL\ResultValidator](https://github.com/aherne/unit-testing/blob/master/src/Validator/URL/ResultValidator.php) $validator)*: asserts results by delegating to a **Lucinda\UnitTest\Validator\URL\ResultValidator** instance implemented by developers

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

Above mechanism allows you to develop MULTIPLE assertions on same URL execution result via a single [Lucinda\UnitTest\Validator\URL](https://github.com/aherne/unit-testing/blob/master/src/Validator/URL.php) instance.

### ASSERTIONS ON FILES

One can perform assertions on files by using [Lucinda\UnitTest\Validator\Files](https://github.com/aherne/unit-testing/blob/master/src/Validator/Files.php) class, which comes with following public methods:

- *__construct(string $path)*: records path to file under testing
- *assertExists(string $message="")*: asserts if file exists
- *assertNotExists(string $message="")*: asserts if file not exists
- *assertContains(string $expected, string $message="")*: asserts if file contains expected string
- *assertNotContains(string $expected, string $message="")*: asserts if file doesn't contain expected string
- *assertSize(int $count, string $message="")*: assert if file is of expected size
- *assertNotSize(int $count, string $message="")*: assert if file is not of expected size

## EXECUTION

By simply running a [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/master/src/Controller.php) (see [installation](#installation) section) classes in *tests* folder are instanced, their public methods executed in the order they are set and [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php) instances are collected. The logic is as following:

- if a class @ *src* has no mirror class @ *tests*, unit test is marked as failed for respective class!
- if a class @ *src* has public methods not present in mirror class @ *tests*, unit test is marked as failed for respective method!
- if any of methods of mirror class do not return a [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php) or a list of latter, unit test is marked as failed for respective method with message that method is not covered
- results of unit tests are collected into a list of [Lucinda\UnitTest\Result](https://github.com/aherne/unit-testing/blob/master/src/Result.php)

This abstract class comes with following methods of interest:

- *__construct(string $xmlFilePath, string $developmentEnvironment)*: reads configuration xml based on development environment, creates missing unit tests and executes them all for each API referenced
- *abstract protected function handle(array $results*: handles unit test results by storing or displaying them.

API comes already with two with two [Lucinda\UnitTest\Controller](https://github.com/aherne/unit-testing/blob/master/src/Controller.php) implementations:

- [Lucinda\UnitTest\ConsoleController](https://github.com/aherne/unit-testing/blob/master/src/ConsoleController.php): displays unit test results in a table on console
- [Lucinda\UnitTest\JsonController](https://github.com/aherne/unit-testing/blob/master/src/JsonController.php): displays unit test results as a json

Developers can build their own extensions that also save results somewhere...


## INSTALLATION

This library is fully PSR-4 compliant and only requires PHP7.1+ interpreter. For installation run:

```console
composer require lucinda/unit-testing
```

To create (if not found already), execute unit tests and display them in console, run a PHP file in your API root with following content:

```php
require(__DIR__."/vendor/autoload.php");
try {
	new Lucinda\UnitTest\ConsoleController(XML_FILE_NAME, DEVELOPMENT_ENVIRONMENT);
} catch (Exception $e) {
	// handle exceptions
}
```

Where:

- *XML_FILE_NAME*: relative or absolute location of XML file that configures unit tests (see below about its syntax and structure)
- *DEVELOPMENT_ENVIRONMENT*: name of current development environment (eg: home, dev, live) that must reflect into a child tag of server in XML above

For more info into above, check CONFIGURATION section below!

