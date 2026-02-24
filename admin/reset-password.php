<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$step    = 1;    // 1=enter username, 2=answer security Q, 3=set new password, 4=done
$error   = '';
$success = '';
$username = '';

// ── Step 1 → 2 : verify username and show security question ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step1'])) {
    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        $error = 'Please enter your username.';
        $step  = 1;
    } else {
        $user = $db->getRow(
            "SELECT id, username, security_question FROM users WHERE username = ?",
            [$username], 's'
        );

        if (!$user || empty($user['security_question'])) {
            $error = 'Username not found or no security question set.';
            $step  = 1;
        } else {
            $_SESSION['reset_uid']      = $user['id'];
            $_SESSION['reset_username'] = $user['username'];
            $_SESSION['reset_question'] = $user['security_question'];
            $_SESSION['reset_step']     = 2;
            $step = 2;
        }
    }

// ── Step 2 → 3 : verify answer ───────────────────────────────────────────────
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step2'])) {
    if (empty($_SESSION['reset_uid']) || ($_SESSION['reset_step'] ?? 0) !== 2) {
        $error = 'Session expired. Please start over.';
        $step  = 1;
    } else {
        $answer = trim($_POST['security_answer'] ?? '');

        if (empty($answer)) {
            $error = 'Please enter your answer.';
            $step  = 2;
        } else {
            $user = $db->getRow(
                "SELECT security_answer FROM users WHERE id = ?",
                [$_SESSION['reset_uid']], 'i'
            );

            if ($user && password_verify(strtolower($answer), $user['security_answer'])) {
                $_SESSION['reset_step'] = 3;
                $step = 3;
            } else {
                $error = 'Incorrect answer. Please try again.';
                $step  = 2;
            }
        }
    }

// ── Step 3 → Done : set new password ─────────────────────────────────────────
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step3'])) {
    if (empty($_SESSION['reset_uid']) || ($_SESSION['reset_step'] ?? 0) !== 3) {
        $error = 'Session expired. Please start over.';
        $step  = 1;
    } else {
        $newPass  = $_POST['new_password']     ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (strlen($newPass) < 8) {
            $error = 'Password must be at least 8 characters.';
            $step  = 3;
        } elseif ($newPass !== $confPass) {
            $error = 'Passwords do not match.';
            $step  = 3;
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->query(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [$hash, $_SESSION['reset_uid']], 'si'
            );

            // Clean up session reset data
            unset($_SESSION['reset_uid'], $_SESSION['reset_username'],
                  $_SESSION['reset_question'], $_SESSION['reset_step']);

            $step    = 4;
            $success = 'Password updated successfully! You can now log in.';
        }
    }

// ── Restore step from session after redirect-free POST ───────────────────────
} elseif (!empty($_SESSION['reset_step'])) {
    $step = (int) $_SESSION['reset_step'];
}

// Restore username/question for display
if ($step === 2 && !empty($_SESSION['reset_username'])) {
    $username = $_SESSION['reset_username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo escape(SITE_NAME); ?></title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin-login-page">

    <!-- Ambient background shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-box">
        <div class="login-header">
            <div class="login-brand-icon">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="18" stroke="white" stroke-width="2" fill="rgba(255,255,255,0.1)"/>
                    <path d="M12 28 L20 12 L28 28" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="15" y1="22" x2="25" y2="22" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <h1>Reset Password</h1>
            <p>
                <?php if ($step === 1): ?>
                    Enter your admin username to get started
                <?php elseif ($step === 2): ?>
                    Answer your security question
                <?php elseif ($step === 3): ?>
                    Choose a new password
                <?php else: ?>
                    Password reset complete
                <?php endif; ?>
            </p>
        </div>

        <?php if ($error): ?>
        <div class="flash flash-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-circle-check"></i> <?php echo escape($success); ?>
        </div>
        <?php endif; ?>

        <!-- ── STEP 1: Enter username ── -->
        <?php if ($step === 1): ?>
        <form method="POST" class="admin-form">
            <div class="field">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" 
                       placeholder="Enter your username" 
                       value="<?php echo escape($username); ?>" 
                       required autofocus>
            </div>
            <button type="submit" name="step1" value="1" class="btn-primary" style="width:100%">
                Continue <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <!-- ── STEP 2: Security question ── -->
        <?php elseif ($step === 2): ?>
        <form method="POST" class="admin-form">
            <div class="field">
                <label>Security Question</label>
                <div style="background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; color:var(--text-strong); font-weight:500">
                    <?php echo escape($_SESSION['reset_question'] ?? ''); ?>
                </div>
            </div>
            <div class="field">
                <label for="security_answer">Your Answer</label>
                <div style="position:relative">
                    <input type="password" id="security_answer" name="security_answer" placeholder="Enter your answer" required autofocus>
                    <button type="button" class="pw-toggle" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <p class="form-hint">Answer is not case-sensitive</p>
            </div>
            <button type="submit" name="step2" value="1" class="btn-primary" style="width:100%">
                Verify Answer <i class="fa-solid fa-shield-halved"></i>
            </button>
        </form>

        <!-- ── STEP 3: New password ── -->
        <?php elseif ($step === 3): ?>
        <form method="POST" class="admin-form" id="resetForm">
            <div class="field">
                <label for="new_password">New Password</label>
                <div style="position:relative">
                    <input type="password" id="new_password" name="new_password" placeholder="At least 8 characters" required autofocus oninput="checkStrength(this.value)">
                    <button type="button" class="pw-toggle" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <div style="height:4px; background:rgba(255,255,255,0.1); border-radius:2px; margin-top:8px; overflow:hidden">
                    <div id="strengthFill" style="height:100%; width:0%; transition:all 0.3s ease"></div>
                </div>
                <p class="form-hint" id="strengthLabel"></p>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm Password</label>
                <div style="position:relative">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    <button type="button" class="pw-toggle" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <p class="form-hint" id="matchLabel"></p>
            </div>
            <button type="submit" name="step3" value="1" class="btn-primary" style="width:100%">
                Set New Password <i class="fa-solid fa-key"></i>
            </button>
        </form>

        <!-- ── STEP 4: Done ── -->
        <?php elseif ($step === 4): ?>
        <div style="text-align:center; padding:1.5rem 0">
            <div style="font-size:3.5rem; margin-bottom:1.5rem">🎉</div>
            <p style="color:var(--text); margin-bottom:2rem; line-height:1.6">
                Your password has been updated successfully. You can now use your new password to access the admin panel.
            </p>
            <a href="<?php echo BASE_URL; ?>/admin/" class="btn-primary" style="width:100%; display:inline-block">
                Go to Login <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>

        <div style="margin-top:2rem; border-top:1px solid var(--glass-border); padding-top:1.5rem; text-align:center">
            <a href="<?php echo BASE_URL; ?>/admin/" style="color:var(--text-muted); font-size:0.9rem; text-decoration:none">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
    // Password visibility toggle
    document.querySelectorAll('.pw-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        });
    });

    // Strength meter
    function checkStrength(val) {
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        if (!fill) return;
        
        let score = 0;
        if (val.length >= 8) score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        
        const levels = [
            {pct:'20%', color:'#ff5252', text:'Very weak'},
            {pct:'40%', color:'#ff9800', text:'Weak'},
            {pct:'60%', color:'#ffab40', text:'Fair'},
            {pct:'80%', color:'#00e676', text:'Strong'},
            {pct:'100%',color:'#00e676', text:'Very strong'},
        ];
        
        const lvl = levels[Math.max(0, score - 1)] || levels[0];
        fill.style.width = val.length ? lvl.pct : '0%';
        fill.style.background = lvl.color;
        label.textContent = val.length ? lvl.text : '';
        label.style.color = lvl.color;
    }

    // Match check
    document.getElementById('confirm_password')?.addEventListener('input', function() {
        const np = document.getElementById('new_password').value;
        const lbl = document.getElementById('matchLabel');
        if (!this.value) { lbl.textContent = ''; return; }
        if (this.value === np) {
            lbl.textContent = '✓ Passwords match';
            lbl.style.color = 'var(--accent)';
        } else {
            lbl.textContent = '✗ Passwords do not match';
            lbl.style.color = '#f87171';
        }
    });
    </script>
</body>
</html>
