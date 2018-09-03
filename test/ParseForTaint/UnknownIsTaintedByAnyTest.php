<?php

namespace De\Idrinth\Test\TaintedPhp\ParseForTaint;

use De\Idrinth\Test\TaintedPhp\ParseForTaintTest;

class UnknownIsTaintedByAnyTest extends ParseForTaintTest
{
    private static $expected = [
        'mysql_query()#1' => [],
        'mysqli_query()#1' => [],
        'eval()#0' => ['handle()'],
        'file_get_contents()#0' => [],
        'file_put_contents()#0' => [],
        'exec()#0' => [],
        'system()#0' => ['get_my_very_own_global()'],
        'proc_open()#0' => [],
        'passthru()#0' => [],
        'shell_exec()#0' => [],
        'PDO->query()#0' => [],
        'PDO->execute()#0' => [],
        'PDO->prepare()#0' => [],
        'basename()#0' => [],
        'dirname()#0' => [],
        'file()#0' => [],
        'fopen()#0' => [],
        'require()#0' => [],
        'require_once()#0' => [],
        'include()#0' => [],
        'include_once()#0' => [],
        'get_my_very_own_global()' => [],
        'get_my_very_own_global()#1' => [],
    ];
    protected function getExpectedOutcome()
    {
        return self::$expected;
    }
}
