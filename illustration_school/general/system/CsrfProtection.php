<?php
/**
 * 
 */
class CsrfProtection
{
    private const TOKEN_LENGTH = 32;  // トークンのバイト数
    private const EXPIRATION_TIME = 3600; // トークンの有効期限（秒）

    /**
     * CSRFトークンの検証を行います。
     * 
     * POSTリクエストで送信されたトークンがセッションに保存されたトークンと一致し、
     * かつトークンが有効期限内であることを確認します。
     * 
     * @return bool トークンが有効であればtrue、無効であればfalseを返します。
     */
    public function checkToken(): bool
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_expiry'])) {
            return false;
        }

        // トークンの一致と有効期限の確認
        if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) && time() <= $_SESSION['csrf_token_expiry']) {
            return true;
        }

        // トークンが一致しないか、有効期限切れの場合
        return false;
    }
    
    /**
     * CSRFトークンを含む<input>タグを生成します。
     * 
     * @return string 安全にエスケープされたhidden inputタグのHTML
     */
    public function generateHiddenInput(): string
    {
        $token = htmlspecialchars($this->getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
    
    /**
     * セッションに新しいCSRFトークンを生成して保存します。
     * 
     * 新しいランダムなトークンを生成し、セッションに保存します。また、トークンの有効期限も
     * 設定されます。トークンは32バイトのランダムデータを使用して生成され、その結果は16進数の
     * 文字列に変換されます。
     */
    public function updateToken(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION['csrf_token_expiry'] = time() + self::EXPIRATION_TIME; // 現在の時刻から1時間後に設定
    }

    /**
     * 現在のCSRFトークンを取得します。
     * 
     * セッションにトークンが存在しない、もしくは有効期限が切れている場合は新たに生成して返します。
     * 
     * @return string 現在のCSRFトークン。
     */
    public function getToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || time() > $_SESSION['csrf_token_expiry']) {
            $this->updateToken();  // トークンが無効か期限切れなら更新する
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * 現在のCSRFトークンの有効期限を取得します。
     * 
     * セッションに保存されているトークンの有効期限を返します。有効期限が設定されていない場合は0を返します。
     * 
     * @return int トークンの有効期限（タイムスタンプ）。
     */
    public function getTokenExpiry(): int
    {
        return $_SESSION['csrf_token_expiry'] ?? 0;
    }

    /**
     * CSRFトークンを手動で無効化します。
     * 
     * セッションに保存されたトークンとその有効期限を削除します。
     */
    public function invalidateToken(): void
    {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_expiry']);
    }
}
