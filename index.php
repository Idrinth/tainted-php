<?php

require_once 'vendor/autoload.php';

$parser = new De\Idrinth\TaintedPhp\ParseForTaint();
$parser->parse(__DIR__.'/test.php');
echo "$parser";