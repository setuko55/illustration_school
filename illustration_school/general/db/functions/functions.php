<?php
/**
 * 暗号化関数
 *
 * @param string $data 暗号化するデータ。
 * @return string 暗号化された文字列。
 */
function encryptData($data)
{
    $key = \Config::ADMIN_CRYPT_KEY;
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    // IVも一緒に保存（IVと暗号文を結合して返す）
    return base64_encode($iv . $encrypted);
}

/**
 * 復号化関数
 *
 * @param string $data 復号化するデータ。
 * @return string 復号化された文字列。
 */
function decryptData($data)
{
    $key = \Config::ADMIN_CRYPT_KEY;
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    
    // IVと暗号文を分ける
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * ユーザーを登録する共通の関数。
 *
 * @param PDOObject $pdoObject PDOインスタンス。
 * @param string $userId ユーザーID。新規ユーザーならランダムに生成する。
 * @return string 生成または取得されたユーザーIDを返す。
 */
function registerUser(PDOObject $pdoObject, $userId = null)
{
    $db = new SimpleDBattacker($pdoObject);
    $db->setTable(\TableName::USERS);

    $cookieName = 'user_id';
    $cookieExpire = time() + (10 * 365 * 24 * 60 * 60); // クッキーの有効期限を10年に設定

    // 既存のユーザーIDが渡されていない場合、新しいユーザーIDを生成
    if ($userId === null) {
        $userId = bin2hex(random_bytes(16)); // ランダムなユーザーIDを生成
    }

    // ユーザーIDを暗号化してクッキーに保存
    $encryptedUserId = encryptData($userId);

    // クッキーに暗号化されたユーザーIDを設定
    setcookie($cookieName, $encryptedUserId, $cookieExpire, "/", "", true, true); // Secure, HttpOnly 設定

    session_regenerate_id(true); // セッションIDを再生成してセッション固定攻撃を防ぐ

    // 新しいユーザーをデータベースに挿入
    $newUser = [
        'user_id' => $userId, // データベースには暗号化されていないIDを保存
        'session_id' => session_id(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'user_ip' => $_SERVER['REMOTE_ADDR'],
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert($newUser);

    $_SESSION['user_registered'] = true; // セッションに登録フラグを設定

    return $userId; // ユーザーIDを返す
}

/**
 * 現在のユーザーの user_id を取得する関数。
 * 
 * @return string ユーザーIDを返す。
 */
function getUserId()
{
    $pdoConfig = new \PDOConfig();
    $pdoObject = new \PDOObject($pdoConfig);
    $db = new SimpleDBattacker($pdoObject);
    $db->setTable(\TableName::USERS);

    $cookieName = 'user_id';

    // クッキーが存在している場合
    if (isset($_COOKIE[$cookieName])) {
        $encryptedUserId = $_COOKIE[$cookieName];
        
        // クッキー内の暗号化されたユーザーIDを復号化
        $userId = decryptData($encryptedUserId);
        
        // データベースからユーザーIDを確認
        $userData = $db->select(['user_id' => $userId]);

        if ($userData) {
            // データベースに存在していれば、そのまま返す
            return $userData[0]['user_id'];
        } else {
            // クッキーはあるが、DBにユーザーが存在しない場合、新しいユーザーを作成
            return registerUser($pdoObject);
        }
    } else {
        // クッキーが存在しない場合、新しいユーザーを作成
        return registerUser($pdoObject);
    }
}
