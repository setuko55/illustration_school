<?php

/**
 * シャミアの秘密分散クラス
 * このクラスは、シャミアの秘密分散方式を実装し、秘密の分割と復元を行う。
 */
class ShamirSecretSharing 
{
    private $prime;

    /**
     * コンストラクタ。素数を設定して、有限体での計算に使用する。
     *
     * @param int $prime 限定された数値範囲で使用する素数。
     */
    public function __construct(int $prime) {
        $this->prime = $prime;
    }

    /**
     * 秘密を n 個のシェアに分割し、k 個のシェアで復元可能にする。
     *
     * @param int $secret 分割する秘密。
     * @param int $n 生成するシェアの総数。
     * @param int $k 復元に必要なシェアの最小数。
     * @return array シェアの配列。各シェアは [x, f(x)] の形式。
     */
    public function splitSecret($secret, $n, $k) {
        $shares = [];
        $coefficients = $this->generateCoefficients($secret, $k);

        for ($i = 1; $i <= $n; $i++) {
            $x = $i;
            $y = $this->evaluatePolynomial($coefficients, $x);
            $shares[] = [$x, $y]; // 各シェアは [x, y] の形式
        }
        return $shares;
    }

    /**
     * 与えられた k 個以上のシェアから秘密を復元する。
     *
     * @param array $shares シェアの配列。各シェアは [x, f(x)] の形式。
     * @return int 復元された秘密。
     */
    public function recoverSecret($shares) {
        $secret = 0;
        $k = count($shares);
        
        for ($i = 0; $i < $k; $i++) {
            $xi = $shares[$i][0];
            $yi = $shares[$i][1];
            $li = 1;

            for ($j = 0; $j < $k; $j++) {
                if ($i != $j) {
                    $xj = $shares[$j][0];
                    // ラグランジュ係数を計算: li *= (xj / (xj - xi))
                    $li *= ($xj * $this->modInverse($xj - $xi, $this->prime)) % $this->prime;
                    $li %= $this->prime;
                }
            }
            // yi * li を加算していく
            $secret += ($yi * $li) % $this->prime;
            $secret %= $this->prime;
        }
        
        return $secret;
    }

    /**
     * ランダムな係数を持つ (k-1) 次の多項式を生成し、秘密を定数項として設定。
     *
     * @param int $secret 定数項となる秘密。
     * @param int $k 復元に必要なシェア数。
     * @return array 多項式の係数配列。
     */
    private function generateCoefficients($secret, $k) {
        $coefficients = [$secret]; // 秘密は定数項
        for ($i = 1; $i < $k; $i++) {
            // 0 から prime-1 までのランダムな係数を生成
            $coefficients[] = rand(0, $this->prime - 1);
        }
        return $coefficients;
    }

    /**
     * 指定された x の値に対して、多項式を評価する。
     *
     * @param array $coefficients 多項式の係数。
     * @param int $x 評価する x の値。
     * @return int 多項式の評価結果。
     */
    private function evaluatePolynomial($coefficients, $x) {
        $y = 0;
        $degree = count($coefficients) - 1;
        for ($i = 0; $i <= $degree; $i++) {
            $y = ($y + $coefficients[$i] * pow($x, $i)) % $this->prime;
        }
        return $y;
    }

    /**
     * 拡張ユークリッド法を使用して、指定された値のモジュラー逆元を計算する。
     *
     * @param int $a 逆元を計算する値。
     * @param int $prime 素数（法として使用）。
     * @return int モジュラー逆元。
     */
    private function modInverse($a, $prime) {
        $prime0 = $prime;
        $y = 0;
        $x = 1;

        if ($prime == 1) return 0;

        while ($a > 1) {
            $q = intdiv($a, $prime);
            $t = $prime;

            $prime = $a % $prime;
            $a = $t;
            $t = $y;

            $y = $x - $q * $y;
            $x = $t;
        }

        if ($x < 0) $x += $prime0;

        return $x;
    }
}
