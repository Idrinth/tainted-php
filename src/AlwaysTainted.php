<?php

namespace De\Idrinth\TaintedPhp;

class AlwaysTainted extends TaintedIf
{
    public function isTainted() {
        return true;
    }
}
