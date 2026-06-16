<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inicializar respuesta
$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolectar datos básicos
    $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
    $tipoDocumento = isset($_POST['tipo_documento']) ? $_POST['tipo_documento'] : '';
    $numeroDocumento = isset($_POST['numero_documento']) ? trim($_POST['numero_documento']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $celular = isset($_POST['celular']) ? trim($_POST['celular']) : '';

    $menorEdad = isset($_POST['menor_edad']) ? $_POST['menor_edad'] : 'no';
    $departamento = isset($_POST['departamento']) ? $_POST['departamento'] : '';
    $provincia = isset($_POST['provincia']) ? $_POST['provincia'] : '';
    $distrito = isset($_POST['distrito']) ? $_POST['distrito'] : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';

    $bienContratado = isset($_POST['bien_contratado']) ? $_POST['bien_contratado'] : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    $tipoReclamo = isset($_POST['tipo_reclamo']) ? $_POST['tipo_reclamo'] : '';
    $detalle = isset($_POST['detalle']) ? trim($_POST['detalle']) : '';
    $pedido = isset($_POST['pedido']) ? trim($_POST['pedido']) : '';

    date_default_timezone_set('America/Lima');
    $fecha = date('Y-m-d H:i:s');

    // Manejo de la subida de archivo
    $archivoAdjuntoTexto = "Sin archivo adjunto";

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';

        // Crear carpeta si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Nombre seguro para el archivo
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['archivo']['name']));
        $targetPath = $uploadDir . $fileName;

        // Validar tamaño (5MB max)
        if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
            $response['error'] = 'El archivo supera el tamaño máximo de 5MB.';
            echo json_encode($response);
            exit;
        }

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $targetPath)) {
            $archivoAdjuntoTexto = "uploads/" . $fileName;
        } else {
            $archivoAdjuntoTexto = "Error al subir el archivo";
        }
    }

    // Preparar el texto para guardar
    $contenidoReclamo = "=================================================\n";
    $contenidoReclamo .= "FECHA Y HORA: $fecha\n";
    $contenidoReclamo .= "TIPO: " . strtoupper($tipoReclamo) . "\n";
    $contenidoReclamo .= "DATOS PERSONALES:\n";
    $contenidoReclamo .= "- Menor de edad: $menorEdad\n";
    $contenidoReclamo .= "- Nombres: $nombres $apellidos\n";
    $contenidoReclamo .= "- Documento: " . strtoupper($tipoDocumento) . " - $numeroDocumento\n";
    $contenidoReclamo .= "- E-mail: $correo | Cel: $celular\n";
    $contenidoReclamo .= "- Dirección: $direccion, $distrito, $provincia, $departamento\n";
    $contenidoReclamo .= "BIEN CONTRATADO:\n";
    $contenidoReclamo .= "- Bien: $bienContratado\n";
    $contenidoReclamo .= "- Descripción: $descripcion\n";
    $contenidoReclamo .= "DETALLE DEL RECLAMO/QUEJA:\n";
    $contenidoReclamo .= "- Detalle: $detalle\n";
    $contenidoReclamo .= "- Pedido: $pedido\n";
    $contenidoReclamo .= "ARCHIVO ADJUNTO: $archivoAdjuntoTexto\n";
    $contenidoReclamo .= "=================================================\n\n";

    // Guardar en archivo de texto plano
    $archivoTxt = __DIR__ . '/reclamos.txt';

    if (file_put_contents($archivoTxt, $contenidoReclamo, FILE_APPEND | LOCK_EX)) {

        // Preparar contenido HTML para el correo
        $contenidoHtml = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f7fb; padding: 20px; color: #031f24;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #143042; padding: 20px; border-bottom: 4px solid #FAEA26;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='width: 80px; vertical-align: middle;'>
                                <img src='cid:logovali' alt='Logo VALI' style='max-height: 60px; width: auto; display: block;'>
                            </td>
                            <td style='vertical-align: middle; text-align: left; padding-left: 15px;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 24px; text-transform: uppercase;'>Libro de Reclamaciones</h1>
                                <p style='color: #FAEA26; margin: 5px 0 0; font-size: 16px; font-weight: bold;'>NUEVO " . strtoupper($tipoReclamo) . "</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style='padding: 30px;'>
                    <p style='margin-bottom: 20px; font-size: 14px; color: #5f6570;'><strong>Fecha y Hora:</strong> $fecha</p>
                    
                    <h2 style='color: #0b3a42; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 0;'>Datos Personales</h2>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                        <tr><td style='padding: 6px 0; width: 35%; border-bottom: 1px solid #f4f7fb;'><strong>Nombres:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'>$nombres $apellidos</td></tr>
                        <tr><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'><strong>Documento:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'>" . strtoupper($tipoDocumento) . " - $numeroDocumento</td></tr>
                        <tr><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'><strong>Menor de edad:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb; text-transform: capitalize;'>$menorEdad</td></tr>
                        <tr><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'><strong>E-mail:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'>$correo</td></tr>
                        <tr><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'><strong>Celular:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'>$celular</td></tr>
                        <tr><td style='padding: 6px 0;'><strong>Dirección:</strong></td><td style='padding: 6px 0;'>$direccion, $distrito, $provincia, $departamento</td></tr>
                    </table>
                    
                    <h2 style='color: #0b3a42; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;'>Bien Contratado</h2>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                        <tr><td style='padding: 6px 0; width: 35%; border-bottom: 1px solid #f4f7fb;'><strong>Bien:</strong></td><td style='padding: 6px 0; border-bottom: 1px solid #f4f7fb;'>$bienContratado</td></tr>
                        <tr><td style='padding: 6px 0;'><strong>Descripción:</strong></td><td style='padding: 6px 0;'>$descripcion</td></tr>
                    </table>
                    
                    <h2 style='color: #0b3a42; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;'>Detalle del Reclamo/Queja</h2>
                    <div style='background-color: #f4f7fb; padding: 15px; border-radius: 4px; border-left: 4px solid #FDD835; margin: 15px 0; font-size: 14px;'>
                        <strong style='color: #0b3a42;'>Detalle:</strong><br/><br/>
                        " . nl2br(htmlspecialchars($detalle)) . "
                    </div>
                    <div style='background-color: #f4f7fb; padding: 15px; border-radius: 4px; border-left: 4px solid #FDD835; margin: 15px 0; font-size: 14px;'>
                        <strong style='color: #0b3a42;'>Pedido:</strong><br/><br/>
                        " . nl2br(htmlspecialchars($pedido)) . "
                    </div>
                    
                    " . ($archivoAdjuntoTexto !== 'Sin archivo adjunto' ? "
                    <h2 style='color: #0b3a42; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 25px;'>Archivo Adjunto</h2>
                    <p style='margin: 10px 0; font-size: 14px;'><strong>" . htmlspecialchars($archivoAdjuntoTexto) . "</strong></p>
                    " : "") . "
                </div>
                <div style='background-color: #143042; padding: 15px; text-align: center; color: #d1d5db; font-size: 12px;'>
                    Este mensaje fue generado automáticamente desde el sitio web.
                </div>
            </div>
        </div>
        ";

        // --- INICIO DE LÓGICA PARA ENVIAR CORREO CON PHPMAILER ---
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del Servidor SMTP
            // POR FAVOR: Configura aquí los datos de tu cuenta de correo
            $mail->isSMTP();
            $mail->Host = 'mail.asbuprop.com'; // O smtp.tudominio.com
            $mail->SMTPAuth = true;
            $mail->Username = 'asbuprop2610@asbuprop.com'; // PON AQUÍ TU USUARIO SMTP
            $mail->Password = 'asbupropdivers2610';         // PON AQUÍ TU CONTRASEÑA SMTP

            // Configuración de Seguridad (TLS/SSL)
            // Usa ENCRYPTION_SMTPS para puerto 465, o ENCRYPTION_STARTTLS para puerto 587
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->SMTPDebug = 0; // Deshabilitar modo debug para no romper la respuesta JSON
            // Remitente y Destinatarios
            $mail->setFrom('asbuprop2610@asbuprop.com', 'Libro de Reclamaciones'); // Correo que envía
            $mail->addAddress('administracion@cesvali.com'); // Correo principal al que llega
            $mail->addCC('ceo@cesvali.com');                 // Correo en copia
            $mail->addCC('lamentemaestraguti@gmail.com');
            // Para poder darle a "Responder" y que le llegue al cliente
            $mail->addReplyTo($correo);

            // Adjuntar archivo (si el cliente subió uno y no excedió el límite)
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK && isset($targetPath)) {
                $mail->addAttachment($targetPath);
            }

            // Adjuntar Logo para el header
            $logoPath = __DIR__ . '/assets/img/logovali.png';
            if (file_exists($logoPath)) {
                $mail->AddEmbeddedImage($logoPath, 'logovali', 'logovali.png');
            }

            // Contenido del Correo
            $mail->isHTML(true); // Formato HTML
            $mail->Subject = "Nuevo " . strtoupper($tipoReclamo) . " - Libro de Reclamaciones";
            $mail->Body = $contenidoHtml;
            $mail->AltBody = $contenidoReclamo;
            $mail->CharSet = 'UTF-8';

            // Enviar
            $mail->send();
        } catch (Exception $e) {
            // Si hay un error al enviar el correo, no detenemos el script,
            // ya que la información sí se guardó correctamente en el TXT.
            // Para ver errores, podrías descomentar la siguiente línea:
            // error_log("Error enviando correo PHPMailer: {$mail->ErrorInfo}");
        }
        // --- FIN DE LÓGICA PARA ENVIAR CORREO CON PHPMAILER ---

        $response['success'] = true;
    } else {
        $response['error'] = 'No se pudo guardar la información en el servidor.';
    }

} else {
    $response['error'] = 'Método de petición inválido.';
}

echo json_encode($response);
?>