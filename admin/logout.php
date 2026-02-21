<?php
require_once '../includes/auth.php';

$auth->requireLogin();
verifyCsrf();

$auth->logout();
header('Location: ' . BASE_URL . '/admin/?logged_out=1');
exit;