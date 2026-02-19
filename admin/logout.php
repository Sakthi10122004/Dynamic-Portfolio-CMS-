<?php
require_once '../includes/auth.php';
$auth->logout();
header('Location: ' . BASE_URL . '/admin/');
exit;