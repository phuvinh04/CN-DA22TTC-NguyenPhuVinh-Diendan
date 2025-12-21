<?php
/**
 * Google Login - Redirect to Google OAuth
 */
require_once 'config/google_config.php';
require_once 'config/session.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (getCurrentUser()) {
    header('Location: index.php');
    exit();
}

// Chuyển hướng đến Google OAuth
header('Location: ' . getGoogleLoginUrl());
exit();
