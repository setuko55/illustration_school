<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    authenticateAdmin($_POST['password'], \Config::ADMIN_PASSWORD);
}

if (!isAdmin()) {
    // パスワード認証ページ
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>管理者ページ</title>
        <link rel="stylesheet" href="<?= getCss('style'); ?>" type="text/css">
    </head>
    <body>
        <h1>管理者ページ</h1>
        <p>必要な情報を入力してください。</p>
        <form method="POST" action="">
            <div id="admin-password">
                <label for="password">パスワード:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">ログイン</button>
        </form>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

$csrf = new CsrfProtection();
// トークンを取得してフォームに埋め込む
$token = $csrf->getToken();

$userDB = new SimpleDBattacker($pdoObject); // ユーザー
$userDB->setTable(\TableName::USERS);

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // まず最初にCSRFトークンのチェックをする
    if (!$csrf->checkToken()) {
        echo "CSRFトークンが無効です。フォームの送信は拒否されました。";
        exit;
    }
    
    $csrf->updateToken();  // 次のリクエスト用に新しいトークンを生成

    // それ以外の処理
    // POSTでなんか保存するならここに記述
}

// フォーム表示のためのトークン生成
$csrfTokenInput = $csrf->generateHiddenInput();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理者用ページ</title>
    <link rel="stylesheet" href="<?= getCss('style'); ?>" type="text/css">
</head>
<body>
    <h1>管理者用ページ</h1>
</body>
</html>