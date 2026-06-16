<?php
$urls = [
    "https://raw.githubusercontent.com/ernestorivero/Ubigeo-Peru/master/json/ubigeos.json",
    "https://raw.githubusercontent.com/jmcastagnetto/ubigeo-peru-aumentado/main/ubigeo_inei_reniec_202204.json",
    "https://raw.githubusercontent.com/joseluisq/ubigeos-peru/master/data/ubigeo_peru_2016_departamentos.json"
];

foreach ($urls as $url) {
    echo "Trying $url\n";
    $headers = @get_headers($url);
    if($headers && strpos($headers[0], '200')) {
        echo "FOUND: $url\n";
    } else {
        echo "NOT FOUND: $url\n";
    }
}
?>
