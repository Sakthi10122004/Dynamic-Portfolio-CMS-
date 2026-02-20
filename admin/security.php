<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

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
        <div class="admin-header">
            <h1>Security Settings</h1>
        </div>

        <?php if ($success): ?>
        <div class="success-message"><?php echo escape($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <!-- ── Change Password ──────────────────────────── -->
        <div class="bento-card visible" style="margin-bottom:1.5rem;">
            <h2 style="font-size:1.1rem;margin-bottom:1.25rem;color:var(--plasma-cyan);">
                <i class="fa-solid fa-lock" style="margin-right:0.5rem;"></i>Change Password
            </h2>
            <form method="POST" class="edit-form" id="pwForm">
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

                <div class="form-actions">
                    <button type="submit" name="change_password" value="1" class="btn primary">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Security Question ───────────────────────── -->
        <div class="bento-card visible">
            <h2 style="font-size:1.1rem;margin-bottom:0.4rem;color:var(--plasma-cyan);">
                <i class="fa-solid fa-shield-halved" style="margin-right:0.5rem;"></i>Security Question
            </h2>
            <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1.25rem;">
                Used to verify your identity when you forget your password.
            </p>

            <?php if (!empty($currentUser['security_question'])): ?>
            <div class="security-question-display" style="margin-bottom:1.25rem;">
                <span style="color:var(--text-muted);font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Current question</span><br>
                <?php echo escape($currentUser['security_question']); ?>
            </div>
            <?php else: ?>
            <div style="background:rgba(255,171,64,.06);border:1px solid rgba(255,171,64,.18);border-radius:var(--r-sm);padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:var(--warning);">
                ⚠️ No security question set. Without one, you cannot reset your password if you forget it.
            </div>
            <?php endif; ?>

            <form method="POST" class="edit-form" id="sqForm">
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

                <div class="form-actions">
                    <button type="submit" name="save_security" value="1" class="btn primary">
                        Save Security Question
                    </button>
                </div>
            </form>
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
        lbl.style.color = 'var(--plasma-green)';
    } else {
        lbl.textContent = '✗ Passwords do not match';
        lbl.style.color = 'var(--error)';
    }
});

// Answer match
document.getElementById('confirm_answer').addEventListener('input', function () {
    var a   = document.getElementById('security_answer').value;
    var lbl = document.getElementById('ansMatchLabel');
    if (!this.value) { lbl.textContent = ''; return; }
    if (this.value === a) {
        lbl.textContent = '✓ Answers match';
        lbl.style.color = 'var(--plasma-green)';
    } else {
        lbl.textContent = '✗ Answers do not match';
        lbl.style.color = 'var(--error)';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
