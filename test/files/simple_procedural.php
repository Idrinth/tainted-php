<?php
// phpcs:disable PSR1.Files.SideEffects
$a = 17;
$b = $_POST['c'];
$c = a_func().file_get_contents($b.'.dds');
function handle($z)
{
    global $a;
    global $link;
    $r = $a;
    $d = mysqli_fetch_all(mysqli_query($link, $z));
    return $r.$d;
}
eval(handle($c));
function doWork($zzz)
{
    global $link;
    return mysql_query($link, $zzz);
}
$q = include $b;
