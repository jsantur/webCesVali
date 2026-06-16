<?php
header('Content-Type: application/json');

$jsonFile = 'data/testimonios.json';
$uploadsDir = 'uploads/testimonios/';

// Crear el directorio de subidas si no existe
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

// Leer testimonios
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($jsonFile)) {
        echo file_get_contents($jsonFile);
    } else {
        echo json_encode([]);
    }
    exit;
}

// Procesar peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Leer el JSON actual
    $testimonios = [];
    if (file_exists($jsonFile)) {
        $testimonios = json_decode(file_get_contents($jsonFile), true);
        if (!is_array($testimonios)) $testimonios = [];
    }

    if ($action === 'add') {
        $nombre = $_POST['nombre'] ?? '';
        $texto = $_POST['texto'] ?? '';
        $estrellas = intval($_POST['estrellas'] ?? 5);
        $fotoPath = null;

        // Procesar subida de foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileName = basename($_FILES['foto']['name']);
            // Generar nombre único
            $uniqueName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $fileName);
            $destination = $uploadsDir . $uniqueName;
            
            if (move_uploaded_file($tmpName, $destination)) {
                $fotoPath = $destination;
            }
        }

        // Generar un ID simple (el máximo actual + 1)
        $maxId = 0;
        foreach ($testimonios as $t) {
            if ($t['id'] > $maxId) $maxId = $t['id'];
        }

        // Convertir saltos de línea a <br>
        $textoFormat = nl2br(htmlspecialchars($texto));

        $nuevoTestimonio = [
            'id' => $maxId + 1,
            'nombre' => htmlspecialchars($nombre),
            'texto' => $textoFormat,
            'estrellas' => $estrellas,
            'foto' => $fotoPath
        ];

        // Añadir al inicio del array
        array_unshift($testimonios, $nuevoTestimonio);

        // Guardar
        file_put_contents($jsonFile, json_encode($testimonios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode(['success' => true, 'message' => 'Testimonio agregado']);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $foundIndex = -1;
        
        foreach ($testimonios as $index => $t) {
            if ($t['id'] === $id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== -1) {
            // Eliminar foto si existe
            if (!empty($testimonios[$foundIndex]['foto']) && file_exists($testimonios[$foundIndex]['foto'])) {
                unlink($testimonios[$foundIndex]['foto']);
            }
            // Eliminar del array
            array_splice($testimonios, $foundIndex, 1);
            // Guardar
            file_put_contents($jsonFile, json_encode($testimonios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Testimonio no encontrado']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
    exit;
}
?>
