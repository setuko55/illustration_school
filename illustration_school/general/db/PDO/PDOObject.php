<?php
require_once('PDOConfig.php');

/**
 * データベースへの接続を管理するクラス。
 * このクラスはPDOを利用して、データベース接続と操作を行う。
 */
class PDOObject
{
    /**
     * @var PDO データベース接続オブジェクト
     */
    protected $pdo;

    /**
     * 許可されたPDOメソッドのリスト
     *
     * @var string[]
     */
    protected static $allowed_methods = [
        'beginTransaction',
        'commit',
        'rollBack',
        'prepare',
        'query',
        'exec',
        'lastInsertId'
    ];

    /**
     * PDOObject の新しいインスタンスを作成します。
     * インスタンス時にPDOオブジェクトを生成します。
     * 
     * @param PDOconfig $config データベース接続情報を持つクラスのインスタンス
     * @throws RuntimeException データベース接続エラー時にスローされる
     */
    public function __construct(PDOconfig $config)
    {
        $error_messages = PDOconfig::getErrorMessages()[PDOconfig::$language];

        try {
            $this->pdo = new PDO(
                "mysql:dbname={$config->dbname};host={$config->host};charset={$config->charset}",
                $config->user,
                $config->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException($error_messages['connection_error'] . $e->getMessage());
        }
    }

    /**
     * デストラクタ。PDOオブジェクトをnullに設定します。
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * データベース接続オブジェクトを取得します。
     *
     * @return PDO データベース接続オブジェクト
     */
    public function get(): PDO
    {
        return $this->pdo;
    }

    /**
     * テーブルが存在するかを確認します。
     *
     * @param string $tableName テーブル名
     * @return bool テーブルが存在する場合はtrue、存在しない場合はfalse
     */
    public function tableExists(string $tableName): bool
    {
        $this->validateName($tableName);
        try {
            $stmt = $this->pdo->prepare("SELECT 1 FROM `$tableName` LIMIT 1");
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * テーブルのカラムを取得します。
     *
     * @param string $tableName テーブル名
     * @return array カラム名の配列
     * @throws RuntimeException カラム取得エラー時にスローされる
     */
    public function getTableColumns(string $tableName): array
    {
        $this->validateName($tableName);
        $error_messages = PDOconfig::getErrorMessages()[PDOconfig::$language];

        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$tableName`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns ?: [];
        } catch (PDOException $e) {
            throw new RuntimeException($error_messages['column_fetch_error'] . $e->getMessage());
        }
    }

    /**
     * 指定されたカラムでテーブルを作成します。
     *
     * @param string $tableName テーブル名
     * @param array $columns カラムの定義の配列
     * @return void
     * @throws RuntimeException テーブル作成エラー時にスローされる
     */
    public function createTable(string $tableName, array $columns)
    {
        $this->validateName($tableName);
        $error_messages = PDOconfig::getErrorMessages()[PDOconfig::$language];

        foreach ($columns as &$col) {
            $this->validateName($col['name']);
        }

        $columnsSql = implode(", ", array_map(function ($col) {
            return "{$col['name']} {$col['type']}" . (isset($col['options']) ? " {$col['options']}" : "");
        }, $columns));

        try {
            $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($columnsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new RuntimeException($error_messages['table_creation_error'] . $e->getMessage());
        }
    }

    /**
     * テーブルが存在し、必要なカラムが揃っているか確認します。
     * 不足している場合はエラーを投げ、存在しない場合はテーブルを作成します。
     *
     * @param string $tableName テーブル名
     * @param array $requiredColumns 必要なカラムの定義の配列
     * @return void
     * @throws Exception 必要なカラムが存在しない場合
     */
    public function ensureTable(string $tableName, array $requiredColumns)
    {
        if ($this->tableExists($tableName)) {
            $existingColumns = $this->getTableColumns($tableName);
            foreach ($requiredColumns as $col) {
                if (!in_array($col['name'], $existingColumns)) {
                    throw new Exception(sprintf(PDOconfig::getErrorMessages()[PDOconfig::$language]['missing_column_error'], $tableName, $col['name']));
                }
            }
        } else {
            $this->createTable($tableName, $requiredColumns);
        }
    }

    /**
     * トランザクションを開始します。
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->delegate('beginTransaction');
    }

    /**
     * トランザクションをコミットします。
     *
     * @return void
     */
    public function commit()
    {
        $this->delegate('commit');
    }

    /**
     * トランザクションをロールバックします。
     *
     * @return void
     */
    public function rollBack()
    {
        $this->delegate('rollBack');
    }

    /**
     * SQLステートメントを準備します。
     *
     * @param string $sql 実行するSQL文
     * @return PDOStatement 準備されたステートメントオブジェクト
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->delegate('prepare', [$sql]);
    }

    /**
     * 最後に挿入されたレコードのIDを取得します。
     *
     * @return string 最後に挿入されたレコードのID
     */
    public function lastInsertId(): string
    {
        return $this->delegate('lastInsertId');
    }

    /**
     * PDOメソッドをデリゲート処理します。
     *
     * @param string $method 呼び出すPDOメソッドの名前
     * @param array $args メソッドに渡す引数の配列
     * @return mixed PDOメソッドの結果
     * @throws BadMethodCallException 存在しないメソッドが呼び出された場合
     */
    protected function delegate(string $method, array $args = [])
    {
        $error_messages = PDOconfig::getErrorMessages()[PDOconfig::$language];

        if (!in_array($method, self::$allowed_methods, true)) {
            throw new BadMethodCallException(sprintf($error_messages['method_not_allowed'], $method));
        }

        return $this->pdo->$method(...$args);
    }

    /**
     * テーブル名やカラム名のバリデーションを行います。
     *
     * @param string $name テーブル名またはカラム名
     * @return void
     * @throws InvalidArgumentException 不正な名前が指定された場合
     */
    private function validateName(string $name): void
    {
        $error_messages = PDOconfig::getErrorMessages()[PDOconfig::$language];

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new InvalidArgumentException($error_messages['invalid_name_error'] . $name);
        }
    }
}
