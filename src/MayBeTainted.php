<?php

namespace De\Idrinth\TaintedPhp;

class MayBeTainted extends TaintedIf
{
    public function mayBeTainted()
    {
        return true;
    }
}
