<?php
session_start();

// Configuración de contraseña (cámbiala si lo deseas)
$PASSWORD = 'vali123';

if (isset($_POST['login'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Contraseña incorrecta.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_testimonios.php");
    exit;
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Testimonios - VALI</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1b3140;
            text-align: center;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }
        button {
            background-color: #f1b32d;
            color: #1b3140;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
        button:hover {
            background-color: #e5a725;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .logout {
            float: right;
            text-decoration: none;
            color: #d9534f;
            font-weight: bold;
        }
        /* Grid de testimonios */
        .testimonial-list {
            margin-top: 30px;
        }
        .testimonio-item {
            display: flex;
            align-items: center;
            background: #fafafa;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        .testimonio-item img, .testimonio-item .icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            background: #eee;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .testimonio-info {
            flex-grow: 1;
        }
        .testimonio-info h4 {
            margin: 0 0 5px 0;
        }
        .testimonio-info p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .btn-delete {
            background-color: #d9534f;
            color: white;
            padding: 6px 12px;
            font-size: 14px;
        }
        .btn-delete:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (!$isLoggedIn): ?>
        <div class="login-form">
            <h1>Acceso al Panel</h1>
            <?php if (isset($error)): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Ingresar</button>
            </form>
        </div>
    <?php else: ?>
        <a href="?logout=1" class="logout">Cerrar Sesión</a>
        <h1>Administrar Testimonios</h1>
        
        <form id="addForm" enctype="multipart/form-data">
            <h3>Agregar Nuevo Testimonio</h3>
            <input type="text" id="nombre" placeholder="Nombre del cliente (Ej: María G.)" required>
            <textarea id="texto" rows="3" placeholder="Texto del testimonio" required></textarea>
            <label>Estrellas:</label>
            <select id="estrellas">
                <option value="5">5 Estrellas</option>
                <option value="4">4 Estrellas</option>
                <option value="3">3 Estrellas</option>
                <option value="2">2 Estrellas</option>
                <option value="1">1 Estrella</option>
            </select>
            <label>Foto (Opcional):</label>
            <input type="file" id="foto" accept="image/*">
            <button type="submit">Guardar Testimonio</button>
        </form>

        <div class="testimonial-list">
            <h3>Testimonios Actuales</h3>
            <div id="listaTestimonios">Cargando...</div>
        </div>

        <script>
            function loadTestimonios() {
                fetch('api_testimonios.php')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('listaTestimonios');
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = '<p>No hay testimonios registrados.</p>';
                        return;
                    }
                    data.forEach(t => {
                        const item = document.createElement('div');
                        item.className = 'testimonio-item';
                        
                        let imgHtml = '';
                        if (t.foto) {
                            imgHtml = `<img src="${t.foto}" alt="${t.nombre}">`;
                        } else {
                            imgHtml = `<div class="icon">
                                <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#1b3140" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 20c0-4 4-7 8-7s8 3 8 7"></path>
                                </svg>
                            </div>`;
                        }

                        item.innerHTML = `
                            ${imgHtml}
                            <div class="testimonio-info">
                                <h4>${t.nombre} - ${t.estrellas} ★</h4>
                                <p>${t.texto}</p>
                            </div>
                            <button class="btn-delete" onclick="deleteTestimonio(${t.id})">Eliminar</button>
                        `;
                        list.appendChild(item);
                    });
                });
            }

            document.getElementById('addForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('nombre', document.getElementById('nombre').value);
                formData.append('texto', document.getElementById('texto').value);
                formData.append('estrellas', document.getElementById('estrellas').value);
                
                const fotoInput = document.getElementById('foto');
                if (fotoInput.files.length > 0) {
                    formData.append('foto', fotoInput.files[0]);
                }

                fetch('api_testimonios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('addForm').reset();
                        loadTestimonios();
                    } else {
                        alert("Error: " + res.message);
                    }
                })
                .catch(err => alert("Ocurrió un error"));
            });

            function deleteTestimonio(id) {
                if (!confirm("¿Seguro que deseas eliminar este testimonio?")) return;
                
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('api_testimonios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        loadTestimonios();
                    } else {
                        alert("Error al eliminar");
                    }
                });
            }

            // Cargar testimonios al inicio
            loadTestimonios();
        </script>
    <?php endif; ?>
</div>

</body>
</html>
