<?php
/**
 * テーブルのスキーマを同期する関数。
 * 指定されたカラムがテーブルに存在しなければ追加し、不要なカラムは削除する。
 *
 * @param PDOObject $pdoObject データベース接続オブジェクト
 * @param string $tableName テーブル名
 * @param array $requiredColumns 必要なカラムの配列
 * @return void
 */
function syncTableSchema(PDOObject $pdoObject, string $tableName, array $requiredColumns): void
{
    // テーブル名のサニタイズ
    $safeTableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

    // テーブルが存在するかを確認
    if (!$pdoObject->tableExists($safeTableName)) {
        // テーブルが存在しない場合、新規作成
        $columnsSql = implode(", ", array_map(function ($col) {
            $safeColumnName = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
            return "`$safeColumnName` {$col['type']}" . (isset($col['options']) ? " {$col['options']}" : "");
        }, $requiredColumns));

        $sql = "CREATE TABLE `$safeTableName` ($columnsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        try {
            $pdoObject->get()->exec($sql);
        } catch (PDOException $e) {
            throw new RuntimeException('テーブル作成エラー: ' . $e->getMessage());
        }
        return;
    }
    
    // 現在のカラムを取得
    $existingColumns = $pdoObject->getTableColumns($tableName);
    
    // 必要なカラムの名前を取得
    $requiredColumnNames = array_column($requiredColumns, 'name');
    
    // 追加する必要があるカラムを見つける
    $columnsToAdd = array_filter($requiredColumns, function($col) use ($existingColumns) {
        return !in_array($col['name'], $existingColumns);
    });
    
    // 削除する必要があるカラムを見つける
    $columnsToDelete = array_diff($existingColumns, $requiredColumnNames);
    
    // カラムを追加
    foreach ($columnsToAdd as $col) {
        $safeColumnName = preg_replace('/[^a-zA-Z0-9_]/', '', $col['name']);
        $sql = "ALTER TABLE `$safeTableName` ADD COLUMN `$safeColumnName` {$col['type']}" . (isset($col['options']) ? " {$col['options']}" : "");
        try {
            $pdoObject->get()->exec($sql);
        } catch (PDOException $e) {
            throw new RuntimeException('カラム追加エラー: ' . $e->getMessage());
        }
    }
    
    // カラムを削除
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
