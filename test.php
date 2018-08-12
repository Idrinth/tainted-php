<?php

$a = 17;
$b = $_POST['c'];
file_get_contents($b.'.dds');
function handle()
{
    global $a;
    $r = $a;
    return $r;
}