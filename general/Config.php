<?php
class Config{
    const ADMIN_PASSWORD = '0000';              // 管理者パスワード
    const ADMIN_CRYPT_KEY = 'SE$d67|WzqkY';     // 
    const BASE_DIR = '/illustration_school/';             // http(s)のあとにつくやつ
    const UPLOADS_FOLDER = '/uploads/';
    const UPLOADS_DIR = __DIR__ . '/../uploads/';
    const UPLOADS_URL = '';
    const URL_SEGMENT = '';                     // BASE_DIRの後にINDEX.php置くところまでについてるやつ
    const JS_DIR = 'general/js';
    const CSS_DIR = 'general/css';
    const SECRET_SHARING_TOTAL_SHARES = 5; // 分割するシェアの数
    const SECRET_SHARING_REQUIRED_SHARES = 3; // 復元に必要なシェアの数
    const PRIME_NUMBER_BIT = 2048; // シャミアの秘密分散で使用する素数のbit長
    const ENV_DIR = __DIR__ . '/.env';
    const ENV_NAME_KEY = 'ENCRYPTION_KEY';
}

/**
 * クラス TableName
 *
 * このクラスは、必須のHEADプレフィックスを使用したテーブル名を定義します。
 * また、HEADやadmin_tokensのような必須フィールドを検証するメソッドも含んでいます。
 */
class TableName {
    /**
     * @const string HEAD すべてのテーブル名に使われるプレフィックス。
     */
    const HEAD = 'ILLUST_SCHOOL_';

    /**
     * @const string ADMIN_CRYPT_KEY 暗号化キー用テーブル
     */
    const ADMIN_CRYPT_KEY = self::HEAD.'ADMIN_CRYPT_KEY';

    /**
     * @const string PRIME_NUMBERS 暗号化用素数
     */
    const PRIME_NUMBERS = self::HEAD.'PRIME_NUMBERS';

    /**
     * @const string ADMIN_TOKENS 管理者トークンのテーブル名。
     */
    const ADMIN_TOKENS = self::HEAD.'ADMIN_TOKENS';

    /**
     * @const string USERS ユーザーテーブルの名前。
     */
    const USERS = self::HEAD.'USERS';

    /**
     * @const string test テストテーブルの名前。
     */
    const TEST = self::HEAD.'test_test_test';

    /**
     * 必須フィールド（HEADやadmin_tokens）が設定されているかを検証します。
     *
     * @throws Exception 必須フィールドが欠けている場合に例外をスローします。
     * @return void
     */
    public static function validateRequiredFields() {
        // 必須フィールド
        $requiredFields = [
            'HEAD' => self::HEAD,
            'PRIME_NUMBERS' => self::PRIME_NUMBERS,
            'ADMIN_CRYPT_KEY' => self::ADMIN_CRYPT_KEY,
            'ADMIN_TOKENS' => self::ADMIN_TOKENS,
        ];

        foreach ($requiredFields as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                throw new Exception("必須フィールドが欠けています: " . $fieldName);
            }
        }
    }
}