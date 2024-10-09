<?php
//DB情報はこちらに格納してください
class PDOConfig{
    /**
     * データベースホスト名
     * @var string
     */
    public const DB_HOST = 'localhost'; 

    /**
     * データベース名
     * @var string
     */
    public const DB_NAME = 'illustration_school';

    /**
     * データベースユーザー名
     * @var string
     */
    public const DB_USER = 'root';      

    /**
     * データベースパスワード
     * @var string
     */
    public const DB_PASS = '';          

    /**
     * データベースキャラセット
     * @var string
     */
    public const DB_CHARSET = 'utf8mb4';

    /**
     * 言語設定（デフォルトは日本語）
     * @var string
     */
    public static $language = 'ja';

    /**
     * データベースホスト
     * @var string
     */
    public $host;

    /**
     * データベース名
     * @var string
     */
    public $dbname;

    /**
     * データベースユーザー名
     * @var string
     */
    public $user;

    /**
     * データベースパスワード
     * @var string
     */
    public $pass;

    /**
     * データベース文字セット
     * @var string
     */
    public $charset;


    /**
     * コンストラクタでPDO接続情報を設定
     *
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $pass
     * @param string $charset
     */
    public function __construct(
        string $host = self::DB_HOST,
        string $dbname = self::DB_NAME,
        string $user = self::DB_USER,
        string $pass = self::DB_PASS,
        string $charset = self::DB_CHARSET
    ) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->pass = $pass;
        $this->charset = $charset;
    }

    // 多言語対応のエラーメッセージ
    public static function getErrorMessages(): array
    {
        return [
            'ja' => [
                'connection_error' => 'データベース接続エラー: ',
                'column_fetch_error' => 'カラム取得エラー: ',
                'table_creation_error' => 'テーブル作成エラー: ',
                'missing_column_error' => "テーブル '%s' に必要なカラム '%s' が存在しません。",
                'invalid_name_error' => '不正な名前です: ',
                'method_not_allowed' => "メソッド '%s' は許可されていないか、存在しません。"
            ],
            'en' => [
                'connection_error' => 'Database connection error: ',
                'column_fetch_error' => 'Error fetching columns: ',
                'table_creation_error' => 'Table creation error: ',
                'missing_column_error' => "Required column '%s' is missing in table '%s'.",
                'invalid_name_error' => 'Invalid name: ',
                'method_not_allowed' => "Method '%s' is not allowed or does not exist."
            ],
            'zh' => [
                'connection_error' => '数据库连接错误: ',
                'column_fetch_error' => '获取列错误: ',
                'table_creation_error' => '创建表错误: ',
                'missing_column_error' => "表 '%s' 缺少必要的列 '%s'。",
                'invalid_name_error' => '无效的名称: ',
                'method_not_allowed' => "方法 '%s' 不允许或不存在。"
            ],
        ];
    }

    /**
     * 現在の言語設定に基づいたエラーメッセージを取得
     *
     * @param string $key メッセージのキー
     * @return string
     */
    public static function getErrorMessage(string $key): string
    {
        $messages = self::getErrorMessages();
        return $messages[self::$language][$key] ?? 'Unknown error.';
    }
}