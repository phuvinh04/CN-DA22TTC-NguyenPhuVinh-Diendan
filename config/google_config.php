<?php
/**
 * Cấu hình Google OAuth
 * 
 * Hướng dẫn lấy Client ID và Secret:
 * 1. Vào https://console.cloud.google.com/
 * 2. Tạo project mới hoặc chọn project có sẵn
 * 3. Vào APIs & Services > Credentials
 * 4. Create Credentials > OAuth client ID
 * 5. Application type: Web application
 * 6. Authorized redirect URIs: http://localhost/diendan/google-callback.php
 * 7. Copy Client ID và Client Secret vào đây
 */

define('GOOGLE_CLIENT_ID', '1015968707492-d50bpregua4nq85tub5dbfu1hld28h2u.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/diendan/google-callback.php');


/**
 * Tạo URL đăng nhập Google
 */
function getGoogleLoginUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Lấy access token từ code
 */
function getGoogleAccessToken($code) {
    $url = 'https://oauth2.googleapis.com/token';
    
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Lấy thông tin user từ Google
 */
function getGoogleUserInfo($accessToken) {
    $url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
