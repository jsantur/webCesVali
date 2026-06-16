<?php
// router.php para el servidor local de desarrollo
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Si se solicita la raíz, cargar el index.html
if ($path === '/') {
    if (file_exists(__DIR__ . '/index.html')) {
        include __DIR__ . '/index.html';
        return true;
    }
    return false;
}

$file = __DIR__ . $path;

// 1. Si existe el archivo tal cual (css, js, imagenes, php directo, etc), lo servimos
if (is_file($file)) {
    return false; // Permite que el servidor incorporado lo maneje
}

// 2. Si la ruta solicitada tiene un .html y queremos forzar limpieza, redirigimos
if (substr($path, -5) === '.html') {
    $cleanUrl = substr($path, 0, -5);
    header("Location: " . $cleanUrl, true, 301);
    exit;
}

// 3. Si existe el archivo agregando .html internamente
if (is_file($file . '.html')) {
    include $file . '.html';
    return true;
}

// 4. Si existe agregando .php
if (is_file($file . '.php')) {
    include $file . '.php';
    return true;
}

// Retornar 404 por defecto
return false;
