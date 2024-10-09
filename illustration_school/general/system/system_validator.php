<?php
/**
 * 環境の必須項目を確認するスクリプト
 * 
 * - PHPバージョンの確認 (8.0以上)
 * - GMP拡張の確認
 */

function checkRequirements() {
    $check = true;
    // PHPバージョンの確認
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        echo "エラー: PHP 8.0以上が必要です。現在のバージョン: " . PHP_VERSION . "\n";
        $check = false;
    }

    // GMP拡張の確認
    if (!extension_loaded('gmp')) {
        echo "エラー: GMP拡張がインストールされていません。\n";
        $check = false;
    }

    return $check;
}

// チェックを実行
if(!checkRequirements()){
    exit;
}