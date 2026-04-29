<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$auth = new Auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$note = getPublishedNote($id);

if (!$note) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Article Not Found';
    require_once 'includes/header.php';
    echo '<div class="project-detail" style="text-align:center;padding:8rem 2rem;">
        <div class="glass-card" style="max-width:500px;margin:0 auto;padding:3rem 2rem;">
            <i class="fa-solid fa-file-lines" style="font-size:2.5rem;color:var(--ink3);opacity:.3;display:block;margin-bottom:1rem;"></i>
            <h1 style="font-family:var(--font-head);font-size:1.5rem;margin-bottom:.75rem;">Article Not Found</h1>
            <p style="color:var(--ink2);margin-bottom:1.5rem;">This article doesn\'t exist or isn\'t published yet.</p>
            <a href="' . BASE_URL . '/#blog" class="btn-primary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Articles
            </a>
        </div>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = escape($note['title']);
$pageDescription = escape(truncate($note['excerpt'] ?? $note['content'] ?? '', 160));

require_once 'includes/header.php';
?>

<article class="project-detail">
    <!-- Cover Image -->
    <?php if (!empty($note['image'])): ?>
        <div class="glass-card" style="padding:0;overflow:hidden;margin-bottom:1.5rem;border-radius:20px;">
            <img src="<?php echo UPLOAD_URL . escape($note['image']); ?>"
                 alt="<?php echo escape($note['title']); ?>"
                 style="width:100%;max-height:450px;object-fit:cover;display:block">
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="glass-card" style="padding:2rem;margin-bottom:1.5rem;">
        <a href="<?php echo BASE_URL; ?>/#blog" class="back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Articles
        </a>
        <h1><?php echo escape($note['title']); ?></h1>
        <time datetime="<?php echo $note['created_at']; ?>" style="font-size:.8rem;color:var(--ink3);display:inline-flex;align-items:center;gap:.4rem;">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <?php echo formatDate($note['created_at']); ?>
        </time>
        <?php if (!empty($note['excerpt'])): ?>
            <p style="font-size:.95rem;color:var(--ink2);margin-top:1rem;line-height:1.7;font-style:italic;border-left:3px solid rgba(139,92,246,0.3);padding-left:1rem;">
                <?php echo escape($note['excerpt']); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="glass-card" style="padding:2rem;">
        <div style="font-size:1rem;line-height:1.85;color:var(--ink2);">
            <?php echo nl2br(escape($note['content'])); ?>
        </div>
    </div>

    <!-- Share & Comments -->
    <div style="margin-top:3rem; border-top: 1px solid var(--glass-border); padding-top: 2.5rem; display: flex; flex-direction: column; gap: 3rem;">
        
        <!-- Share Post -->
        <div class="share-post glass-card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; padding: 1.5rem 2rem;">
            <h3 style="font-family: var(--font-head); font-size: 1.15rem; margin: 0; color: var(--ink);">Share this article</h3>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <?php
                $shareUrl = urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                $shareTitle = urlencode($note['title']);
                ?>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $shareUrl; ?>&text=<?php echo $shareTitle; ?>" target="_blank" rel="noopener noreferrer" class="btn-glass" aria-label="Share on Twitter" style="padding: 0.6rem 1rem;">
                    <i class="fa-brands fa-twitter" style="color: #1DA1F2;"></i> Twitter
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $shareUrl; ?>&title=<?php echo $shareTitle; ?>" target="_blank" rel="noopener noreferrer" class="btn-glass" aria-label="Share on LinkedIn" style="padding: 0.6rem 1rem;">
                    <i class="fa-brands fa-linkedin-in" style="color: #0A66C2;"></i> LinkedIn
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>" target="_blank" rel="noopener noreferrer" class="btn-glass" aria-label="Share on Facebook" style="padding: 0.6rem 1rem;">
                    <i class="fa-brands fa-facebook-f" style="color: #1877F2;"></i> Facebook
                </a>
                <button onclick="navigator.clipboard.writeText('<?php echo urldecode($shareUrl); ?>'); alert('Link copied to clipboard!');" class="btn-glass" aria-label="Copy Link" style="padding: 0.6rem 1rem; border: 1px solid var(--glass-border); background: transparent; color: var(--ink); cursor: pointer;">
                    <i class="fa-solid fa-link" style="color: var(--secondary);"></i> Copy
                </button>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section glass-card" style="padding: 2.5rem; border-radius: var(--radius);">
            <h3 style="font-family: var(--font-head); font-size: 1.4rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.6rem; color: var(--ink);">
                <i class="fa-regular fa-comments" style="color: var(--primary-light);"></i> Discussion
            </h3>
            
            <!-- Comment Form -->
            <form class="comment-form" onsubmit="event.preventDefault(); alert('Comment submitted successfully! (Frontend demo)'); this.reset();" style="margin-bottom: 3rem;">
                <div class="form-row">
                    <div class="field">
                        <label for="comment-name">Name</label>
                        <input type="text" id="comment-name" required placeholder="John Doe">
                    </div>
                    <div class="field">
                        <label for="comment-email">Email</label>
                        <input type="email" id="comment-email" required placeholder="john@example.com">
                    </div>
                </div>
                <div class="field">
                    <label for="comment-message">Message</label>
                    <textarea id="comment-message" rows="4" required placeholder="Share your thoughts..."></textarea>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Post Comment
                </button>
            </form>

            <!-- Comments List -->
            <div class="comments-list">
                <div style="text-align: center; padding: 2rem 0;">
                    <i class="fa-regular fa-comment-dots" style="font-size: 2.5rem; color: var(--ink3); opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                    <p style="color: var(--ink3); font-size: 0.95rem; margin: 0;">No comments yet. Be the first to start the discussion!</p>
                </div>
            </div>
        </div>
    </div>
</article>

<?php require_once 'includes/footer.php'; ?>
