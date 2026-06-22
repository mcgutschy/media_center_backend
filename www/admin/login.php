<?php
session_start();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST["username"] === "admin" && $_POST["password"] === "Strato2026!") {
        $_SESSION["auth"] = true;
        header("Location: index.php");
        exit;
    }
    $error = "Falsche Zugangsdaten";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Center - Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
               background: #1a1a2e; color: #eee; display: flex; align-items: center;
               justify-content: center; min-height: 100vh; }
        .login-box { background: #16213e; padding: 2rem; border-radius: 12px;
                     box-shadow: 0 8px 32px rgba(0,0,0,0.3); width: 320px; }
        h1 { text-align: center; margin-bottom: 1.5rem; color: #e94560; font-size: 1.4rem; }
        label { display: block; margin-bottom: 0.3rem; color: #aaa; font-size: 0.85rem; }
        input[type=text], input[type=password] {
            width: 100%; padding: 0.6rem; margin-bottom: 1rem; border: 1px solid #333;
            border-radius: 6px; background: #0f3460; color: #eee; font-size: 1rem; }
        input[type=text]:focus, input[type=password]:focus { border-color: #e94560; outline: none; }
        button { width: 100%; padding: 0.7rem; background: #e94560; color: #fff;
                 border: none; border-radius: 6px; font-size: 1rem; cursor: pointer;
                 font-weight: 600; }
        button:hover { background: #c73e54; }
        .error { color: #e94560; text-align: center; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Media Center Admin</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" autofocus>
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password">
            <button type="submit">Anmelden</button>
        </form>
    </div>
</body>
</html>
