# PHP Unit Testing API

This library was in part created out of frustration while working with PHPUnit, the standard solution used by over 99% of PHP applications that feature unit testing. Everything about that old API reminds us of bygone ages when developers built huge classes that do "everything", knew nothing about encapsulation except keyword "extends". That is the fundamental "architecture" principle of PHPUnit\Framework\TestCase: https://github.com/sebastianbergmann/phpunit/blob/master/src/Framework/TestCase.php, a monster that tries to do everything in ugliest way possible.

Should unit testing logic abide to good principles of object oriented programming or only the code that is being tested? IMHO, as long as a developer feels confortable working with a mess, it will become a bad precedent to build something similar later on. This API aims at building something that PHPUnit is not: a cleanly coded, zero dependencies API standing on three pylons:

- *creation*: automated creation of unit testing architecture (classes and methods) for target API under testing
- *development*: user development of one or more unit tests for each class method created above
- *execution*: automated execution of unit tests on above foundations

## CREATION

To create unit test classes and methods, you only need to use **Lucinda\UnitTest\Creator**. Its constructor has following signature:

- *public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests")*

Example:

```php
new Lucinda\UnitTest\Creator("/home/aherne/apis/php-servlets-api");
```

This will mirror all classes in *$sourcesFolder* into *$testsFolder* according to following rules:

- original folder structure is preserved, only that classes are renamed (see below)
- original class and file name is preserved, only it has "Test" appended. So *MyClass* and *MyClass.php* is mirrored to *MyClassTest* and *MyClassTest.php*
- original namespace is preserved, only it has "Test" namespace prepended. So *Foo\Bar* is mirrored to *Test\Foo\Bar*
- only public methods of source classes are mirrored
- arguments and return type of source methods are ignored, so original *public function asd(string fgh): int* will be mirrored to *php public function asd()*
- all created methods will have empty bodies

## DEVELOPMENT

In order to be covered, each public method of class created MUST return either a single **Lucinda\UnitTest\Result** instance or a list of **Lucinda\UnitTest\Result** instances, depending on whether or not you desire one or more tests. Each test has a status (passed or not) and an optional message (containing details that identify test against siblings).

Example:

```php
// here you must require composer autoload or class under testing itself
class BarTest {
    public function asd()
    {
        $object = new Foo\BAR(...);
        $data = $object->asd(...);
        // makes a single numeric assertion
        return (new Lucinda\UnitTest\Validator\Integers($data))->assertEquals(12);
    }

    public function fgh()
    {
        $results = [];
        $object = new Foo\BAR(...);
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

- *integer*: via **Lucinda\UnitTest\Validator\Integers**
- *float*: via **Lucinda\UnitTest\Validator\Floats**
- *string*: via **Lucinda\UnitTest\Validator\Strings**
- *boolean*: via **Lucinda\UnitTest\Validator\Booleans**
- *array*: via **Lucinda\UnitTest\Validator\Arrays**
- *object*: via **Lucinda\UnitTest\Validator\Objects**

Each of these classes has a constructor in which a value of respective type is injected then a number of methods that make assertions on that value. In real life, you will only use those classes to make single assertions. 

Assertion example:

$test = new Lucinda\UnitTest\Validator\Arrays($data);
return $test->assertNotEmpty("is it empty");

### ASSERTIONS ON SQL QUERIES RESULTS

Sometimes it is necessary to test information in database as well. For this you can use **Lucinda\UnitTest\Validator\SQL** class provided by API, which has three public methods:

- *public function __construct(Lucinda\UnitTest\Validator\SQL\DataSource $dataSource)*: opens connection to SQL server using PDO based on information encapsulated by **Lucinda\UnitTest\Validator\SQL\DataSource**
- *public function assertStatement(string $query, Lucinda\UnitTest\Validator\SQL\ResultValidator $validator): Result*: executes a SQL statement and asserts results by delegating to a **Lucinda\UnitTest\Validator\SQL\ResultValidator** instance implemented by developers
- *public function assertPreparedStatement(string $query, array $boundParameters, Lucinda\UnitTest\Validator\SQL\ResultValidator $validator): Result*: executes a SQL prepared statement and asserts results by delegating to a **Lucinda\UnitTest\Validator\SQL\ResultValidator** instance implemented by developers

Assertion example:

```php
//instances and feeds $dataSource
$test = new Lucinda\UnitTest\Validator\SQL($dataSource);
$test->assertStatement("SELECT COUNT(id) AS nr FROM users", new class extends Lucinda\UnitTest\Validator\SQL\ResultValidator() {
    public function validate(\PDOStatement $statementResults): Result {
        $test = new Lucinda\UnitTest\Validator\Integer((integer) $statementResults->fetchColumn());
        return $test->assertEquals(8);
    }
});
```

Above mechanism allows you to develop MULTIPLE assertions on a single UnitTest\Validator\SQL instance, which in turn corresponds to a single SQL connection.

### ASSERTIONS ON URL EXECUTION RESULTS

Sometimes it is necessary to test results of URL execution. For this you can use **Lucinda\UnitTest\Validator\URL** class provided by API, which has two public methods:

- *public function __construct(Lucinda\UnitTest\Validator\URL\DataSource $dataSource)*: opens connection to an URL using Lucinda\UnitTest\Validator\URL\Request based on information encapsulated by **Lucinda\UnitTest\Validator\URL\DataSource** then collects results into a Lucinda\UnitTest\Validator\URL\Response instance.
- *public function assert(Lucinda\UnitTest\Validator\URL\ResultValidator $validator): Result*: asserts results by delegating to a **Lucinda\UnitTest\Validator\URL\ResultValidator** instance implemented by developers

Assertion example:

```php
// instances and feeds $dataSource
$test = new Lucinda\UnitTest\Validator\URL(new Lucinda\UnitTest\Validator\URL\DataSource("https://www.google.com"));
$test->assertStatement("SELECT COUNT(id) AS nr FROM users", new class extends Lucinda\UnitTest\Validator\SQL\ResultValidator() {
    public function validate(Lucinda\UnitTest\Validator\URL\Response $response): Result {
        $test = new Lucinda\UnitTest\Validator\Strings($response->getBody());
        return $test->assertContains("google");
    }
});
```

Above mechanism allows you to develop MULTIPLE assertions on same URL execution result via a single UnitTest\Validator\URL instance.

### ASSERTIONS OF DAO CLASSES

The most difficult part of any unit testing API is providing an ability to test logic of classes whose methods internally perform operations on database (sql or nosql). There are two basic ways of doing this:

- using mocks, WITHOUT testing database itself
- using transactions to restore database to its previous state after tests have ran 

## EXECUTION
