<?php
// データベース接続の確立
$pdoConfig = new PDOConfig();
$pdoObject = new PDOObject($pdoConfig);

// テスト用のテーブルを設定
$tableName = \TableName::TEST;
$attacker = new SimpleDBAttacker($pdoObject);
$attacker->setTable($tableName);

// テーブルを作成
$columns = [
    ['name' => 'id', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'name', 'type' => 'VARCHAR(255)'],
    ['name' => 'age', 'type' => 'INT'],
    ['name' => 'created_at', 'type' => 'DATETIME DEFAULT CURRENT_TIMESTAMP']
];
$pdoObject->createTable($tableName, $columns);

// 動作確認用の関数
function testSimpleDBAttacker(SimpleDBAttacker $attacker)
{
    echo "<h3>テスト開始</h3>";

    echo "<p>現在のユーザーID:".getUserId()."</p><br>";

    // データ挿入
    echo "<h4>データ挿入</h4>";
    try {
        $attacker->insert(['name' => 'Alice', 'age' => 30]);
        $attacker->insert(['name' => 'Bob', 'age' => 25]);
        echo "データ挿入成功<br>";
    } catch (Exception $e) {
        echo "データ挿入エラー: " . $e->getMessage() . "<br>";
    }

    // 全レコード取得
    echo "<h4>全レコード取得</h4>";
    try {
        $records = $attacker->getAll();
        echo "全レコード取得: " . json_encode($records) . "<br>";
    } catch (Exception $e) {
        echo "データ取得エラー: " . $e->getMessage() . "<br>";
    }

    // データ更新
    echo "<h4>データ更新</h4>";
    try {
        $attacker->update(['age' => 35], ['name' => 'Alice']);
        $updatedRecord = $attacker->get(['name' => 'Alice']);
        echo "データ更新成功: " . json_encode($updatedRecord) . "<br>";
    } catch (Exception $e) {
        echo "データ更新エラー: " . $e->getMessage() . "<br>";
    }

    // 条件付きレコード取得
    echo "<h4>条件付きレコード取得</h4>";
    try {
        $record = $attacker->get(['name' => 'Bob']);
        echo "条件付きレコード取得: " . json_encode($record) . "<br>";
    } catch (Exception $e) {
        echo "条件付きレコード取得エラー: " . $e->getMessage() . "<br>";
    }

    // レコード数のカウント
    echo "<h4>レコード数のカウント</h4>";
    try {
        $count = $attacker->count();
        echo "レコードの総数: " . $count . "<br>";
    } catch (Exception $e) {
        echo "レコードカウントエラー: " . $e->getMessage() . "<br>";
    }

    // プライマリキーによるレコード検索
    echo "<h4>プライマリキーでのレコード検索</h4>";
    try {
        $recordById = $attacker->findById(1); // IDが1のレコードを検索
        echo "プライマリキーによるレコード取得: " . json_encode($recordById) . "<br>";
    } catch (Exception $e) {
        echo "プライマリキー検索エラー: " . $e->getMessage() . "<br>";
    }

    // 条件に一致するレコードが存在するか確認
    echo "<h4>条件に一致するレコードの存在確認</h4>";
    try {
        $exists = $attacker->exists(['name' => 'Bob']);
        echo "条件に一致するレコードが存在するか: " . ($exists ? 'はい' : 'いいえ') . "<br>";
    } catch (Exception $e) {
        echo "レコード存在確認エラー: " . $e->getMessage() . "<br>";
    }

    // 最後に挿入されたIDを取得
    echo "<h4>最後に挿入されたIDの取得</h4>";
    try {
        $lastInsertId = $attacker->lastInsertId();
        echo "最後に挿入されたID: " . $lastInsertId . "<br>";
    } catch (Exception $e) {
        echo "最後に挿入されたID取得エラー: " . $e->getMessage() . "<br>";
    }

    // 任意のクエリの実行
    echo "<h4>任意のSQLクエリの実行</h4>";
    $sql = 'SELECT COUNT(*) as total FROM ' . $attacker->getTableName();
    echo '実行コード：'.$sql.'<br>';
    try {
        $stmt = $attacker->executeQuery($sql);
        $result = $stmt->fetch();
        echo "クエリ実行結果: " . json_encode($result) . "<br>";
    } catch (Exception $e) {
        echo "クエリ実行エラー: " . $e->getMessage() . "<br>";
    }

    // 特定のカラムのユニークな値を取得
    echo "<h4>特定のカラムのユニークな値を取得</h4>";
    try {
        $distinctNames = $attacker->getDistinct('name');
        echo "ユニークな名前取得: " . json_encode($distinctNames) . "<br>";
    } catch (Exception $e) {
        echo "ユニーク値取得エラー: " . $e->getMessage() . "<br>";
    }

    // 特定のカラムの合計値を取得
    echo "<h4>特定のカラムの合計値を取得</h4>";
    try {
        $totalAge = $attacker->sum('age');
        echo "年齢の合計: " . $totalAge . "<br>";
    } catch (Exception $e) {
        echo "合計値取得エラー: " . $e->getMessage() . "<br>";
    }

    // 特定のカラムの最小値と最大値を取得
    echo "<h4>特定のカラムの最小値と最大値を取得</h4>";
    try {
        $minAge = $attacker->min('age');
        $maxAge = $attacker->max('age');
        echo "最小年齢: " . $minAge . ", 最大年齢: " . $maxAge . "<br>";
    } catch (Exception $e) {
        echo "最小値/最大値取得エラー: " . $e->getMessage() . "<br>";
    }

    // 日付範囲内のレコード取得
    echo "<h4>日付範囲内のレコード取得</h4>";
    try {
        $recordsByDateRange = $attacker->getRecordsByDateRange('created_at', '2022-01-01', '2025-01-01');
        echo "指定した日付範囲内のレコード: " . json_encode($recordsByDateRange) . "<br>";
    } catch (Exception $e) {
        echo "日付範囲内のレコード取得エラー: " . $e->getMessage() . "<br>";
    }

    // 最近N日以内のレコードを取得
    echo "<h4>最近N日以内のレコード取得</h4>";
    try {
        $recentRecords = $attacker->getRecentRecords('created_at', 7);
        echo "最近7日以内のレコード: " . json_encode($recentRecords) . "<br>";
    } catch (Exception $e) {
        echo "最近のレコード取得エラー: " . $e->getMessage() . "<br>";
    }

    // 特定の日付のレコード取得
    echo "<h4>特定の日付のレコード取得</h4>";
    try {
        $recordsByExactDate = $attacker->getRecordsByExactDate('created_at', '2023-01-01');
        echo "指定した日付のレコード: " . json_encode($recordsByExactDate) . "<br>";
    } catch (Exception $e) {
        echo "特定の日付のレコード取得エラー: " . $e->getMessage() . "<br>";
    }

    // 特定の月の最初と最後のレコード取得
    echo "<h4>特定の月の最初と最後のレコード取得</h4>";
    try {
        $firstRecordOfMonth = $attacker->getFirstRecordOfMonth('created_at', '2023', '01');
        $lastRecordOfDay = $attacker->getLastRecordOfDay('created_at', '2023-01-01');
        echo "最初のレコード: " . json_encode($firstRecordOfMonth) . ", 最後のレコード: " . json_encode($lastRecordOfDay) . "<br>";
    } catch (Exception $e) {
        echo "月の最初/最後のレコード取得エラー: " . $e->getMessage() . "<br>";
    }

    // データ削除
    echo "<h4>データ削除</h4>";
    try {
        $attacker->delete(['name' => 'Alice']);
        $remainingRecords = $attacker->getAll();
        echo "データ削除成功。残りのレコード: " . json_encode($remainingRecords) . "<br>";
    } catch (Exception $e) {
        echo "データ削除エラー: " . $e->getMessage() . "<br>";
    }

    echo "<h3>テスト完了</h3>";
}

// テスト関数を呼び出し
testSimpleDBAttacker($attacker);
?>
