<?php
$adminPassword = \Config::ADMIN_PASSWORD; // ここに管理者パスワードを設定

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    if ($password === $adminPassword) {
        // 全てのセッション変数を解除
        $_SESSION = [];

        // セッションのクッキーも削除
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // 最終的にセッションを破壊
        session_destroy();

        // 全てのクッキーを削除
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', time() - 42000, '/');
        }

        $message = "すべてのセッション情報とクッキー情報がクリアされました。";
    } else {
        $message = "パスワードが違います。";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>セッションとクッキーのクリア</title>
</head>
<body>
    <h1>セッションとクッキーのクリア</h1>
    <form method="POST" action="">
        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">クリア</button>
    </form>
    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</body>
</html>
