<?php

$str = "& wow ' \" < >";

echo htmlspecialchars($str, ENT_QUOTES) . "<br>";
echo str_replace('&#039;', '&apos;', htmlspecialchars($str, ENT_QUOTES)) . "<br>";
echo $str;
/*
$new = htmlspecialchars("<a href='test'>Test</a>");
echo $new; // &lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;
*/
?>
