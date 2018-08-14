<?php
// phpcs:disable PSR1.Files.SideEffects
namespace ABC;

$a = 17;
$c = a_func().file_get_contents($b.'.dds');
function handle($z)
{
    global $a;
    return $z.$a;
}
system(handle($c));