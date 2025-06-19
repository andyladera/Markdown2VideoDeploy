<?php
// --- Views/auth/registro.php ---
$base_url = $base_url ?? '';
$pageTitle = $pageTitle ?? 'Registro de Usuario';
// La variable $csrf_token aquí es el valor de $_SESSION['csrf_token_register'] pasado por AuthController->showRegisterForm()
$csrf_token = $csrf_token ?? ''; 
$error_message = $error_message ?? null;
$form_data = $form_data ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/public/css/registro.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/public/css/header.css">
    <style> /* Estilos para mensajes */
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align:center; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <?php // if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . 'header.php')) { include VIEWS_PATH . 'header.php'; } ?>
    <div class="login-container"> <!-- Puedes renombrar las clases si tienes CSS específico para registro -->
        <div class="login-form">   
            <?php if ($error_message): ?><p class="message error"><?php echo $error_message; ?></p><?php endif; ?>
            <form class="register-form" action="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/auth/register" method="POST">
                <!-- Token CSRF específico para el registro -->
                <input type="hidden" name="csrf_token_register" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="input-group">
                    <h2>Regístrate</h2>
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Elige un nombre de usuario" required 
                           value="<?php echo htmlspecialchars($form_data['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="tu@correo.com" required
                           value="<?php echo htmlspecialchars($form_data['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                </div>
                <div class="input-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirma tu contraseña" required>
                </div>
                <button type="submit">Registrarse Ahora</button>
                <a href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/auth/login">¿Ya tienes una cuenta? Inicia sesión</a>
            </form>
        </div>
    </div>
</body>
</html>