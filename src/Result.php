<?php
namespace Lucinda\UnitTest;

/**
 * Encapsulates results of a test round.
 */
class Result
{
    private $status;
    private $message;

    /**
     * UnitTestResult constructor.
     * @param boolean $status Validation status: normally a boolean (whether or not it has passed)
     * @param string $payload Validation message: normally failure payload that goes along with not passing status
     */
    public function __construct(bool $status, string $message="")
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Gets validation status: normally a boolean (whether or not it has passed)
     *
     * @return boolean
     */
    public function hasPassed(): bool
    {
        return $this->status;
    }

    /**
     * Gets validation message: normally failure payload that goes along with not passing status
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
