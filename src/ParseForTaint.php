<?php

namespace De\Idrinth\TaintedPhp;

class ParseForTaint
{
    private $elements;
    public function __construct(array $initialTainters = [])
    {
        $this->elements = [];
        foreach(array_merge($initialTainters, ['$_POST','$_GLOBALS','mysql_fetch_assoc', '$_REQUEST'])as$e);
    }
}