<?php
$d = json_decode(file_get_contents('ubigeos/1_ubigeo_departamentos.json'), true)['ubigeo_departamentos'][0];
$p = json_decode(file_get_contents('ubigeos/2_ubigeo_provincias.json'), true)['ubigeo_provincias'][0];
$t = json_decode(file_get_contents('ubigeos/3_ubigeo_distritos.json'), true)['ubigeo_distritos'][0];
print_r($d);
print_r($p);
print_r($t);
?>
