<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$profile = getProfile();
$success = false;
$error = '';

// Auto-migrate missing columns for users upgrading from v1
try {
    $db->query("ALTER TABLE profile ADD COLUMN github VARCHAR(255) DEFAULT NULL;");
} catch (Exception $e) {
}
try {
    $db->query("ALTER TABLE profile ADD COLUMN linkedin VARCHAR(255) DEFAULT NULL;");
} catch (Exception $e) {
}
try {
    $db->query("ALTER TABLE profile ADD COLUMN twitter VARCHAR(255) DEFAULT NULL;");
} catch (Exception $e) {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');

    if (empty($name) || empty($headline) || empty($email)) {
        $error = 'Name, headline, and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $avatarFile = $profile['avatar'] ?? null;
        $resumeFile = $profile['resume'] ?? null;

        // Handle avatar upload
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $result = uploadImage($_FILES['avatar'], $avatarFile);
            if ($result['success']) {
                $avatarFile = $result['filename'];
            } else {
                $error = 'Avatar: ' . $result['error'];
            }
        }

        // Handle avatar removal — only if no new avatar was just uploaded
        if (isset($_POST['remove_avatar']) && $avatarFile && $avatarFile === ($profile['avatar'] ?? null)) {
            if (file_exists(UPLOAD_DIR . $avatarFile)) {
                unlink(UPLOAD_DIR . $avatarFile);
            }
            $avatarFile = null;
        }

        // Handle resume upload
        if (!empty($_FILES['resume']['name']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['resume'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedResume = ['pdf', 'doc', 'docx'];

            if (!in_array($ext, $allowedResume)) {
                $error = 'Resume must be a PDF, DOC, or DOCX file';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Resume file too large (max 10MB)';
            } else {
                // Delete old resume
                if ($resumeFile && file_exists(UPLOAD_DIR . $resumeFile)) {
                    unlink(UPLOAD_DIR . $resumeFile);
                }
                $resumeFilename = 'resume_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (!is_dir(UPLOAD_DIR))
                    mkdir(UPLOAD_DIR, 0755, true);
                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $resumeFilename)) {
                    $resumeFile = $resumeFilename;
                } else {
                    $error = 'Failed to upload resume';
                }
            }
        }

        // Handle resume removal
        if (isset($_POST['remove_resume']) && $resumeFile) {
            if (file_exists(UPLOAD_DIR . $resumeFile)) {
                unlink(UPLOAD_DIR . $resumeFile);
            }
            $resumeFile = null;
        }

        if (!$error) {
            $existing = $db->getRow("SELECT id FROM profile WHERE id = 1");

            $querySuccess = false;
            if ($existing) {
                $stmt = $db->query(
                    "UPDATE profile SET name=?, headline=?, bio=?, email=?, avatar=?, resume=?, github=?, linkedin=?, twitter=? WHERE id=1",
                    [$name, $headline, $bio, $email, $avatarFile, $resumeFile, $github, $linkedin, $twitter],
                    'sssssssss'
                );
                $querySuccess = $stmt !== false;
            } else {
                $stmt = $db->query(
                    "INSERT INTO profile (id, name, headline, bio, email, avatar, resume, github, linkedin, twitter) VALUES (1,?,?,?,?,?,?,?,?,?)",
                    [$name, $headline, $bio, $email, $avatarFile, $resumeFile, $github, $linkedin, $twitter],
                    'sssssssss'
                );
                $querySuccess = $stmt !== false;
            }

            if ($querySuccess) {
                $success = true;
                $profile = getProfile();
            } else {
                $error = 'Failed to save profile settings. Error: ' . $db->getConnection()->error;
            }
        }
    }
}

$pageTitle = 'Edit Profile';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700;color:var(--text-strong)">
                    <i class="fa-solid fa-id-card" style="color:var(--accent)"></i> Profile
                </h1>
                <p style="font-size:.83rem;color:var(--text-muted);margin-top:.1rem">Manage your personal information,
                    bio, and resume</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn-glass btn-sm">View Public Profile</a>
        </div>

        <?php if ($success): ?>
            <div class="flash flash-success">
                <i class="fa-solid fa-circle-check"></i> Profile updated successfully
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="flash flash-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2><i class="fa-solid fa-user-gear"></i> Account Information</h2>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <?php echo csrfField(); ?>

                    <!-- Avatar Section -->
                    <div class="field">
                        <label><i class="fa-solid fa-camera"></i> Profile Photo</label>
                        <div style="display:flex; align-items:center; gap:2rem; margin-bottom:1rem">
                            <div
                                style="width:100px; height:100px; border-radius:16px; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); position:relative; overflow:hidden">
                                <?php if (!empty($profile['avatar'])): ?>
                                    <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>"
                                        id="avatar-img-preview" alt="Avatar"
                                        style="width:100%; height:100%; object-fit:cover">
                                <?php else: ?>
                                    <div
                                        style="display:flex; align-items:center; justify-content:center; height:100%; font-size:2rem; color:var(--text-muted)">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <img id="avatar-img-preview"
                                        style="display:none; width:100%; height:100%; object-fit:cover">
                                <?php endif; ?>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:0.5rem">
                                <label class="btn-glass btn-sm" for="avatar"
                                    style="cursor:pointer; margin-bottom:0">Change Photo</label>
                                <?php if (!empty($profile['avatar'])): ?>
                                    <button type="submit" name="remove_avatar" value="1" class="btn-icon danger"
                                        style="width:auto; padding:0 0.8rem; height:32px; font-size:0.75rem"
                                        onclick="return confirm('Remove profile photo?')">
                                        <i class="fa-solid fa-trash" style="margin-right:0.3rem"></i> Remove
                                    </button>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="avatar" name="avatar" accept="image/*" hidden
                                data-preview="avatar-img-preview">
                        </div>
                        <p class="form-hint">JPG, PNG, GIF, or WebP. Max 5MB.</p>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo escape($profile['name']); ?>"
                                required>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo escape($profile['email']); ?>"
                                required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="headline">Headline</label>
                        <input type="text" id="headline" name="headline"
                            value="<?php echo escape($profile['headline']); ?>" required>
                        <p class="form-hint">e.g. "Full-Stack Developer" — shown below your name on the homepage</p>
                    </div>

                    <div class="field">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4"><?php echo escape($profile['bio']); ?></textarea>
                    </div>

                    <!-- Resume Section -->
                    <div class="field">
                        <label><i class="fa-solid fa-file-pdf"></i> Resume / CV</label>
                        <div
                            style="background:rgba(255,255,255,0.03); border:1px solid var(--glass-border); border-radius:12px; padding:1.25rem; margin-bottom:1rem">
                            <?php if (!empty($profile['resume'])): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center">
                                    <div style="display:flex; align-items:center; gap:0.8rem">
                                        <i class="fa-solid fa-file-invoice"
                                            style="font-size:1.5rem; color:var(--accent)"></i>
                                        <div>
                                            <div style="font-size:0.85rem; font-weight:600; color:var(--text-strong)">
                                                <?php echo escape($profile['resume']); ?>
                                            </div>
                                            <div style="font-size:0.75rem; color:var(--text-muted)">Click to download or
                                                replace below</div>
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:0.5rem">
                                        <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>" class="btn-icon"
                                            download title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="submit" name="remove_resume" value="1" class="btn-icon danger"
                                            onclick="return confirm('Remove resume?')" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div style="height:1px; background:var(--glass-border); margin:1.25rem 0"></div>
                            <?php endif; ?>
                            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" class="btn-glass"
                                style="width:auto">
                            <p class="form-hint" style="margin-top:0.5rem">PDF, DOC, or DOCX. Max 10MB.</p>
                        </div>
                    </div>

                    <div style="margin:2rem 0; height:1px; background:var(--glass-border)"></div>

                    <h3 style="font-size:1rem; font-weight:700; color:var(--text-strong); margin-bottom:1.5rem">
                        <i class="fa-solid fa-share-nodes" style="color:var(--accent); margin-right:0.5rem"></i> Social
                        Links
                    </h3>

                    <div class="form-row">
                        <div class="field">
                            <label for="github">GitHub URL</label>
                            <input type="url" id="github" name="github" placeholder="https://github.com/username"
                                value="<?php echo escape($profile['github'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="linkedin">LinkedIn URL</label>
                            <input type="url" id="linkedin" name="linkedin"
                                placeholder="https://linkedin.com/in/username"
                                value="<?php echo escape($profile['linkedin'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label for="twitter">Twitter / X URL</label>
                        <input type="url" id="twitter" name="twitter" placeholder="https://twitter.com/username"
                            value="<?php echo escape($profile['twitter'] ?? ''); ?>">
                    </div>

                    <div class="form-actions" style="margin-top:1.5rem">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Profile Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Live avatar preview
    document.getElementById('avatar')?.addEventListener('change', function () {
        if (!this.files || !this.files[0]) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            let img = document.getElementById('avatar-img-preview');
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            // Hide placeholder icon if present
            const placeholder = img?.parentElement?.querySelector('div');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>

<?php require_once '../includes/footer.php'; ?>