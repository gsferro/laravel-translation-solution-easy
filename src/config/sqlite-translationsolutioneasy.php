<?php

return [
    "connection" => ":database-sqlite",
];

$base = [":database-sqlite", ":database", ":database-sqlite",'teste'];
$base_convert = json_encode($base);
$base_convert = str_replace( ":database-sqlite", "nome-novo", $base_convert);
$basket = json_decode($base_convert);

$replacements = [":database-sqlite" => "nome-novo"];

$basket = array_replace($base, $replacements);
print_r($basket);