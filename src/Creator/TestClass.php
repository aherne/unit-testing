<?php

namespace Lucinda\UnitTest\Creator;

/**
 * Creates or updates an unit test class
 */
class TestClass
{
    private string $testClassName;
    private string $testNamespace;
    private string $testFileLocation;

    /**
     * Sets up class information
     *
     * @param string $testClassName
     * @param string $testNamespace
     * @param string $testFileLocation
     */
    public function __construct(string $testClassName, string $testNamespace, string $testFileLocation)
    {
        $this->testClassName = $testClassName;
        $this->testNamespace = $testNamespace;
        $this->testFileLocation = $testFileLocation;
    }

    /**
     * Create an unit test class
     *
     * @param string[] $methods
     */
    public function create(array $methods): void
    {
        $source = '<?php
'.($this->testNamespace ? 'namespace '.$this->testNamespace.';' : '').'
    
class '.$this->testClassName.'
{
';
        foreach ($methods as $method) {
            $source .= '
    public function '.$method.'()
    {
    }
        
';
        }
        $source .= '
}
';
        $directory = substr($this->testFileLocation, 0, strrpos($this->testFileLocation, "/"));
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($this->testFileLocation, $source);
    }

    /**
     * Appends new methods to existing unit test class
     *
     * @param string[] $methods
     */
    public function update(array $methods): void
    {
        $additions = '';
        foreach ($methods as $method) {
            // create method
            $additions .= '
    public function '.$method.'()
    {
    }
        
';
        }
        $source = file_get_contents($this->testFileLocation);
        $position = strrpos($source, "}");
        $source = substr($source, 0, $position-1).$additions.'
}
';
        file_put_contents($this->testFileLocation, $source);
    }
}
