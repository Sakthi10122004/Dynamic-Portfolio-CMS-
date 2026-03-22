<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->requireLogin();
$isAdminPage = true;

$db = Database::getInstance();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$note = $id ? getNote($id) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        // Handle image upload
        $imageFilename = $note['image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $result = uploadImage($_FILES['image'], $imageFilename);
            if ($result['success']) {
                $imageFilename = $result['filename'];
            } else {
                $error = $result['error'];
            }
        }

        // Handle image removal
        if (isset($_POST['remove_image']) && $imageFilename) {
            $oldPath = UPLOAD_DIR . $imageFilename;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $imageFilename = null;
        }

        if (!$error) {
            if ($id) {
                $db->query(
                    "UPDATE notes SET title = ?, excerpt = ?, content = ?, image = ?, published = ? WHERE id = ?",
                    [$title, $excerpt, $content, $imageFilename, $published, $id],
                    'ssssii'
                );
            } else {
                $db->insert(
                    "INSERT INTO notes (title, excerpt, content, image, published) VALUES (?, ?, ?, ?, ?)",
                    [$title, $excerpt, $content, $imageFilename, $published],
                    'ssssi'
                );
            }

            header('Location: ' . BASE_URL . '/admin/notes.php?saved=1');
            exit;
        }
    }
}

$pageTitle = $id ? 'Edit Post' : 'New Post';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="margin:0;font-size:1.4rem;font-weight:700">
                    <i class="fa-solid fa-<?php echo $id ? 'pen' : 'plus'; ?>" style="color:#a78bfa"></i>
                    <?php echo $id ? 'Edit Post' : 'New Post'; ?>
                </h1>
                <p style="font-size:.83rem;color:#6e6590;margin-top:.1rem">Draft and publish articles with cover
                    images</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn-glass btn-sm">← Back to Posts</a>
        </div>

        <?php if ($error): ?>
            <div class="flash flash-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <?php echo csrfField(); ?>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fa-solid fa-file-signature"></i> Post Content</h2>
                </div>
                <div class="admin-card-body">

                    <div class="field">
                        <label for="title">
                            <i class="fa-solid fa-heading"></i> Post Title <span style="color:#f43f5e">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                            value="<?php echo escape($note['title'] ?? $_POST['title'] ?? ''); ?>" required
                            placeholder="Enter a catchy title...">
                    </div>

                    <div class="field">
                        <label for="excerpt">
                            <i class="fa-solid fa-align-left"></i> Excerpt / Summary
                        </label>
                        <textarea id="excerpt" name="excerpt" rows="2"
                            placeholder="Brief summary shown on the blog index..."><?php echo escape($note['excerpt'] ?? $_POST['excerpt'] ?? ''); ?></textarea>
                        <p class="form-hint">Shown in the blog card on the homepage.</p>
                    </div>

                    <div class="field">
                        <label for="content">
                            <i class="fa-solid fa-pen-fancy"></i> Full Content <span style="color:#f43f5e">*</span>
                        </label>
                        <textarea id="content" name="content" rows="15" required
                            placeholder="Write your post content here..."><?php echo escape($note['content'] ?? $_POST['content'] ?? ''); ?></textarea>
                        <p class="form-hint">Plain text with line breaks. HTML is escaped for security.</p>
                    </div>
                </div>
            </div>

            <!-- Cover Image Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fa-solid fa-image"></i> Cover Image</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($note['image'])): ?>
                        <div class="current-image-wrap" style="margin-bottom:1rem">
                            <label style="display:block;font-size:.75rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#b0a8c9;margin-bottom:.5rem">
                                <i class="fa-solid fa-image"></i> Current Cover Image
                            </label>
                            <img src="<?php echo UPLOAD_URL . escape($note['image']); ?>"
                                alt="Current cover"
                                style="width:100%;max-width:400px;height:auto;border-radius:12px;border:1px solid rgba(139,92,246,0.15);object-fit:cover">
                            <div style="margin-top:.75rem">
                                <label class="admin-checkbox">
                                    <input type="checkbox" name="remove_image" value="1">
                                    <span class="checkmark"></span>
                                    <span style="font-size:.85rem;color:#b0a8c9">Remove current image</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="field">
                        <label for="image">
                            <i class="fa-solid fa-upload"></i>
                            <?php echo !empty($note['image']) ? 'Replace Image' : 'Upload Cover Image'; ?>
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" data-preview="imagePreview">
                        <p class="form-hint">JPG, PNG, GIF, WebP. Max 5MB. Displayed at the top of the blog post.</p>
                        <img id="imagePreview" src="" alt="Preview"
                            style="display:none;margin-top:.75rem;max-width:400px;width:100%;border-radius:12px;border:1px solid rgba(139,92,246,0.15)">
                    </div>
                </div>
            </div>

            <!-- Publish Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fa-solid fa-gear"></i> Settings</h2>
                </div>
                <div class="admin-card-body">
                    <label class="admin-checkbox">
                        <input type="checkbox" id="published" name="published"
                            <?php echo (($note['published'] ?? false) || isset($_POST['published'])) ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        <span class="checkbox-label">
                            <i class="fa-solid fa-paper-plane" style="font-size:.8rem;color:#a78bfa"></i>
                            Publish this post
                        </span>
                    </label>
                    <p class="form-hint" style="margin-left:2rem">Published posts appear on the public portfolio. Drafts
                        are only visible in admin.</p>

                    <div class="form-actions" style="margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <?php echo $id ? 'Update Post' : 'Save Post'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/notes.php" class="btn-glass">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>