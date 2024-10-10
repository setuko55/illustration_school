<?php

class DelStock {
    // テスト用のテーブルを設定
    private $tableName = \TableName::stocks;
    private $attacker;

    //商品DB設定
    private $md_tableName = \TableName::merchandise;
    private $md_attacker;
    
    function __construct(){
        $pdoConfig = new \PDOConfig();
        $pdoObject = new \PDOObject($pdoConfig);
        $this -> attacker = new SimpleDBAttacker($pdoObject);
        $this -> attacker -> setTable($this -> tableName);
        $this -> md_attacker = new SimpleDBAttacker($pdoObject);
        $this -> md_attacker -> setTable($this -> md_tableName);
    }

    /**
     * 在庫を削除
     * 
     * @return void
     */
    public function delStock() {
        $check_md = $this -> md_attacker -> get(['jancode' => $_POST['jan']]);
        if(is_array($check_md)){
            $check_st = $this -> attacker -> get(['merchandise_id' => $check_md['ID'], 'work_id' => $_POST['work_id']]);
            if(is_array($check_st)){
                //在庫にスキャンした商品が存在する場合
                $num = $check_st['quantity'];
                if($num > 0){
                    $num -= 1;
                    $this -> attacker -> update(['quantity' => $num], ['merchandise_id' => $check_md['ID'], 'work_id' => $_POST['work_id']]);
                } 
                $mes = 'true';
            }else{
                $mes = 'false';
            }
            jsonResponse(['print' => $mes]);
        }else{
            jsonResponse(['error' => 'false']);
        }
    }

}
