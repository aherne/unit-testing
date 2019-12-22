# PHP Unit Testing API

This library was in part created out of frustration while working with PHPUnit, the standard solution used by over 99% of PHP applications that feature unit testing. Everything about that old API reminds us of bygone ages when developers built huge classes that do "everything", knew nothing about encapsulation except keyword "extends". That is the fundamental "architecture" principle of PHPUnit\Framework\TestCase: https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php, a monster that tries to do everything in ugliest way possible.

Should unit testing logic abide to good principles of object oriented programming or only the code that is being tested? IMHO, as long as a developer feels confortable working with a mess, it will become a bad precedent to build something similar later on. This API aims at building something that PHPUnit is not: a cleanly coded, zero dependencies API standing on three pylons:

- *creation*: automated creation of unit testing architecture (classes and methods) for target API under testing
- *development*: user development of one or more unit tests for each class method created above
- *execution*: automated execution of unit tests on above foundations
- *configuration*: (optional) sets up sql/nosql connection and credentials useful when you want to develop unit tests that use databases

## INSTALLATION & USAGE

To install it, you only need to add following line to *require-dev* in your **composer.json** file:

```json
"lucinda/unit-testing": "~1.0"
```

Then, run:

```bash
composer update
```

To create unit test classes and methods based on classes under testing, run a PHP file in your API root with following content:

```php
require __DIR__ . '/vendor/autoload.php';
try {
	new Lucinda\UnitTest\Creator(__DIR__);
} catch (Exception $e) {
	// handle exceptions
}

```

To execute all unit tests and display them in unix console, run a PHP file in your API root with following content:

```php
require __DIR__ . '/vendor/autoload.php';
try {
	new Lucinda\UnitTest\UnixConsoleRunner(__DIR__);
} catch (Exception $e) {
	// handle exceptions
}
```

Both of above assume you are using standard *src* and *tests* folders in API root. If not, check complete constructors signature in documentation below. 

If you're planning to customize unit tests or make SQL assertions as well, change above code to:

```php
require __DIR__ . '/vendor/autoload.php';
try {
	new Lucinda\UnitTest\Configuration({XML_LOCATION}, {DEVELOPMENT_ENVIRONMENT});
	new Lucinda\UnitTest\UnixConsoleRunner(__DIR__);
} catch (Exception $e) {
	// handle exceptions
}
```

Where:

- *XML_LOCATION*: relative or absolute location of XML file that configures unit tests
- *DEVELOPMENT_ENVIRONMENT*: name of current development environment (eg: home, dev, live) that must reflect into a tag in XML above

For more info into above, check CONFIGURATION section below!

## DOCUMENTATION

### CREATION

To create unit test classes and methods automatically, you only need to use **Lucinda\UnitTest\Creator**. Its constructor has following signature:

- *public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests")*: performs creation logic

Example:

```php
new Lucinda\UnitTest\Creator("/home/aherne/apis/php-servlets-api");
```

This will mirror all classes in *src* folder into *tests* folder within */home/aherne/apis/php-servlets-api* according to following rules:

- original folder structure is preserved, only that classes are renamed (see below)
- original class and file name is preserved, only it has "Test" appended. So *MyClass* and *MyClass.php* is mirrored to *MyClassTest* and *MyClassTest.php*
- original namespace is preserved, only it has "Test" namespace prepended. So *Foo\Bar* is mirrored to *Test\Foo\Bar*
- only public methods of source classes are mirrored
- arguments and return type of source methods are ignored, so original *public function asd(string fgh): int* will be mirrored to *php public function asd()*
- all created methods will have empty bodies

If you added another method to any of original classes, you will only need to instance Lucinda\UnitTest\Creator again in order to add the new test methods.

### DEVELOPMENT

In order to be covered, each public method of class created MUST return either a single **Lucinda\UnitTest\Result** instance or a list of **Lucinda\UnitTest\Result** instances, depending on whether or not you desire one or more tests. Each test has a status (passed or not) and an optional message (containing details that identify test against siblings).

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

#### ASSERTIONS ON PRIMITIVE VALUES

API allows you to make assertions on all PHP primitive data types:

- *integer*: via **Lucinda\UnitTest\Validator\Integers**
- *float*: via **Lucinda\UnitTest\Validator\Floats**
- *string*: via **Lucinda\UnitTest\Validator\Strings**
- *boolean*: via **Lucinda\UnitTest\Validator\Booleans**
- *array*: via **Lucinda\UnitTest\Validator\Arrays**
- *object*: via **Lucinda\UnitTest\Validator\Objects**

Each of these classes has a constructor in which a value of respective type is injected then a number of methods that make assertions on that value. In real life, you will only use those classes to make single assertions. 

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\Arrays($data);
return $test->assertNotEmpty("is it empty");
```

#### ASSERTIONS ON SQL QUERIES RESULTS

Sometimes it is necessary to test information in database as well. For this you can use **Lucinda\UnitTest\Validator\SQL** class provided by API, which has three public methods:

- *public static function setDataSource(Lucinda\UnitTest\Validator\SQL\DataSource $dataSource)*: sets information required in opening a connection later on (eg: driver, user and password)
- *public function __construct()*: opens connection to SQL server using PDO based on information encapsulated by **Lucinda\UnitTest\Validator\SQL\DataSource**
- *public function assertStatement(string $query, Lucinda\UnitTest\Validator\SQL\ResultValidator $validator): Result*: executes a SQL statement and asserts results by delegating to a **Lucinda\UnitTest\Validator\SQL\ResultValidator** instance implemented by developers
- *public function assertPreparedStatement(string $query, array $boundParameters, Lucinda\UnitTest\Validator\SQL\ResultValidator $validator): Result*: executes a SQL prepared statement and asserts results by delegating to a **Lucinda\UnitTest\Validator\SQL\ResultValidator** instance implemented by developers

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

Above mechanism allows you to develop MULTIPLE assertions on a single UnitTest\Validator\SQL instance, which in turn corresponds to a single SQL connection.

#### ASSERTIONS ON URL EXECUTION RESULTS

Sometimes it is necessary to test results of URL execution. For this you can use **Lucinda\UnitTest\Validator\URL** class provided by API, which has two public methods:

- *public function __construct(Lucinda\UnitTest\Validator\URL\DataSource $dataSource)*: opens connection to an URL using Lucinda\UnitTest\Validator\URL\Request based on information encapsulated by **Lucinda\UnitTest\Validator\URL\DataSource** then collects results into a Lucinda\UnitTest\Validator\URL\Response instance.
- *public function assert(Lucinda\UnitTest\Validator\URL\ResultValidator $validator): Result*: asserts results by delegating to a **Lucinda\UnitTest\Validator\URL\ResultValidator** instance implemented by developers

Assertion example:

```php
$test = new Lucinda\UnitTest\Validator\URL(new Lucinda\UnitTest\Validator\URL\DataSource("https://www.google.com"));
$test->assertStatement("SELECT COUNT(id) AS nr FROM users", new class extends Lucinda\UnitTest\Validator\SQL\ResultValidator() {
    public function validate(Lucinda\UnitTest\Validator\URL\Response $response): Result {
        $test = new Lucinda\UnitTest\Validator\Strings($response->getBody());
        return $test->assertContains("google");
    }
});
```

Above mechanism allows you to develop MULTIPLE assertions on same URL execution result via a single UnitTest\Validator\URL instance.

#### ASSERTIONS ON FILES

One can perform assertions on files by using **UnitTest\Validator\Files** class, which comes with following public methods:

- *public function __construct(string $path)*
- *public function assertExists(string $message=""): Result*: asserts if file exists
- *public function assertNotExists(string $message=""): Result*: asserts if file not exists
- *public function assertContains(string $expected, string $message=""): Result*: asserts if file contains expected string
- *public function assertNotContains(string $expected, string $message=""): Result*: asserts if file doesn't contain expected string
- *public function assertSize(int $count, string $message=""): Result*: assert if file is of expected size
- *public function assertNotSize(int $count, string $message=""): Result*: assert if file is not of expected size

### EXECUTION

Execution works in a way similar to CREATION only that its purpose is to FIND & RUN unit tests instead of CREATING them. All of this is done by Lucinda\UnitTest\Runner, which comes with two methods of interest:

- *public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests")*: performs execution logic
- *abstract protected function display(array $results): void*: displays results of unit tests (must be implemented by children)

As once can notice above, because while there is a single logic for FIND & RUN, there is no single logic of displaying results. For that reason, class was made abstract and thus MUST be extended in order to display results:

- on unix terminal: using **Lucinda\UnitTest\UnixConsoleRunner**
- on windows command prompt: using **Lucinda\UnitTest\WindowsConsoleRunner**
- as a JSON: using using **Lucinda\UnitTest\JsonRunner**

Example:

```php
new Lucinda\UnitTest\UnixConsoleRunner("/home/aherne/apis/php-servlets-api");
```

Above will loop recursively through all classes in *src* folder, match them to a mirror in *tests* folder within */home/aherne/apis/php-servlets-api*,
instance each found class, execute its public method and collect **Lucinda\UnitTest\Result** instances returned. The logic is as following:

- if a class @ *src* has no mirror class @ *tests*, unit test is marked as failed for respective class and Lucinda\UnitTest\Creator must be ran!
- if a class @ *src* has public methods not present in mirror class @ *tests*, unit test is marked as failed for respective method and Lucinda\UnitTest\Creator must be ran!
- if any of methods of mirror class do not return a Lucinda\UnitTest\Result or a list of latter, unit test is marked as failed for respective method with message that method is not covered
- results of unit tests are collected into a list of Lucinda\UnitTest\Result

## CONFIGURATION

This is an optional feature required to configure unit testing (currently only ASSERTIONS ON SQL QUERIES RESULTS). Similar to PHPUnit, it is done via an XML file with following structure:

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xml>
<xml>
  <servers>
    <sql>
      <{ENVIRONMENT}>
        <server driver="{DRIVER}" host="{HOSTNAME}" port="{PORT}" username="{USERNAME}" password="{PASSWORD}" schema="{SCHEMA}" charset="{CHARSET}"/>
        ...
      </{ENVIRONMENT}>
      ...
    </sql>
  </servers>
</xml>
``` 

Where:

- *ENVIRONMENT*: development environment, value of ```php getenv("ENVIRONMENT") ```. Example: *live*
- *DRIVER*: (mandatory) name of SQL vendor, as recognized by PDO. Example: *mysql*
- *HOSTNAME*: (mandatory) current database server host name. Example: *127.0.0.1*
- *PORT*: (optional) current database server port number. Example: *3306*
- *USERNAME*: (mandatory) database server user name. Example: *root*
- *PASSWORD*: (mandatory) database server use password. Example: *my-password*
- *SCHEMA*: (optional) name of schema unit tests will run on. Example: *test_schema*
- *CHARSET*: (optional) default character set to use in connection. Example: *utf8*

Above file is parsed by **Lucinda\UnitTest\Configuration** class via its constructor:

- *public function __construct(string $xmlFilePath, string $developmentEnvironment)*

Above will locate &lt;server&gt; tag that matches current ENVIRONMENT, build a Lucinda\UnitTest\SQL\DataSource and injects it statically into Lucinda\UnitTest\SQL 
in order to be used in connections later on.