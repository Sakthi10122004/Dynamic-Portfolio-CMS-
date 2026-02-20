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
    <title>Reset Password | <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <a href="<?php echo BASE_URL; ?>/" class="login-logo">✦ Sakthi</a>
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

            <!-- Progress indicators -->
            <?php if ($step < 4): ?>
            <div class="reset-steps">
                <div class="reset-step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'done' : ''; ?>">
                    <span class="rs-num"><?php echo $step > 1 ? '✓' : '1'; ?></span>
                    <span class="rs-label">Username</span>
                </div>
                <div class="reset-step-line"></div>
                <div class="reset-step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'done' : ''; ?>">
                    <span class="rs-num"><?php echo $step > 2 ? '✓' : '2'; ?></span>
                    <span class="rs-label">Security Q</span>
                </div>
                <div class="reset-step-line"></div>
                <div class="reset-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                    <span class="rs-num">3</span>
                    <span class="rs-label">New Password</span>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="error-message"><?php echo escape($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success-message"><?php echo escape($success); ?></div>
            <?php endif; ?>

            <!-- ── STEP 1: Enter username ── -->
            <?php if ($step === 1): ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           placeholder="Enter your admin username"
                           value="<?php echo escape($username); ?>"
                           required autofocus>
                </div>
                <button type="submit" name="step1" value="1" class="btn primary full-width">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- ── STEP 2: Security question ── -->
            <?php elseif ($step === 2): ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>Security Question</label>
                    <div class="security-question-display">
                        <?php echo escape($_SESSION['reset_question'] ?? ''); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="security_answer">Your Answer</label>
                    <div class="pw-wrap">
                        <input type="password"
                               id="security_answer"
                               name="security_answer"
                               placeholder="Enter your answer"
                               required autofocus>
                        <button type="button" class="pw-toggle" id="ansToggle" aria-label="Toggle answer visibility">
                            <i class="fa-regular fa-eye" id="ansIcon"></i>
                        </button>
                    </div>
                    <small style="color:var(--text-muted);margin-top:0.4rem;display:block;">
                        Answer is not case-sensitive
                    </small>
                </div>
                <button type="submit" name="step2" value="1" class="btn primary full-width">
                    Verify <i class="fa-solid fa-shield-halved"></i>
                </button>
            </form>
            <script>
            document.getElementById('ansToggle').addEventListener('click', function () {
                var inp = document.getElementById('security_answer');
                var icn = document.getElementById('ansIcon');
                if (inp.type === 'password') {
                    inp.type = 'text';
                    icn.className = 'fa-regular fa-eye-slash';
                } else {
                    inp.type = 'password';
                    icn.className = 'fa-regular fa-eye';
                }
            });
            </script>

            <!-- ── STEP 3: New password ── -->
            <?php elseif ($step === 3): ?>
            <form method="POST" class="login-form" id="resetForm">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="pw-wrap">
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               placeholder="At least 8 characters"
                               required autofocus
                               oninput="checkStrength(this.value)">
                        <button type="button" class="pw-toggle" id="np-toggle" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye" id="np-icon"></i>
                        </button>
                    </div>
                    <div class="pw-strength-bar" id="strengthBar"><div id="strengthFill"></div></div>
                    <small id="strengthLabel" style="margin-top:0.25rem;display:block;"></small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               placeholder="Re-enter password"
                               required>
                        <button type="button" class="pw-toggle" id="cp-toggle" aria-label="Toggle confirm visibility">
                            <i class="fa-regular fa-eye" id="cp-icon"></i>
                        </button>
                    </div>
                    <small id="matchLabel" style="margin-top:0.25rem;display:block;"></small>
                </div>
                <button type="submit" name="step3" value="1" class="btn primary full-width">
                    Set New Password <i class="fa-solid fa-key"></i>
                </button>
            </form>
            <script>
            // Toggle helpers
            function makeToggle(btnId, iconId, inputId) {
                document.getElementById(btnId).addEventListener('click', function () {
                    var inp = document.getElementById(inputId);
                    var icn = document.getElementById(iconId);
                    if (inp.type === 'password') {
                        inp.type = 'text';
                        icn.className = 'fa-regular fa-eye-slash';
                    } else {
                        inp.type = 'password';
                        icn.className = 'fa-regular fa-eye';
                    }
                });
            }
            makeToggle('np-toggle', 'np-icon', 'new_password');
            makeToggle('cp-toggle', 'cp-icon', 'confirm_password');

            // Strength meter
            function checkStrength(val) {
                var fill  = document.getElementById('strengthFill');
                var label = document.getElementById('strengthLabel');
                var score = 0;
                if (val.length >= 8)  score++;
                if (val.length >= 12) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                var levels = [
                    {pct:'20%', color:'#ff5252', text:'Very weak'},
                    {pct:'40%', color:'#ff9800', text:'Weak'},
                    {pct:'60%', color:'#ffab40', text:'Fair'},
                    {pct:'80%', color:'#00e676', text:'Strong'},
                    {pct:'100%',color:'#00e676', text:'Very strong'},
                ];
                var lvl = levels[Math.max(0, score - 1)] || levels[0];
                fill.style.width = val.length ? lvl.pct : '0';
                fill.style.background = lvl.color;
                label.textContent = val.length ? lvl.text : '';
                label.style.color = lvl.color;
            }

            // Match check
            document.getElementById('confirm_password').addEventListener('input', function () {
                var np  = document.getElementById('new_password').value;
                var lbl = document.getElementById('matchLabel');
                if (!this.value) { lbl.textContent = ''; return; }
                if (this.value === np) {
                    lbl.textContent = '✓ Passwords match';
                    lbl.style.color = 'var(--plasma-green)';
                } else {
                    lbl.textContent = '✗ Passwords do not match';
                    lbl.style.color = 'var(--error)';
                }
            });

            // Client-side validation before submit
            document.getElementById('resetForm').addEventListener('submit', function (e) {
                var np = document.getElementById('new_password').value;
                var cp = document.getElementById('confirm_password').value;
                if (np !== cp) {
                    e.preventDefault();
                    document.getElementById('matchLabel').textContent = '✗ Passwords do not match';
                    document.getElementById('matchLabel').style.color = 'var(--error)';
                }
            });
            </script>

            <!-- ── STEP 4: Done ── -->
            <?php elseif ($step === 4): ?>
            <div style="text-align:center;padding:1.5rem 0;">
                <div style="font-size:3rem;margin-bottom:1rem;">🔐</div>
                <p style="color:var(--text-secondary);margin-bottom:1.5rem;">
                    Your password has been updated. You can now sign in with your new credentials.
                </p>
                <a href="<?php echo BASE_URL; ?>/admin/" class="btn primary full-width">
                    Go to Login <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>

            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>/admin/">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
