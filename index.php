<?php

require_once 'vendor/autoload.php';

var_dump(implode("\n", (new De\Idrinth\TaintedPhp\ParseForTaint())->parse(__DIR__.'/test.php')));