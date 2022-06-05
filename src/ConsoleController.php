<?php

namespace Lucinda\UnitTest;

use Lucinda\Console\BackgroundColor;
use Lucinda\Console\FontStyle;
use Lucinda\Console\Table;
use Lucinda\Console\Text;
use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests on unix console and displays results
 */
class ConsoleController extends Controller
{
    /**
     * Displays results of unit tests
     *
     * @param  UnitTest[] $results
     * @throws \Exception
     */
    protected function handle(array $results): void
    {
        $totals = ["passed"=>0, "failed"=>0];

        $columns = ["Class", "Method", "Status", "Description"];
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $object = new Table(
                array_map(
                    function ($column) {
                        $text = new Text($column);
                        $text->setFontStyle(FontStyle::BOLD);
                        return $text;
                    },
                    $columns
                )
            );
            foreach ($results as $unitTest) {
                $status = null;
                if ($unitTest->result->hasPassed()) {
                    $status = new Text(" PASSED ");
                    $status->setBackgroundColor(BackgroundColor::GREEN);
                } else {
                    $status = new Text(" FAILED ");
                    $status->setBackgroundColor(BackgroundColor::RED);
                }
                $object->addRow(
                    [
                    $unitTest->className,
                    $unitTest->methodName,
                    $status,
                    $unitTest->result->getMessage()
                    ]
                );
                $totals[$unitTest->result->hasPassed() ? "passed" : "failed"]++;
            }
            echo $object->__toString()."\n";
        } else {
            $object = new Table(
                array_map(
                    function ($column) {
                        return strtoupper($column);
                    },
                    $columns
                )
            );
            foreach ($results as $unitTest) {
                $object->addRow(
                    [
                    $unitTest->className,
                    $unitTest->methodName,
                    ($unitTest->result->hasPassed() ? "PASSED" : "FAILED"),
                    $unitTest->result->getMessage()
                    ]
                );
                $totals[$unitTest->result->hasPassed() ? "passed" : "failed"]++;
            }
            echo $object->__toString()."\n";
        }

        echo "Total: ".($totals["passed"]+$totals["failed"])." (".$totals["passed"]." passed, ".$totals["failed"]." failed)\n";
    }
}
