<?php

use PSpell\Config;

/**
 * 現在のページにリダイレクトします。
 *
 * 現在のリクエストURIを使用して、ユーザーを同じページにリダイレクトします。
 * この関数は実行後にスクリプトを終了します。
 *
 * @return void
 */
function redirectToCurrentPage()
{
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

/**
 * シンプルな.envファイルの読み込み関数。
 * .envファイルの内容を環境変数として設定する。
 *
 * @param string $filePath .envファイルのパス
 * @return void
 */
function loadEnv($filePath = \Config::ENV_DIR) {
    if (!file_exists($filePath)) {
        throw new Exception(".envファイルが見つかりません: " . $filePath);
    }

    // ファイルの各行を読み込む
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // コメント行はスキップ
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE 形式の行を解析
        list($name, $value) = explode('=', $line, 2);
        
        // 環境変数に設定
        $name = trim($name);
        $value = trim($value);

        // ダブルクオートやシングルクオートを取り除く
        $value = trim($value, '"\'');
        
        // 環境変数にセット
        putenv("$name=$value");
    }
}

/**
 * .envファイルにキーと値を保存する関数。
 *
 * @param string $key 環境変数のキー
 * @param string $value 環境変数の値
 * @param string $filePath .envファイルのパス（省略時はアプリのルートに'.env'を想定）
 * @return void
 */
function saveEnv($key, $value, $filePath = \Config::ENV_DIR) {
    // .envファイルが存在しない場合、作成する
    if (!file_exists($filePath)) {
        file_put_contents($filePath, "");
    }

    // 既存の内容を取得
    $envContent = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // 新しい内容を格納する配列
    $newContent = [];
    $keyExists = false;

    // 既存のキーがあれば、それを更新
    foreach ($envContent as $line) {
        if (strpos(trim($line), "$key=") === 0) {
            $newContent[] = "$key=\"$value\"";  // キーが既にある場合、値を更新
            $keyExists = true;
        } else {
            $newContent[] = $line;
        }
    }

    // キーが存在しない場合、新しいエントリを追加
    if (!$keyExists) {
        $newContent[] = "$key=\"$value\"";
    }

    // 新しい内容をファイルに書き戻す
    file_put_contents($filePath, implode(PHP_EOL, $newContent) . PHP_EOL);
}

/**
 * .envファイルが存在し、内容が正しく記述されているか確認する関数
 *
 * @param string $filePath 確認する.envファイルのパス
 * @return bool 正常であれば true、問題があれば false を返す
 */
function validateEnvFile($filePath = \Config::ENV_DIR) {
    // ファイルが存在するか確認
    if (!file_exists($filePath)) {
        echo $filePath."に.envファイルが存在しません。\n";
        return false;
    }

    // ファイルを1行ずつ読み込む
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // 各行が "KEY=VALUE" の形式になっているか確認
    foreach ($lines as $line) {
        // コメント行や空行はスキップ
        if (strpos(trim($line), '#') === 0 || trim($line) === '') {
            continue;
        }

        // "KEY=VALUE" の形式を確認
        if (strpos($line, '=') === false) {
            echo "エラー: 無効な行が見つかりました: $line\n";
            return false;
        }

        // 左側がキー、右側が値であることを確認
        list($key, $value) = explode('=', $line, 2);
        if (empty(trim($key)) || empty(trim($value))) {
            echo "エラー: キーまたは値が不正です: $line\n";
            return false;
        }
    }
    return true;
}

/**
 * 管理者認証を行い、成功した場合は指定された管理ページにリダイレクトします。
 *
 * @param string $inputPassword 入力されたパスワード。
 * @param string $adminPassword 管理者パスワード。
 * @param string|null $redirectUrl 認証成功時のリダイレクト先URL。デフォルトは現在のURL。
 * @return void 認証が成功した場合はリダイレクト、失敗した場合はエラーメッセージを設定します。
 */
function authenticateAdmin($inputPassword, $adminPassword, $redirectUrl = null)
{
    // 入力されたパスワードが正しいか確認
    if ($inputPassword === $adminPassword) {
        // 管理者セッション登録
        $_SESSION['is_admin'] = true;

        // リダイレクト先URLが指定されていない場合は、デフォルトで現在のURLを使用
        if (is_null($redirectUrl)) {
            $redirectUrl = getFullUrl() . \Config::BASE_DIR . \Config::URL_SEGMENT . 'admin';
        }
        header('Location: ' . $redirectUrl, true, 301);
        exit;
    } else {
        // エラーメッセージを設定
        global $error; // グローバル変数としてエラーメッセージを扱う
        $error = "パスワードが違います";
    }
}

/**
 * 管理者かどうかを確認する関数。
 * authenticateAdmin 関数の認証ロジックに準拠。
 *
 * @return bool 管理者であれば true、それ以外は false。
 */
function isAdmin()
{
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        return false;
    }
    return true;
}

/**
 * 指定された文字数のランダム文字列を生成する関数
 *
 * @param int $length 生成する文字列の長さ。省略時は8文字。
 * @param string $mode 生成する文字列のタイプ。選択肢は 'numeric' (数字のみ), 'uppercase' (大文字英語のみ), 'lowercase' (小文字英語のみ), 'mixed' (大小英語混合), 'alphanumeric' (英数字混合), 'symbol' (記号混合)。
 * @param string $customCharset カスタム文字セット。指定された場合、この文字セットからランダムに文字を選びます。省略時はモードに基づく文字セットを使用。
 * @return string 指定されたモードに基づくランダムな文字列。
 * @throws InvalidArgumentException 不正なモードや文字セットが指定された場合にスローされます。
 */
function generateRandomString(int $length = 8, string $mode = 'mixed', string $customCharset = ''): string {
    // 文字数バリデーション
    if ($length <= 0) {
        throw new InvalidArgumentException('Length must be greater than zero.');
    }

    // 基本の文字セットを定義
    $charsets = [
        'numeric' => '0123456789',
        'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        'mixed' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
        'alphanumeric' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        'symbol' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?'
    ];

    // カスタム文字セットが指定された場合
    if (!empty($customCharset)) {
        $characters = $customCharset;
    } elseif (array_key_exists($mode, $charsets)) {
        $characters = $charsets[$mode];
    } else {
        throw new InvalidArgumentException('Invalid mode specified.');
    }

    $charactersLength = strlen($characters);
    if ($charactersLength === 0) {
        throw new InvalidArgumentException('Character set must not be empty.');
    }

    // ランダム文字列を生成
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * データベースに有効な素数が存在しない場合、新しく生成して保存する関数。
 *
 * @param PDOObject $pdoObject PDOObject
 * @return string 生成または取得された有効な素数
 */
function checkAndGeneratePrimeNumber($pdoObject,$key = null) {
    // 環境変数から暗号化鍵を取得
    $encryptionKey = $key??getenv('ENCRYPTION_KEY');
    if (!$encryptionKey) {
        throw new Exception('暗号化キーが設定されていません');
    }

    // PRIME_NUMBERSテーブルを操作するためのDBインスタンスを作成
    $primeDB = new SimpleDBattacker($pdoObject);
    $primeDB->setTable(\TableName::PRIME_NUMBERS);

    // すでに有効な素数が存在するか確認する
    $activePrime = $primeDB->select(['is_active' => 1]);

    // 有効な素数がない場合、生成して保存する
    if (count($activePrime) === 0) {
        // 新しい素数を生成
        $newPrime = generateSecurePrimeNumber(\Config::PRIME_NUMBER_BIT);

        // 暗号化処理
        $encryptedPrime = encryptPrimeNumber($newPrime, $encryptionKey);

        // 現在の日時を取得
        $createdAt = date('Y-m-d H:i:s');

        // 生成した素数を暗号化してデータベースに保存
        $primeDB->insert([
            'prime_number' => $encryptedPrime,
            'created_at' => $createdAt,
            'is_active' => 1
        ]);

        // 元の素数を返す
        return $newPrime;
    }

    // すでに有効な素数がある場合、その素数を復号して返す
    $decryptedPrime = decryptPrimeNumber($activePrime[0]['prime_number'], $encryptionKey);
    return $decryptedPrime;
}

/**
 * 素数を暗号化する関数
 *
 * @param string $primeNumber 暗号化する素数
 * @param string $key 暗号化キー
 * @return string 暗号化された素数
 */
function encryptPrimeNumber($primeNumber, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($primeNumber, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * 素数を復号化する関数
 *
 * @param string $encryptedPrime 暗号化された素数
 * @param string $key 暗号化キー
 * @return string 復号化された素数
 */
function decryptPrimeNumber($encryptedPrime, $key) {
    $data = base64_decode($encryptedPrime);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

/**
 * GMPライブラリを使って指定されたビット長の素数を生成する関数
 *
 * @param int $bitLength 生成する素数のビット長
 * @return string 生成された素数（文字列形式）
 */
function generateSecurePrimeNumber(int $bitLength) {
        // GMPがロードされているか確認
        if (!extension_loaded('gmp')) {
            // GMPが使えない場合はエラーメッセージを出力してfalseを返す
            trigger_error("GMP拡張が読み込まれていません！GMPをインストールまたは有効にしてください。", E_USER_WARNING);
            return false;
        }
    
    do {
        // CSPRNGを使ってランダムなバイトを生成し、それをGMPの数値に変換
        $randomBytes = random_bytes($bitLength / 8); // 指定ビット長をバイトに変換
        $randomNumber = gmp_import($randomBytes);    // バイトデータをGMPオブジェクトに変換

        // 上位ビットを1にセットして指定ビット長を満たす
        gmp_setbit($randomNumber, $bitLength - 1);

        // 奇数にする
        $randomNumber = gmp_or($randomNumber, 1);

    } while (gmp_prob_prime($randomNumber) == 0); // 確率的に素数かどうか確認

    return gmp_strval($randomNumber); // 素数を文字列として返す
}

/**
 * 分散キーの存在を確認し、なければ生成・保存する関数
 * 
 * @param PDOObject $pdoObject PDOObject
 * @param string $userId ユーザーID
 */
function checkAndGenerateCryptKey($pdoObject, $userId) {
    // `ADMIN_CRYPT_KEY`テーブルを操作するためのDBインスタンスを作成
    $cryptKeyDB = new SimpleDBattacker($pdoObject);
    $cryptKeyDB->setTable(\TableName::ADMIN_CRYPT_KEY);

    // すでに分散キーが存在するかを確認するためのクエリを実行
    $existingKeys = $cryptKeyDB->select(['user_id' => $userId]);

    // 結果が存在するかチェック
    if (count($existingKeys) === 0) {
        // 新しい分散キーを生成する (ランダムな文字列を使用)
        $newCryptKey = bin2hex(random_bytes(32)); // 32バイトのランダムなキー

        // 現在の日時を取得
        $createdAt = date('Y-m-d H:i:s');

        // IPアドレスとユーザーエージェントを取得
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // 分散キーをDBに保存する
        $cryptKeyDB->insert([
            'user_id' => $userId,
            'crypt_key' => $newCryptKey,
            'created_at' => $createdAt,
            'last_used_at' => null, // まだ使われていないためNULL
            'IP' => $ipAddress,
            'user_agent' => $userAgent,
            'is_active' => 1
        ]);

        // 保存したキーを返す
        return $newCryptKey;
    }

    // 既存の分散キーが存在する場合、そのキーを返す
    return $existingKeys[0]['crypt_key'];
}

/**
 * ユーザーID、IPアドレス、ユーザーエージェントを基に管理者用トークンを生成する関数
 *
 * @param int $userId ユーザーID
 * @return string 生成されたトークン（SHA256ハッシュ化済み）
 */
function generateAdminToken($userId)
{
    // IPアドレスとユーザーエージェントを取得
    $userIp = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // IPアドレスとユーザーエージェントを使って一意なトークンを生成
    $tokenData = $userId . $userIp . $userAgent . bin2hex(random_bytes(16)); // ランダムな要素を追加

    return $tokenData;
}

/**
 * 生成したトークンをデータベースに保存する関数
 *
 * @param int $userId ユーザーID
 * @param string $expiry トークンの有効期限 (Y-m-d H:i:s形式)
 * @return string|null 生成されたトークン（成功時）、もしくはnull（失敗時）
 */
function saveAdminToken($userId, $expiry)
{
    try {
        // データベース接続の確立
        $pdoConfig = new PDOConfig();
        $pdoObject = new PDOObject($pdoConfig);
        $attacker = new SimpleDBAttacker($pdoObject);
        $attacker->setTable(\tableName::ADMIN_TOKENS);

        // トークンを生成
        $token = generateAdminToken($userId);

        // トークンをハッシュ化して保存
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        // トークンをDBに挿入
        $attacker->insert([
            'user_id' => $userId,
            'token' => $hashedToken,
            'expiry' => $expiry,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // トークンを返す
        return $token;

    } catch (Exception $e) {
        echo "トークン保存エラー: " . $e->getMessage() . "<br>";
        return null;
    }
}

/**
 * CSVファイルとしてデータを出力する
 *
 * @param string $filename 出力するCSVファイルの名前
 * @param array $headers CSVのヘッダー行（文字列の配列）
 * @param array $data CSVに出力するデータ（2次元配列）
 * 
 * @throws Exception ヘッダーやデータが無効な場合、または出力に失敗した場合に例外を投げる
 * 
 * @return void スクリプト終了後、CSVを出力し終了
 */
function exportToCsv($filename, $headers, $data) {
    // ヘッダーが配列かチェック
    if (!is_array($headers) || empty($headers)) {
        throw new Exception('無効なヘッダーです。配列で渡してください。');
    }

    // データが配列かチェック
    if (!is_array($data) || empty($data)) {
        throw new Exception('無効なデータです。配列で渡してください。');
    }

    // 出力バッファが開けるかチェック
    if (!$output = fopen('php://output', 'w')) {
        throw new Exception('出力ストリームのオープンに失敗しました。');
    }

    // HTTPヘッダーをセット
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    // BOMを追加して文字化け防止（UTF-8の場合）
    if (fwrite($output, "\xEF\xBB\xBF") === false) {
        throw new Exception('BOMの書き込みに失敗しました。');
    }

    // CSVのヘッダー行を追加
    if (fputcsv($output, $headers) === false) {
        throw new Exception('CSVヘッダーの書き込みに失敗しました。');
    }

    // 各データ行をCSVに出力
    foreach ($data as $row) {
        if (fputcsv($output, $row) === false) {
            throw new Exception('データ行の書き込みに失敗しました。');
        }
    }

    fclose($output);
    exit; // ダウンロードが完了したらスクリプトを終了
}

/**
 * データベースに保存された管理者トークンを検証する関数
 *
 * @param int $userId ユーザーID
 * @param string $token 検証するトークン
 * @return bool トークンが有効な場合はtrue、無効な場合はfalse
 */
function verifyAdminToken($userId, $token)
{
    try {
        // データベース接続の確立
        $pdoConfig = new PDOConfig();
        $pdoObject = new PDOObject($pdoConfig);
        $attacker = new SimpleDBAttacker($pdoObject);
        $attacker->setTable(\tableName::ADMIN_TOKENS);

        // ユーザーIDに基づいて有効なトークンを取得
        $record = $attacker->get([
            'user_id' => $userId,
            'expiry' => ['>=', date('Y-m-d H:i:s')] // 有効期限内のトークン
        ]);

        // トークンが存在しない場合
        if (!$record) {
            echo "トークンが見つかりません<br>";
            return false;
        }

        // IPアドレスとユーザーエージェント、user_id を組み合わせたトークンを再生成
        $userIp = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $generatedToken = hash('sha256', $userId . $userIp . $userAgent . $token);

        // トークンが一致するか確認
        if (password_verify($generatedToken, $record['token'])) {
            echo "トークン認証成功<br>";
            return true;
        } else {
            echo "トークン認証失敗<br>";
            return false;
        }
    } catch (Exception $e) {
        echo "トークン確認エラー: " . $e->getMessage() . "<br>";
        return false;
    }
}

/**
 * 指定された文字列をエスケープして返却します。指定された文字列がnullまたは未定義の場合は、代わりのサブストリングを使用します。
 * 
 * 使用例）safeCher($var??null,'isnull')　
 * @を使用してエラーをエスケープしても良いです 
 * 例） @safeCher($var,'isnull')
 *
 * @param string|null $string エスケープする対象の文字列。
 * @param string|null $sub_string $stringがnullまたは未定義の場合に使用する代替の文字列。指定されなかった場合は空文字列を使用します。
 * @return string エスケープされた文字列、または指定された文字列がnullの場合は代替の文字列、もしくは空文字列。
 */
function safeCher(string|null $string, string|null $sub_string = ''): string
{
    return htmlspecialchars(($string ?? $sub_string) ?? '');
}

/**
 * セッションとクッキーをクリアします。
 *
 * @return void
 */
function clearSessionAndCookies()
{
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
}

/**
 * 指定されたURLからホスト部分（スキームとホスト）を取得して返します。
 *
 * @param string $url ホスト部分を取得したいURL。
 * @return string ホスト部分（スキームとホスト）を含むURLの文字列。
 */
function getHost($url)
{
    // URLを解析してホスト部分を取得
    $parsed_url = parse_url($url);
    // ホスト部分を返す
    return $parsed_url['scheme'] . '://' . $parsed_url['host'];
}

/**
 * プロトコル（http または https）とホストを含む完全なURLを取得します。
 *
 * @return string 完全なURL。
 */
function getFullUrl() {
    $protocol = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
    return $protocol . $_SERVER['HTTP_HOST'];
}

/**
 * プロトコル、ホスト、および指定されたパスを含む完全なURLを取得します。
 *
 * @param string $path 任意のページのパス（省略可、デフォルトは'index'）
 * @return string 完全なURL。
 */
function buildFullUrlWithPath($path = 'index') {
    // プロトコルとホストを取得
    $protocol = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
    $host = $_SERVER['HTTP_HOST'];

    // ベースディレクトリとURLセグメントを組み合わせてフルURLを生成
    $fullUrl = rtrim($protocol . $host . \Config::BASE_DIR .\Config::URL_SEGMENT, '/') . trim(\Config::URL_SEGMENT, '/') . '/' . ltrim($path, '/') . '/';

    return $fullUrl;
}

/**
 * JavaScriptファイルのパスを生成して返します。
 *
 * 指定されたJavaScriptファイル名に基づいて、完全なURLパスを生成します。
 * 拡張子が含まれていない場合は、自動的に ".js" を追加します。
 * ホストURLを指定することもできます。
 *
 * @param string $js JavaScriptファイル名。nullの場合は空文字列に設定されます。
 * @param string|false $hosturl 使用するホストURL。デフォルトはfalseで、現在のサーバーのホストが使用されます。
 * @return string 完全なJavaScriptファイルのパス。
 */
function getjs($js, $hosturl = false)
{
    // JSファイルのパスを返す関数
    // $jsがnullの場合は空文字にする
    $js = $js ?? '';

    // $jsに'.js'拡張子がついていなければ追加
    if (pathinfo($js, PATHINFO_EXTENSION) !== 'js') {
        $js .= '.js';
    }

    // ホスト部分を設定
    if ($hosturl === false) {
        // デフォルトホストを設定
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $scheme . '://' . $_SERVER['HTTP_HOST'];
        $host.= \Config::BASE_DIR.\Config::URL_SEGMENT;
    } elseif (is_string($hosturl)) {
        // 渡されたホストURLを使用
        $host = getHost($hosturl);
    } else {
        // ホストURLなし
        $host = '';
    }

    // 完全なパスを返す
    return $host . \Config::JS_DIR . '/' . $js;
}


/**
 * ページ名に基づいて、完全なページファイル名を返します。
 *
 * 指定されたページ名に ".php" 拡張子を追加して返します。
 * ページ名が null または空の場合は、デフォルトのページ名として空文字列を使用します。
 *
 * @param string $pagename ページ名。nullまたは空の場合は空文字列が設定されます。
 * @return string 完全なページファイル名（例: "example.php"）。
 */
function getpage($pagename)
{
    // ページ名がnullまたは空の場合はデフォルトページに設定
    $pagename = $pagename ?? '';

    // URLの末尾に'.php'を追加
    if (pathinfo($pagename, PATHINFO_EXTENSION) !== 'php') {
        $pagename .= '.php';
    }

    return $pagename;
}


/**
 * テンプレートページの完全なパスを生成して返します。
 *
 * 指定されたページ名に ".php" 拡張子を追加し、テンプレートディレクトリのパスと結合して返します。
 * ページ名が null または空の場合は、空文字列が設定されます。
 *
 * @param string $pagename ページ名。nullまたは空の場合は空文字列が設定されます。
 * @return string テンプレートページの完全なパス（例: "pages/template/example.php"）。
 */
function getTemplatePage($pagename)
{
    // ページ名がnullまたは空の場合はデフォルトページに設定
    $pagename = $pagename ?? '';

    // URLの末尾に'.php'を追加
    if (pathinfo($pagename, PATHINFO_EXTENSION) !== 'php') {
        $pagename .= '.php';
    }

    return 'pages/templates/'.$pagename;
}


/**
 * 管理ページの完全なパスを生成して返します。
 *
 * 指定されたページ名に ".php" 拡張子を追加し、管理ページディレクトリのパスと結合して返します。
 * ページ名が null または空の場合は、空文字列が設定されます。
 *
 * @param string $pagename ページ名。nullまたは空の場合は空文字列が設定されます。
 * @return string 管理ページの完全なパス（例: "pages/admin/example.php"）。
 */
function getAdminPage($pagename)
{
    // ページ名がnullまたは空の場合はデフォルトページに設定
    $pagename = $pagename ?? '';

    // URLの末尾に'.php'を追加
    if (pathinfo($pagename, PATHINFO_EXTENSION) !== 'php') {
        $pagename .= '.php';
    }

    return 'pages/admin/'.$pagename;
}

/**
 * ajax_handlerの完全なパスを生成して返します。
 * 
 * @return string ajax_handlerの完全なパス
 */
function getAjaxHandler()
{
    return 'general/ajax/ajax_handler.php';
}

/**
 * ajaxの完全なパスを生成して返します。
 *
 * 指定されたページ名に ".php" 拡張子を追加し、テンプレートディレクトリのパスと結合して返します。
 * ページ名が null または空の場合は、空文字列が設定されます。
 *
 * @param string $pagename ページ名。nullまたは空の場合は空文字列が設定されます。
 * @return string テンプレートページの完全なパス（例: "pages/template/example.php"）。
 */
function getAjax($pagename)
{
    // ページ名がnullまたは空の場合はデフォルトページに設定
    $pagename = $pagename ?? '';

    // URLの末尾に'.php'を追加
    if (pathinfo($pagename, PATHINFO_EXTENSION) !== 'php') {
        $pagename .= '.php';
    }

    return 'general/ajax/'.$pagename;
}

/**
 * CSSファイルのURLを取得し、バージョンクエリパラメータを付加して返します。
 *
 * @param string $filename CSSファイルの名前（.css拡張子は不要）
 * @return string バージョンクエリパラメータ付きのCSSファイルのフルURL
 */
function getCss($filename) {
    $cssFile = \Config::CSS_DIR . '/' . $filename . '.css';
    $fullUrl = getFullUrl() .\Config::BASE_DIR .\Config::URL_SEGMENT . $cssFile;
    if (file_exists($cssFile)) {
        $version = filemtime($cssFile); // ファイルの最終更新日時を取得
        return $fullUrl . '?v=' . $version;
    } else {
        return $fullUrl;
    }
}

/**
 * Uploads an image file to the designated directory and returns its URL.
 *
 * 指定されたディレクトリに画像ファイルをアップロードし、そのURLを返します。
 * アップロードが失敗した場合は false を返します。
 *
 * @param string $fileInputName The name of the input field containing the file. ファイルの入力フィールド名。
 * @return string|false The URL of the uploaded image or false if upload fails. アップロードされた画像のURLまたは失敗した場合はfalse。
 */
function uploadImage($fileInputName)
{
    // アップロードディレクトリの設定
    $uploadDir = \Config::UPLOADS_DIR;

    // ファイルが存在し、エラーがない場合のみ処理を進める
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        // ファイルの保存先のパスを作成
        $uploadFile = $uploadDir . basename($_FILES[$fileInputName]['name']);

        // ファイルの移動に成功した場合
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $uploadFile)) {
            // フルURLを生成して返す
            return getFullUrl() . \Config::BASE_DIR .\Config::URL_SEGMENT . \Config::UPLOADS_FOLDER . basename($_FILES[$fileInputName]['name']);
        } else {
            // ファイル移動に失敗した場合は false を返す
            return false;
        }
    }

    // ファイルが存在しないか、エラーがある場合は false を返す
    return false;
}

/**
 * クライアントのIPアドレスを取得する関数。Cloudflareや他のリバースプロキシを考慮し、
 * IPアドレスを取得します。正しいIPが見つからない場合は、'127.0.0.1'を返します。
 *
 * この関数は、まずCloudflare経由かどうかを確認し、その後REMOTE_ADDRや
 * X-Forwarded-For、X-Real-IPといったプロキシヘッダを確認してIPを取得します。
 * 
 * @return string クライアントのIPアドレス。見つからない場合は'127.0.0.1'を返します。
 */
function getIp(): string
{
    // Cloudflare経由での接続がある場合、'HTTP_CF_CONNECTING_IP'ヘッダを使用してIPを取得
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    // Cloudflare経由でない場合は、'REMOTE_ADDR'から直接IPアドレスを取得
    elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];

        // もし取得したIPがプライベートIPやローカルIPなら、プロキシヘッダを確認
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            // 'HTTP_X_REAL_IP'が存在する場合、そのIPを使用
            if (isset($_SERVER['HTTP_X_REAL_IP'])) {
                $ip = $_SERVER['HTTP_X_REAL_IP'];
            }
            // 'HTTP_X_FORWARDED_FOR'が存在する場合、カンマで区切られた最初のIPを取得
            elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // 複数のIPが存在する場合、カンマで区切られるので最初のIPを使う
                $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($forwarded_ips[0]);
            }
        }
    } 
    // どのIPも見つからない場合、デフォルトでループバックアドレス'127.0.0.1'を設定
    else {
        $ip = '127.0.0.1';
    }

    // もし取得したIPがループバックアドレス（::1、0.0.0.0、127.0.0.1など）なら、'127.0.0.1'に変更
    if (in_array($ip, ['::1', '0.0.0.0', '127.0.0.1'], true)) {
        $ip = '127.0.0.1';
    }

    // 最後に、取得したIPが有効なIPv4またはIPv6アドレスかを確認。無効な場合は'127.0.0.1'を返す
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = '127.0.0.1';
    }

    // 最終的なIPアドレスを返す
    return $ip;
}