<?php

namespace De\Idrinth\Test\TaintedPhp\ParseForTaint;

use De\Idrinth\Test\TaintedPhp\ParseForTaintTest;

class SimpleClassTest extends ParseForTaintTest
{
    private static $expected = [
        'Qq\ForMe\ABC::a()' => [],
        'Qq\ForMe\ABC::a()#0' => [],
        'Qq\ForMe\ABC::a()$r' => ['Qq\ABC::a()#0'],
        'Qq\ForMe\ABC::a()#1' => [],
        'Qq\ForMe\ABC::a()$z' => ['Qq\ABC::a()#1'],
        'Qq\ForMe\ABC::b()' => [],
        'Qq\ForMe\ABC::b()#0' => ['Qq\ABC::b()#0'],
        'Qq\ForMe\ABC::b()$q' => [],
        'implode' => ['Qq\ForMe\ABC::a'],
        'Qq\ForMe\implode' => ['Qq\ForMe\ABC::a'],
        'system' => ['Qq\ForMe\implode', 'implode'],
        'Qq\ForMe\system' => ['Qq\ForMe\implode', 'implode'],
    ];
    protected function getExpectedOutcome()
    {self::markTestIncomplete();
        return self::$expected;
    }
}
