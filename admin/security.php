<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db      = Database::getInstance();
$user    = $auth->getCurrentUser();
$success = '';
$error   = '';

// Get current security question
$currentUser = $db->getRow(
    "SELECT security_question FROM users WHERE id = ?",
    [$user['id']], 'i'
);

// ── Handle Change Password ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    verifyCsrf();

    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password']     ?? '';
    $confPass    = $_POST['confirm_password'] ?? '';

    $row = $db->getRow("SELECT password_hash FROM users WHERE id = ?", [$user['id']], 'i');

    if (!password_verify($currentPass, $row['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPass !== $confPass) {
        $error = 'New passwords do not match.';
    } else {
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $user['id']], 'si');
        $success = 'Password changed successfully.';
    }
}

// ── Handle Security Question Update ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_security'])) {
    verifyCsrf();

    $question = trim($_POST['security_question'] ?? '');
    $answer   = trim($_POST['security_answer']   ?? '');
    $confirm  = trim($_POST['confirm_answer']    ?? '');

    if (empty($question) || empty($answer)) {
        $error = 'Question and answer are required.';
    } elseif ($answer !== $confirm) {
        $error = 'Answers do not match.';
    } else {
        $answerHash = password_hash(strtolower($answer), PASSWORD_BCRYPT, ['cost' => 10]);
        $db->query(
            "UPDATE users SET security_question = ?, security_answer = ? WHERE id = ?",
            [$question, $answerHash, $user['id']], 'ssi'
        );
        $success = 'Security question saved.';
        $currentUser['security_question'] = $question;
    }
}

$pageTitle = 'Security Settings';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-shield-halved" style="color:var(--accent)"></i> Security Settings
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage your password and identity verification methods</p>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="flash flash-success">
            <i class="fa-solid fa-circle-check"></i> <?php echo escape($success); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="flash flash-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
        </div>
        <?php endif; ?>

        <style>
        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            align-items: start;
            margin-top: 1.5rem;
        }
        .pw-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .pw-wrap input {
            width: 100%;
            padding-right: 45px !important;
        }
        .pw-toggle {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pw-toggle:hover {
            color: var(--primary-light);
            transform: scale(1.15);
        }
        .pw-strength-bar {
            height: 6px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            margin-top: 12px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }
        #strengthFill {
            height: 100%;
            width: 0;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.4s ease;
            border-radius: 10px;
        }
        .current-question-box {
            background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.05));
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(99,102,241,0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .current-question-box:hover {
            border-color: rgba(99,102,241,0.5);
            box-shadow: 0 8px 32px rgba(99,102,241,0.2);
        }
        .current-question-box::before {
            content: '\f059';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -10px;
            bottom: -20px;
            font-size: 6rem;
            color: rgba(99,102,241,0.08);
            pointer-events: none;
            transform: rotate(-15deg);
        }
        </style>

        <div class="security-grid">
            <!-- ── Change Password ──────────────────────────── -->
            <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-lock"></i> Change Password</h2>
            </div>
            <div class="admin-card-body">
                <form method="POST" class="admin-form" id="pwForm">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Enter current password" required>
                        <button type="button" class="pw-toggle" data-target="current_password"
                                aria-label="Toggle visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="new_password" name="new_password"
                               placeholder="At least 8 characters" required
                               oninput="checkStrength(this.value)">
                        <button type="button" class="pw-toggle" data-target="new_password"
                                aria-label="Toggle visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <div class="pw-strength-bar"><div id="strengthFill"></div></div>
                    <small id="strengthLabel" style="display:block;margin-top:0.25rem;"></small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Re-enter new password" required>
                        <button type="button" class="pw-toggle" data-target="confirm_password"
                                aria-label="Toggle visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <small id="matchLabel" style="display:block;margin-top:0.25rem;"></small>
                </div>

                <div class="form-actions" style="margin-top:1.5rem">
                    <button type="submit" name="change_password" value="1" class="btn-primary">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </form>
            </div>
        </div>

        <!-- ── Security Question ───────────────────────── -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-shield-halved"></i> Security Question</h2>
            </div>
            <div class="admin-card-body">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1.5rem;">
                    Used to verify your identity when you forget your password.
                </p>

            <?php if (!empty($currentUser['security_question'])): ?>
            <div class="current-question-box">
                <div style="color:var(--primary-light);font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.4rem;">
                    <i class="fa-solid fa-circle-question"></i> Current question
                </div>
                <div style="font-size:1.1rem; color:var(--text-strong); font-weight:600; line-height: 1.4; position: relative; z-index: 1;">
                    <?php echo escape($currentUser['security_question']); ?>
                </div>
            </div>
            <?php else: ?>
            <div class="flash flash-info" style="margin-bottom:1.5rem">
                <i class="fa-solid fa-circle-info"></i> No security question set. Please set one to enable password recovery.
            </div>
            <?php endif; ?>

            <form method="POST" class="admin-form" id="sqForm">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label for="security_question">Security Question</label>
                    <select id="security_question" name="security_question" required>
                        <option value="">— Select a question —</option>
                        <?php
                        $questions = [
                            "What was the name of your first pet?",
                            "What city were you born in?",
                            "What is your mother's maiden name?",
                            "What was the name of your first school?",
                            "What is your favourite movie?",
                            "What was your childhood nickname?",
                            "What street did you grow up on?",
                        ];
                        $current = $currentUser['security_question'] ?? '';
                        foreach ($questions as $q): ?>
                        <option value="<?php echo escape($q); ?>"
                            <?php echo ($current === $q) ? 'selected' : ''; ?>>
                            <?php echo escape($q); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="security_answer">Answer</label>
                    <div class="pw-wrap">
                        <input type="password" id="security_answer" name="security_answer"
                               placeholder="Your answer (not case-sensitive)" required>
                        <button type="button" class="pw-toggle" data-target="security_answer"
                                aria-label="Toggle visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_answer">Confirm Answer</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirm_answer" name="confirm_answer"
                               placeholder="Re-enter your answer" required>
                        <button type="button" class="pw-toggle" data-target="confirm_answer"
                                aria-label="Toggle visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <small id="ansMatchLabel" style="display:block;margin-top:.25rem;"></small>
                </div>

                <div class="form-actions" style="margin-top:1.5rem">
                    <button type="submit" name="save_security" value="1" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Security Question
                    </button>
                </div>
            </form>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
// Generic toggle for any .pw-toggle with data-target
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var inp = document.getElementById(this.dataset.target);
        var icn = this.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icn.className = 'fa-regular fa-eye-slash';
        } else {
            inp.type = 'password';
            icn.className = 'fa-regular fa-eye';
        }
    });
});

// Password strength
function checkStrength(val) {
    var fill  = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');
    if (!fill) return;
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
    fill.style.width     = val.length ? lvl.pct : '0';
    fill.style.background = lvl.color;
    label.textContent    = val.length ? lvl.text : '';
    label.style.color    = lvl.color;
}

// Password match
document.getElementById('confirm_password').addEventListener('input', function () {
    var np  = document.getElementById('new_password').value;
    var lbl = document.getElementById('matchLabel');
    if (!this.value) { lbl.textContent = ''; return; }
    if (this.value === np) {
        lbl.textContent = '✓ Passwords match';
        lbl.style.color = 'var(--accent)';
    } else {
        lbl.textContent = '✗ Passwords do not match';
        lbl.style.color = 'var(--danger)';
    }
});

// Answer match
document.getElementById('confirm_answer').addEventListener('input', function () {
    var a   = document.getElementById('security_answer').value;
    var lbl = document.getElementById('ansMatchLabel');
    if (!this.value) { lbl.textContent = ''; return; }
    if (this.value === a) {
        lbl.textContent = '✓ Answers match';
        lbl.style.color = 'var(--accent)';
    } else {
        lbl.textContent = '✗ Answers do not match';
        lbl.style.color = 'var(--danger)';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
