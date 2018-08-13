<?php

require_once __DIR__.'/../vendor/autoload.php';

$parser = new De\Idrinth\TaintedPhp\ParseForTaint();
$parser->parse($file);
echo "$parser";