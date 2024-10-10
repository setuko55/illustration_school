<?php
// システム上必須な項目を登録

// 必要なカラムの定義 (ADMIN_TOKENS)
$requiredColumnsTokens = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'user_id', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL'],
    ['name' => 'token', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL'],
    ['name' => 'expiry', 'type' => 'DATETIME', 'options' => 'NOT NULL'],
    ['name' => 'created_at', 'type' => 'DATETIME', 'options' => 'NOT NULL'],
    ['name' => 'last_used_at', 'type' => 'DATETIME', 'options' => 'NULL'], // 最後に使用された時刻
    ['name' => 'IP', 'type' => 'VARCHAR(45)', 'options' => 'NULL'], // 発行時のIPアドレス
    ['name' => 'user_agent', 'type' => 'VARCHAR(255)', 'options' => 'NULL'] // 発行時のユーザーエージェント
];
// テーブルスキーマを同期 (ADMIN_TOKENS)
$SDBA->syncTableSchema($pdoObject, \TableName::ADMIN_TOKENS, $requiredColumnsTokens);

// 必要なカラムの定義 (PRIME_NUMBERS)
$requiredColumnsPrimeNumbers = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'], // 自動インクリメントのID
    ['name' => 'prime_number', 'type' => 'TEXT', 'options' => 'NOT NULL'], // 素数を保存するカラム
    ['name' => 'created_at', 'type' => 'DATETIME', 'options' => 'NOT NULL'], // 素数が生成された日時
    ['name' => 'is_active', 'type' => 'TINYINT(1)', 'options' => 'DEFAULT 1'], // 有効な素数かどうかを示すフラグ（1 = 有効, 0 = 無効）
];
// テーブルスキーマを同期 (PRIME_NUMBERS)
$SDBA->syncTableSchema($pdoObject, \TableName::PRIME_NUMBERS, $requiredColumnsPrimeNumbers);

// 必要なカラムの定義(USERS)
$requiredColumnsUsers = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'user_id', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL UNIQUE'],
    ['name' => 'session_id', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL'],
    ['name' => 'user_agent', 'type' => 'TEXT', 'options' => 'NOT NULL'],
    ['name' => 'user_ip', 'type' => 'VARCHAR(255)', 'options' => 'NOT NULL'],
    ['name' => 'created_at', 'type' => 'DATETIME', 'options' => 'NOT NULL']
];
// テーブルスキーマを同期 
$SDBA->syncTableSchema($pdoObject, \TableName::USERS, $requiredColumnsUsers);

/*
// 作業管理テーブル
$requiredColumnsWorks = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'work_name', 'type' => 'TEXT', 'options' => 'NOT NULL'],
    ['name' => 'date', 'type' => 'DATETIME', 'options' => 'NOT NULL'],
    ['name' => 'manager', 'type' => 'TEXT', 'options' => 'NOT NULL'],
];
// テーブルスキーマを同期 
$SDBA->syncTableSchema($pdoObject, \TableName::works, $requiredColumnsWorks);


// 在庫管理テーブル
$requiredColumnsStocks = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'work_id', 'type' => 'INT', 'options' => 'NOT NULL'],
    ['name' => 'merchandise_id', 'type' => 'INT', 'options' => 'NOT NULL'],
    ['name' => 'quantity', 'type' => 'INT', 'options' => 'NOT NULL'],
];
// テーブルスキーマを同期 
$SDBA->syncTableSchema($pdoObject, \TableName::stocks, $requiredColumnsStocks);


// 商品管理テーブル
$requiredColumnsmerchandise = [
    ['name' => 'ID', 'type' => 'INT', 'options' => 'AUTO_INCREMENT PRIMARY KEY'],
    ['name' => 'part_num', 'type' => 'text', 'options' => 'NOT NULL'],
    ['name' => 'jancode', 'type' => 'text', 'options' => 'NOT NULL'],
];
// テーブルスキーマを同期 
$SDBA->syncTableSchema($pdoObject, \TableName::merchandise, $requiredColumnsmerchandise);

*/