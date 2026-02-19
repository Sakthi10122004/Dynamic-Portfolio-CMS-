<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();

$db = Database::getInstance();
$profile = getProfile();
$success = false;
$error = '';

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
        
        // Handle avatar removal
        if (isset($_POST['remove_avatar']) && $avatarFile) {
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
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
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
            
            if ($existing) {
                $db->query(
                    "UPDATE profile SET name=?, headline=?, bio=?, email=?, avatar=?, resume=?, github=?, linkedin=?, twitter=? WHERE id=1",
                    [$name, $headline, $bio, $email, $avatarFile, $resumeFile, $github, $linkedin, $twitter],
                    'sssssssss'
                );
            } else {
                $db->query(
                    "INSERT INTO profile (id, name, headline, bio, email, avatar, resume, github, linkedin, twitter) VALUES (1,?,?,?,?,?,?,?,?,?)",
                    [$name, $headline, $bio, $email, $avatarFile, $resumeFile, $github, $linkedin, $twitter],
                    'sssssssss'
                );
            }
            
            $success = true;
            $profile = getProfile();
        }
    }
}

$pageTitle = 'Edit Profile';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Profile</h1>
        </div>
        
        <?php if ($success): ?>
        <div class="success-message">Profile updated successfully</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-message"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="edit-form bento-card">
            <?php echo csrfField(); ?>
            
            <!-- Avatar Section -->
            <div class="form-group">
                <label>Profile Photo</label>
                <div class="avatar-upload-area">
                    <?php if (!empty($profile['avatar'])): ?>
                    <div class="avatar-preview">
                        <img src="<?php echo UPLOAD_URL . escape($profile['avatar']); ?>" alt="Profile Photo" class="avatar-img-preview">
                        <div class="avatar-actions">
                            <label class="btn secondary btn-sm" for="avatar">Change Photo</label>
                            <button type="submit" name="remove_avatar" value="1" class="btn danger btn-sm" onclick="return confirm('Remove profile photo?')">Remove</button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="avatar-placeholder">
                        <div class="avatar-placeholder-icon">👤</div>
                        <p>No profile photo</p>
                        <label class="btn secondary btn-sm" for="avatar">Upload Photo</label>
                    </div>
                    <?php endif; ?>
                    <input type="file" id="avatar" name="avatar" accept="image/*" hidden>
                </div>
                <small>JPG, PNG, GIF, or WebP. Max 5MB.</small>
            </div>
            
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo escape($profile['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="headline">Headline</label>
                <input type="text" id="headline" name="headline" 
                       value="<?php echo escape($profile['headline']); ?>" required>
                <small>e.g. "Full-Stack Developer" — shown below your name on the homepage</small>
            </div>
            
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo escape($profile['bio']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo escape($profile['email']); ?>" required>
            </div>
            
            <!-- Resume Section -->
            <div class="form-group">
                <label>Resume / CV</label>
                <?php if (!empty($profile['resume'])): ?>
                <div class="resume-info">
                    <span class="resume-file">📎 <?php echo escape($profile['resume']); ?></span>
                    <div class="resume-actions">
                        <a href="<?php echo UPLOAD_URL . escape($profile['resume']); ?>" class="btn secondary btn-sm" download>Download</a>
                        <button type="submit" name="remove_resume" value="1" class="btn danger btn-sm" onclick="return confirm('Remove resume?')">Remove</button>
                    </div>
                </div>
                <?php endif; ?>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                <small>PDF, DOC, or DOCX. Max 10MB.</small>
            </div>
            
            <hr class="form-divider">
            
            <h3 class="form-section-title">Social Links</h3>
            
            <div class="form-group">
                <label for="github">GitHub URL</label>
                <input type="url" id="github" name="github" placeholder="https://github.com/username"
                       value="<?php echo escape($profile['github'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="linkedin">LinkedIn URL</label>
                <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/username"
                       value="<?php echo escape($profile['linkedin'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="twitter">Twitter / X URL</label>
                <input type="url" id="twitter" name="twitter" placeholder="https://twitter.com/username"
                       value="<?php echo escape($profile['twitter'] ?? ''); ?>">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">Save Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
// Live avatar preview — MUST NOT destroy the file input
document.getElementById('avatar')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Find or create a preview container
            let preview = document.getElementById('avatarPreviewContainer');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'avatarPreviewContainer';
                preview.className = 'avatar-preview';
                document.querySelector('.avatar-upload-area').prepend(preview);
            }
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="avatar-img-preview">
                <p style="color: var(--success); font-size: 0.85rem; margin-top: 0.5rem;">New photo selected — click Save to apply</p>
            `;
            // Hide the placeholder if it exists
            const placeholder = document.querySelector('.avatar-placeholder');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
