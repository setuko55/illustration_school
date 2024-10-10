<?php
require_once('simpleDBattackerConfig.php');
/**
 * クラス SimpleDBAttacker
 * データベーステーブルに対して基本的なCRUD操作を提供する。
 */
class SimpleDBAttacker
{
    /**
     * @var PDOObject PDOインスタンス。
     */
    protected $pdoObject;

    /**
     * @var string データベーステーブルの名前。
     */
    protected $tableName;

    /**
     * コンストラクタ。
     *
     * @param PDOObject $pdoObject PDOインスタンス。
     */
    public function __construct(PDOObject $pdoObject)
    {
        $this->pdoObject = $pdoObject;
        $this->initializeLogTable(); // ログテーブルの初期化
    }

    /**
     * `operation_logs` テーブルの存在を確認し、必要であれば同期する。
     */
    private function initializeLogTable()
    {
        // 必要なカラムの定義
        $requiredColumns = [
            ['name' => 'id', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
            ['name' => 'table_name', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL'],
            ['name' => 'operation_type', 'type' => "ENUM('INSERT', 'UPDATE', 'DELETE')", 'options' => 'NOT NULL'],
            ['name' => 'record_id', 'type' => 'INT', 'options' => 'DEFAULT NULL'],
            ['name' => 'changes', 'type' => 'TEXT', 'options' => 'DEFAULT NULL'],
            ['name' => 'executed_at', 'type' => 'DATETIME', 'options' => 'DEFAULT CURRENT_TIMESTAMP'],
            ['name' => 'user_id', 'type' => 'INT', 'options' => 'DEFAULT NULL'],
            ['name' => 'ip_address', 'type' => 'VARCHAR(45)', 'options' => 'DEFAULT NULL'],
            ['name' => 'user_agent', 'type' => 'TEXT', 'options' => 'DEFAULT NULL']
        ];

        // `syncTableSchema` 関数を使用してテーブルスキーマを同期
        self::syncTableSchema($this->pdoObject, \SDBAConfig::LOG_TABLE, $requiredColumns);
    }
    /**
     * テーブルのスキーマを同期する静的メソッド。
     * 指定されたカラムがテーブルに存在しなければ追加し、不要なカラムは削除する。
     *
     * @param PDOObject $pdoObject データベース接続オブジェクト
     * @param string $tableName テーブル名
     * @param array $requiredColumns 必要なカラムの配列
     * @return void
     */
    public function syncTableSchema(PDOObject $pdoObject, string $tableName, array $requiredColumns): void
    {
        $safeTableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        if (!$pdoObject->tableExists($safeTableName)) {
            $columnsSql = implode(", ", array_map(function ($col) {
                $safeColumnName = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
                return "`$safeColumnName` {$col['type']}" . (isset($col['options']) ? " {$col['options']}" : "");
            }, $requiredColumns));

            $sql = "CREATE TABLE `$safeTableName` ($columnsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            try {
                $pdoObject->get()->exec($sql);
                $this->logOperation('CREATE', $safeTableName, null, ['query' => $sql]);
            } catch (PDOException $e) {
                throw new RuntimeException('テーブル作成エラー: ' . $e->getMessage());
            }
            return;
        }

        $existingColumns = $pdoObject->getTableColumns($safeTableName);

        $requiredColumnNames = array_column($requiredColumns, 'name');

        // 追加する必要があるカラムを見つける
        $columnsToAdd = array_filter($requiredColumns, function ($col) use ($existingColumns) {
            return !in_array($col['name'], $existingColumns);
        });

        // 削除する必要があるカラムを見つける
        $columnsToDelete = array_diff($existingColumns, $requiredColumnNames);

        foreach ($columnsToAdd as $col) {
            $safeColumnName = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
            $sql = "ALTER TABLE `$safeTableName` ADD COLUMN `$safeColumnName` {$col['type']}" . (isset($col['options']) ? " {$col['options']}" : "");
            try {
                $pdoObject->get()->exec($sql);
            } catch (PDOException $e) {
                throw new RuntimeException('カラム追加エラー: ' . $e->getMessage());
            }
        }

        foreach ($columnsToDelete as $col) {
            $safeColumnName = preg_replace('/[^a-zA-Z0-9_]/', '', $col);
            $sql = "ALTER TABLE `$safeTableName` DROP COLUMN `$safeColumnName`";
            try {
                $pdoObject->get()->exec($sql);
            } catch (PDOException $e) {
                throw new RuntimeException('カラム削除エラー: ' . $e->getMessage());
            }
        }
    }


    
    /**
     * 操作ログを記録する。
     *
     * @param string $operation 操作の種類（INSERT, UPDATE, DELETE）。
     * @param string $tableName 操作が行われたテーブルの名前。
     * @param int|null $recordId 操作されたレコードのID。
     * @param array $changes 変更内容を表す配列。
     */
    protected final function logOperation(string $operation, string $tableName, ?int $recordId, array $changes = [])
    {
        // ログを記録する処理をここに記述（例：データベースへの挿入）
        $changesJson = json_encode($changes);
        $userId = $_SESSION['user_id'] ?? null; // セッションからユーザーIDを取得（適宜変更）
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
        $sql = "INSERT INTO " . \SDBAConfig::LOG_TABLE . " (table_name, operation_type, record_id, changes, user_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
    
        // 必要に応じて pdoObject も静的メソッドで管理する必要がある
        $stmt = $this->pdoObject->prepare($sql);
        $stmt->execute([$tableName, $operation, $recordId, $changesJson, $userId, $ipAddress, $userAgent]);
    }

    /**
     * テーブル名を設定する。
     *
     * @param string $tableName データベーステーブルの名前。
     * @return self
     */
    public function setTable(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * テーブルに新しいレコードを挿入する。
     *
     * @param array $data 挿入するデータ。
     */
    public function insert(array $data)
    {
        // operation_logsテーブルへの操作をブロック
        if ($this->tableName === \SDBAConfig::LOG_TABLE) {
            throw new RuntimeException('ログ用テーブルには直接操作できません。');
        }
        
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";
        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_values($data));
            $lastInsertId = $this->pdoObject->lastInsertId();
            $this->logOperation('INSERT', $this->tableName, $lastInsertId, $data);
        } catch (PDOException $e) {
            throw new RuntimeException('挿入エラー: ' . $e->getMessage());
        }
    }

    /**
     * テーブルの既存レコードを更新する。
     *
     * @param array $data 更新するデータ。
     * @param array $conditions 更新する条件。
     */
    public function update(array $data, array $conditions)
    {
        // operation_logsテーブルへの操作をブロック
        if ($this->tableName === \SDBAConfig::LOG_TABLE) {
            throw new RuntimeException('ログ用テーブルには直接操作できません。');
        }

        $setPart = implode(", ", array_map(fn($key) => "$key = ?", array_keys($data)));
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "UPDATE {$this->tableName} SET $setPart $wherePart";
        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($data), array_values($conditions)));
            $this->logOperation('UPDATE', $this->tableName, null, $data);
        } catch (PDOException $e) {
            throw new RuntimeException('更新エラー: ' . $e->getMessage());
        }
    }

    /**
     * テーブルのレコードを削除する。
     *
     * @param array $conditions 削除する条件。
     */
    public function delete(array $conditions)
    {
        // operation_logsテーブルへの操作をブロック
        if ($this->tableName === \SDBAConfig::LOG_TABLE) {
            throw new RuntimeException('ログ用テーブルには直接操作できません。');
        }
        
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "DELETE FROM {$this->tableName} $wherePart";
        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_values($conditions));
            $this->logOperation('DELETE', $this->tableName, null, $conditions);
        } catch (PDOException $e) {
            throw new RuntimeException('削除エラー: ' . $e->getMessage());
        }
    }

    /**
     * テーブルからレコードを選択する。
     *
     * @param array $conditions 選択する条件。
     * @return array 選択されたレコード。
     */
    public function select(array $conditions = []): array
    {
        // WHERE句を作成
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} $wherePart";

        try {
            $stmt = $this->pdoObject->prepare($sql);

            // $conditions配列を値のみでなく、適切にバインドするために整形
            $params = [];
            foreach ($conditions as $key => $value) {
                // 配列かどうかチェックして、演算子と値に分解
                if (is_array($value)) {
                    // 例: ['expiry' => ['>=', '2023-01-01']]
                    $params[] = $value[1]; // ここで実際の値をバインド
                } else {
                    $params[] = $value; // 単純なキー=値の場合
                }
            }

            // SQLを実行
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('選択エラー: ' . $e->getMessage());
        }
    }
    
    /**
     * テーブルから1つのレコードを取得する。
     *
     * @param array $conditions 選択する条件。
     * @return array|false 取得されたレコード、見つからない場合はfalse。
     */
    public function get(array $conditions)
    {
        $results = $this->select($conditions);
        return $results ? $results[0] : false;
    }

    /**
     * テーブルから全てのレコードを取得する。
     *
     * @return array 取得されたレコード。
     */
    public function getAll(): array
    {
        return $this->select(); // selectメソッドを利用してすべてのレコードを取得
    }

    /**
     * 設定されているテーブル名を取得する。
     *
     * @return string テーブル名。
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * テーブルから指定した条件に一致するレコードの数を取得する。
     *
     * @param array $conditions カウントする条件。
     * @return int 条件に一致するレコードの数。
     */
    public function count(array $conditions = []): int
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} $wherePart";
        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_values($conditions));
            $result = $stmt->fetch();
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            throw new RuntimeException('カウントエラー: ' . $e->getMessage());
        }
    }

    /**
     * 条件に基づいてWHERE句を構築するメソッド。
     *
     * @param array $conditions 条件の配列。
     * @return string WHERE句。
     */
    private function buildWhereClause(array $conditions): string
    {
        $whereParts = [];
        foreach ($conditions as $key => $value) {
            // 演算子が指定されている場合と、指定されていない場合を処理
            if (is_array($value)) {
                $whereParts[] = "$key {$value[0]} ?"; // 例: "expiry >= ?"
            } else {
                $whereParts[] = "$key = ?"; // 例: "user_id = ?"
            }
        }
    
        return !empty($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';
    }
    /**
     * プライマリキーでレコードを検索します。
     *
     * @param mixed $id レコードのID。
     * @return array|false 取得されたレコード、見つからない場合はfalse。
     */
    public function findById($id)
    {
        return $this->get(['id' => $id]);
    }

    /**
     * 条件に一致するレコードが存在するか確認します。
     *
     * @param array $conditions 存在を確認する条件。
     * @return bool 条件に一致するレコードが存在する場合はtrue、それ以外はfalse。
     */
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    /**
     * 最後に挿入されたレコードのIDを取得します。
     *
     * @return string 最後に挿入されたレコードのID。
     */
    public function lastInsertId(): string
    {
        return $this->pdoObject->get()->lastInsertId();
    }

    /**
     * 安全なSQLクエリを実行します。
     *
     * @param string $query 実行するSQLクエリ。
     * @param array $params クエリにバインドするパラメータ。
     * @param array $allowedTypes 許可するクエリのタイプ（例: ['SELECT', 'UPDATE', 'DELETE', 'INSERT']）。
     * @return PDOStatement 実行されたステートメント。
     * @throws RuntimeException 安全でないクエリまたはその他のエラー。
     */
    public function executeQuery(string $query, array $params = [], array $allowedTypes = ['SELECT', 'UPDATE', 'DELETE', 'INSERT', 'SHOW']): PDOStatement
    {
        // クエリタイプの確認 (ホワイトリスト方式)
        $queryType = strtoupper(strtok(trim($query), ' '));
        if (!in_array($queryType, $allowedTypes, true)) {
            throw new RuntimeException('このクエリタイプは許可されていません: ' . $queryType);
        }
    
        try {
            // クエリの準備
            $stmt = $this->pdoObject->prepare($query);
    
            // パラメータをバインドしてクエリを実行
            $stmt->execute($params);
    
            // クエリタイプに応じてロギングする
            if ($queryType !== 'SELECT') {
                $this->logOperation($queryType, $this->tableName, null, ['query' => $query, 'params' => $params]);
            }
    
            return $stmt;
        } catch (PDOException $e) {
            throw new RuntimeException('クエリ実行エラー: ' . $e->getMessage() . '：SQL( ' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . ' )');
        }
    }


    /**
     * ページネーションでレコードを取得します。
     *
     * @param int $page 現在のページ番号。
     * @param int $pageSize 1ページあたりのレコード数。
     * @param array $conditions 選択する条件。
     * @return array ページネーションされたレコード。
     */
    public function paginate(int $page, int $pageSize, array $conditions = []): array
    {
        $offset = ($page - 1) * $pageSize;
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} $wherePart LIMIT :offset, :limit";
        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->execute(array_values($conditions));
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('ページネーションエラー: ' . $e->getMessage());
        }
    }

    /**
     * 複数のレコードを一度に挿入します。
     *
     * @param array $dataList 挿入するデータの配列（各データは連想配列）。
     */
    public function bulkInsert(array $dataList)
    {
        if (empty($dataList)) return;

        $columns = implode(", ", array_keys($dataList[0]));
        $placeholders = implode(", ", array_fill(0, count($dataList[0]), "?"));
        $sql = "INSERT INTO {$this->tableName} ($columns) VALUES ($placeholders)";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            foreach ($dataList as $data) {
                $stmt->execute(array_values($data));
                $lastInsertId = $this->pdoObject->lastInsertId();
                $this->logOperation('INSERT', $this->tableName, $lastInsertId, $data);
            }
        } catch (PDOException $e) {
            throw new RuntimeException('バルク挿入エラー: ' . $e->getMessage());
        }
    }

    /**
     * 特定のカラムのユニークな値を取得します。
     *
     * @param string $column カラム名。
     * @param array $conditions 選択する条件。
     * @return array ユニークな値の配列。
     */
    public function getDistinct(string $column, array $conditions = []): array
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT DISTINCT $column FROM {$this->tableName} $wherePart";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_values($conditions));
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new RuntimeException('ユニーク値取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * 特定のカラムの集計値を取得します。
     *
     * @param string $function 集計関数（SUM, MIN, MAX）。
     * @param string $column カラム名。
     * @param array $conditions 集計する条件。
     * @return float 集計値。
     */
    private function getAggregateValue(string $function, string $column, array $conditions = []): float
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT $function($column) as value FROM {$this->tableName} $wherePart";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_values($conditions));
            $result = $stmt->fetch();
            return $result ? (float)$result['value'] : 0.0;
        } catch (PDOException $e) {
            throw new RuntimeException("$function エラー: " . $e->getMessage());
        }
    }

    /**
     * 特定のカラムの合計値を取得します。
     *
     * @param string $column カラム名。
     * @param array $conditions 合計する条件。
     * @return float 合計値。
     */
    public function sum(string $column, array $conditions = []): float
    {
        return $this->getAggregateValue('SUM', $column, $conditions);
    }

    /**
     * 特定のカラムの最小値を取得します。
     *
     * @param string $column カラム名。
     * @param array $conditions 最小値を取得する条件。
     * @return float 最小値。
     */
    public function min(string $column, array $conditions = []): float
    {
        return $this->getAggregateValue('MIN', $column, $conditions);
    }

    /**
     * 特定のカラムの最大値を取得します。
     *
     * @param string $column カラム名。
     * @param array $conditions 最大値を取得する条件。
     * @return float 最大値。
     */
    public function max(string $column, array $conditions = []): float
    {
        return $this->getAggregateValue('MAX', $column, $conditions);
    }

    /**
     * 特定の日付範囲内のレコードを取得します。
     *
     * @param string $column 日付を格納しているカラム名。
     * @param string $startDate 範囲の開始日（YYYY-MM-DD形式）。
     * @param string $endDate 範囲の終了日（YYYY-MM-DD形式）。
     * @param array $conditions その他の条件。
     * @return array 取得されたレコード。
     */
    public function getRecordsByDateRange(string $column, string $startDate, string $endDate, array $conditions = []): array
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} WHERE $wherePart $column BETWEEN ? AND ?";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($conditions), [$startDate, $endDate]));
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('日付範囲内のレコード取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * 最近N日以内に作成または更新されたレコードを取得します。
     *
     * @param string $column 日付を格納しているカラム名（例: 'created_at'）。
     * @param int $days 最近の日数（例: 7は過去7日間）。
     * @param array $conditions その他の条件。
     * @return array 取得されたレコード。
     */
    public function getRecentRecords(string $column, int $days, array $conditions = []): array
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} WHERE $wherePart $column >= DATE_SUB(NOW(), INTERVAL ? DAY)";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($conditions), [$days]));
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('最近のレコード取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * 特定の日付に作成または更新されたレコードを取得します。
     *
     * @param string $column 日付を格納しているカラム名。
     * @param string $date 取得したい日付（YYYY-MM-DD形式）。
     * @param array $conditions その他の条件。
     * @return array 取得されたレコード。
     */
    public function getRecordsByExactDate(string $column, string $date, array $conditions = []): array
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} WHERE $wherePart DATE($column) = ?";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($conditions), [$date]));
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('特定の日付のレコード取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * 特定の月の最初のレコードを取得します。
     *
     * @param string $column 日付を格納しているカラム名。
     * @param string $year 年（YYYY形式）。
     * @param string $month 月（MM形式）。
     * @param array $conditions その他の条件。
     * @return array|false 取得されたレコード、見つからない場合はfalse。
     */
    public function getFirstRecordOfMonth(string $column, string $year, string $month, array $conditions = [])
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} WHERE $wherePart YEAR($column) = ? AND MONTH($column) = ? ORDER BY $column ASC LIMIT 1";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($conditions), [$year, $month]));
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new RuntimeException('特定の月の最初のレコード取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * 特定の日の最後のレコードを取得します。
     *
     * @param string $column 日付を格納しているカラム名。
     * @param string $date 日付（YYYY-MM-DD形式）。
     * @param array $conditions その他の条件。
     * @return array|false 取得されたレコード、見つからない場合はfalse。
     */
    public function getLastRecordOfDay(string $column, string $date, array $conditions = [])
    {
        $wherePart = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM {$this->tableName} WHERE $wherePart DATE($column) = ? ORDER BY $column DESC LIMIT 1";

        try {
            $stmt = $this->pdoObject->prepare($sql);
            $stmt->execute(array_merge(array_values($conditions), [$date]));
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new RuntimeException('特定の日の最後のレコード取得エラー: ' . $e->getMessage());
        }
    }

    /**
     * トランザクションを開始します。
     */
    public function beginTransaction()
    {
        $this->pdoObject->beginTransaction();
    }

    /**
     * トランザクションをコミットします。
     */
    public function commit()
    {
        $this->pdoObject->commit();
    }

    /**
     * トランザクションをロールバックします。
     */
    public function rollback()
    {
        $this->pdoObject->rollback();
    }
}
