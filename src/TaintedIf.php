<?php

namespace De\Idrinth\TaintedPhp;

class TaintedIf
{
    private $name;
    /**
     * @var TaintedIf[]
     */
    private $taintedBy = [];
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function isTainted()
    {
        foreach ($this->taintedBy as $taintedIf)
        {
            if($taintedIf->isTainted()) {
                return true;
            }
        }
        return false;
    }
    public function mayBeTainted()
    {
        foreach ($this->taintedBy as $taintedIf)
        {
            if($taintedIf->isTainted()) {
                return true;
            }
        }
        return true;
    }
}