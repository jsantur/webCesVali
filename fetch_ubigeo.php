<?php
$deps_json = json_decode(file_get_contents(__DIR__ . "/ubigeos/1_ubigeo_departamentos.json"), true);
$provs_json = json_decode(file_get_contents(__DIR__ . "/ubigeos/2_ubigeo_provincias.json"), true);
$dists_json = json_decode(file_get_contents(__DIR__ . "/ubigeos/3_ubigeo_distritos.json"), true);

$deps = $deps_json['ubigeo_departamentos'];
$provs = $provs_json['ubigeo_provincias'];
$dists = $dists_json['ubigeo_distritos'];

function toTitleCase($str) {
    if (!mb_check_encoding($str, 'UTF-8')) {
        $str = utf8_encode($str);
    }
    return mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
}

$result = [];

// Index them
$dep_index = [];
foreach($deps as $d) {
    if (!isset($d['id'])) continue;
    $dep_name = toTitleCase($d['departamento']);
    $dep_index[$d['id']] = $dep_name;
    $result[$dep_name] = [];
}

$prov_index = [];
foreach($provs as $p) {
    if (!isset($p['id']) || !isset($p['departamento_id'])) continue;
    $dep_id = $p['departamento_id'];
    if(!isset($dep_index[$dep_id])) continue;
    $dep_name = $dep_index[$dep_id];
    
    $prov_name = toTitleCase($p['provincia']);
    $prov_index[$p['id']] = [
        'name' => $prov_name,
        'dep_name' => $dep_name
    ];
    if(!isset($result[$dep_name][$prov_name])) {
        $result[$dep_name][$prov_name] = [];
    }
}

foreach($dists as $d) {
    if (!isset($d['provincia_id']) || !isset($d['distrito'])) continue;
    $prov_id = $d['provincia_id'];
    if(isset($prov_index[$prov_id])) {
        $p = $prov_index[$prov_id];
        $dep_name = $p['dep_name'];
        $prov_name = $p['name'];
        $dist_name = toTitleCase($d['distrito']);
        
        if(!in_array($dist_name, $result[$dep_name][$prov_name])) {
            $result[$dep_name][$prov_name][] = $dist_name;
        }
    }
}

// Sort alphabetically
ksort($result);
foreach($result as $dep => &$p_list) {
    ksort($p_list);
    foreach($p_list as $prov => &$d_list) {
        sort($d_list);
    }
}

$encoded = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | (defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0));

if ($encoded === false) {
    die("JSON Encode failed: " . json_last_error_msg());
}

$js = 'const ubigeo = ' . $encoded . ";\n\n";
$js .= "document.addEventListener(\"DOMContentLoaded\", function() {\n";
$js .= "    const depSelect = document.querySelector('select[name=\"departamento\"]');\n";
$js .= "    const provSelect = document.querySelector('select[name=\"provincia\"]');\n";
$js .= "    const distSelect = document.querySelector('select[name=\"distrito\"]');\n";
$js .= "    if (depSelect && provSelect && distSelect) {\n";
$js .= "        depSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu departamento</option>';\n";
$js .= "        for (let dep in ubigeo) {\n";
$js .= "            depSelect.innerHTML += `<option value=\"\${dep}\">\${dep}</option>`;\n";
$js .= "        }\n";
$js .= "        provSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu provincia</option>';\n";
$js .= "        distSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu distrito</option>';\n";
$js .= "        depSelect.addEventListener('change', function() {\n";
$js .= "            const depSeleccionado = this.value;\n";
$js .= "            const provincias = ubigeo[depSeleccionado];\n";
$js .= "            provSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu provincia</option>';\n";
$js .= "            distSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu distrito</option>';\n";
$js .= "            if (provincias) {\n";
$js .= "                for (let prov in provincias) {\n";
$js .= "                    provSelect.innerHTML += `<option value=\"\${prov}\">\${prov}</option>`;\n";
$js .= "                }\n";
$js .= "            }\n";
$js .= "        });\n";
$js .= "        provSelect.addEventListener('change', function() {\n";
$js .= "            const depSeleccionado = depSelect.value;\n";
$js .= "            const provSeleccionada = this.value;\n";
$js .= "            const distritos = ubigeo[depSeleccionado][provSeleccionada];\n";
$js .= "            distSelect.innerHTML = '<option value=\"\" disabled selected>Selecciona tu distrito</option>';\n";
$js .= "            if (distritos) {\n";
$js .= "                distritos.forEach(dist => {\n";
$js .= "                    distSelect.innerHTML += `<option value=\"\${dist}\">\${dist}</option>`;\n";
$js .= "                });\n";
$js .= "            }\n";
$js .= "        });\n";
$js .= "    }\n";
$js .= "});\n";

file_put_contents(__DIR__ . "/js/ubigeo.js", $js);
echo "Successfully built ubigeo.js";
?>
