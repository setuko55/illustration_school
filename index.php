<?php
// 環境確認
require_once('general/system/system_validator.php');    // システム要件チェッカー
require_once('general/system/CsrfProtection.php');      // csrf管理オブジェクト

// セッションが開始されていない場合にセッションを開始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('general/Config.php');
require_once('general/functions.php');
require_once('general/db/PDO/PDOObject.php');
require_once('general/db/simpleDBattacker.php');
require_once('general/db/functions/functions.php');

// データベース接続の確立
$pdoConfig = new \PDOConfig();
$pdoObject = new \PDOObject($pdoConfig);
$SDBA = new SimpleDBAttacker($pdoObject);
// 必須スキーマ同期
include('general/sync_required_schema.php');

// .envファイルを検証、存在しなければ生成
if (!validateEnvFile()) {
    $key = generateRandomString();                  // キー生成
    saveEnv(\Config::ENV_NAME_KEY,$key);            // 新規キー保存
    $primeNumDB = new SimpleDBattacker($pdoObject);     // 
    $primeNumDB->setTable(\TableName::PRIME_NUMBERS);   // 
    $primeNumDB->update(['is_active' => 0],['is_active' => 1]); // 古い素数情報を無効化
    checkAndGeneratePrimeNumber($pdoObject,$key);   // 素数生成・登録
    redirectToCurrentPage();    // リダイレクト
}else{
    loadEnv();
    checkAndGeneratePrimeNumber($pdoObject,getenv(\Config::ENV_NAME_KEY));
}

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// ベースURLを除いた部分を取得
$path = str_replace(Config::BASE_DIR.Config::URL_SEGMENT, '', $request);
// ルーティングのためのパスを正規化
$path = trim($path, '/');

if($path === 'cookie-and-session-clear'){
    require_once(getAdminPage('clear_session'));
    exit;
}else if($path === 'admin-login' && isset($_SESSION['is_admin']) && $_SESSION['is_admin']){
    header('Location: '.getFullUrl() .\Config::BASE_DIR .\Config::URL_SEGMENT.'admin');
    exit;
}

// ユーザー登録または既存ユーザーの取得
$userId = getUserId();

if ($userId === null) {
    echo 'ユーザーの登録に失敗しました。';
    exit;
}

if($path == 'ajax'){
    require_once(getAjaxHandler());
    exit;
}

//ヘッダー呼び出し
require_once(getTemplatePage('header'));

// ルーティング処理
switch ($path) {
    case '':
    case 'index':
        // INDEX
        require_once(getTemplatePage('index'));
        break;
    case 'test':
        // テスト用
        require_once(getTemplatePage('test_db_attacker'));
        break;
    case 'top':
        require_once(getTemplatePage('top'));
        break;
    case 'admin-login':
    case 'admin':
        // 管理者ページ
        require_once(getAdminPage('admin'));
        break;
    default:
        // 404ページ
        require_once(getpage('404'));
        break;
}

//フッター呼び出し
require_once(getTemplatePage('footer'));