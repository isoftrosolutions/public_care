<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Blog Posts';
$active_page = 'blog';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param('i', $_POST['delete']);
        $stmt->execute();
        header('Location: blog.php');
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (!$slug) {
        $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($title)));
    }
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $published_at = trim($_POST['published_at'] ?? date('Y-m-d H:i:s'));

    if (!$title) {
        $errors[] = 'Title is required.';
    }

    if (!$errors) {
        if (isset($_POST['edit_id'])) {
            $stmt = $db->prepare("UPDATE blog_posts SET title=?, slug=?, category=?, excerpt=?, content=?, image_url=?, author=?, published_at=? WHERE id=?");
            $stmt->bind_param('ssssssssi', $title, $slug, $category, $excerpt, $content, $image_url, $author, $published_at, $_POST['edit_id']);
        } else {
            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, category, excerpt, content, image_url, author, published_at) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssssss', $title, $slug, $category, $excerpt, $content, $image_url, $author, $published_at);
        }
        $stmt->execute();
        header('Location: blog.php');
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$search = trim($_GET['search'] ?? '');

$where_conds = [];
$bind_params = [];
$bind_types = '';

if ($search) {
    $where_conds[] = "title LIKE ?";
    $bind_params[] = "%$search%";
    $bind_types .= 's';
}
$where_sql = $where_conds ? 'WHERE ' . implode(' AND ', $where_conds) : '';

$total = $db->query("SELECT COUNT(*) FROM blog_posts $where_sql")->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$query = "SELECT * FROM blog_posts $where_sql ORDER BY published_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
if ($bind_params) {
    $bind_params[] = $per_page;
    $bind_params[] = $offset;
    $bind_types .= 'ii';
    $stmt->bind_param($bind_types, ...$bind_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$edit_post = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param('i', $_GET['edit']);
    $stmt->execute();
    $edit_post = $stmt->get_result()->fetch_assoc();
}
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Blog Posts</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Create and manage wellness blog articles.</p>
</div>
<?php if (!$edit_post): ?>
<a href="?add=1" class="bg-primary text-on-primary text-label-sm px-4 py-2 rounded-lg hover:opacity-90 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add</span> New Post</a>
<?php endif; ?>
</header>

<?php if ($edit_post || isset($_GET['add'])): ?>
<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 mb-8">
<h3 class="text-headline-md font-headline-md text-on-surface mb-6"><?= $edit_post ? 'Edit Post' : 'New Post' ?></h3>
<?php if ($errors): ?>
<div class="mb-4 p-3 rounded-lg bg-error-container text-on-error-container text-body-md"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
<?php endif; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<?php if ($edit_post): ?>
<input type="hidden" name="edit_id" value="<?= $edit_post['id'] ?>">
<?php endif; ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Title</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="title" value="<?= htmlspecialchars($edit_post['title'] ?? '') ?>" required>
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Slug</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="slug" placeholder="Auto-generated from title" value="<?= htmlspecialchars($edit_post['slug'] ?? '') ?>">
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Category</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="category" value="<?= htmlspecialchars($edit_post['category'] ?? '') ?>">
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Author</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="author" value="<?= htmlspecialchars($edit_post['author'] ?? '') ?>">
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Image URL</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="image_url" value="<?= htmlspecialchars($edit_post['image_url'] ?? '') ?>">
</div>
<div>
<label class="text-label-sm text-on-surface-variant block mb-1">Published At</label>
<input class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="published_at" type="datetime-local" value="<?= $edit_post ? date('Y-m-d\TH:i', strtotime($edit_post['published_at'])) : date('Y-m-d\TH:i') ?>">
</div>
<div class="md:col-span-2">
<label class="text-label-sm text-on-surface-variant block mb-1">Excerpt</label>
<textarea class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="excerpt" rows="3"><?= htmlspecialchars($edit_post['excerpt'] ?? '') ?></textarea>
</div>
<div class="md:col-span-2">
<label class="text-label-sm text-on-surface-variant block mb-1">Content</label>
<textarea class="w-full bg-surface border border-outline-variant text-body-md rounded-lg px-3 py-2 focus:ring-primary" name="content" rows="12"><?= htmlspecialchars($edit_post['content'] ?? '') ?></textarea>
</div>
</div>
<div class="flex gap-3 mt-6">
<button class="bg-primary text-on-primary text-label-sm px-6 py-2 rounded-lg hover:opacity-90" type="submit"><?= $edit_post ? 'Update' : 'Save' ?></button>
<a href="blog.php" class="bg-surface-container-high text-on-surface text-label-sm px-6 py-2 rounded-lg hover:bg-surface-container-highest transition-colors">Cancel</a>
</div>
</form>
</section>
<?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden">
<div class="px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
<form method="GET" class="flex gap-4 flex-wrap items-center">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2 w-56" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>" type="text"/>
</div>
<button class="text-label-sm text-primary hover:underline" type="submit">Search</button>
<?php if ($search): ?>
<a href="blog.php" class="text-label-sm text-on-surface-variant hover:underline">Clear</a>
<?php endif; ?>
</form>
</div>
<table class="w-full text-left">
<thead>
<tr class="bg-surface-container-low text-on-surface-variant">
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Title</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Category</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Author</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Published</th>
<th class="px-6 py-4 text-label-sm uppercase tracking-wider">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<?php foreach ($posts as $p): ?>
<tr>
<td class="px-6 py-4 text-body-md font-bold text-on-surface"><?= htmlspecialchars($p['title']) ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= htmlspecialchars($p['author'] ?? '—') ?></td>
<td class="px-6 py-4 text-body-md text-on-surface-variant"><?= date('M d, Y', strtotime($p['published_at'])) ?></td>
<td class="px-6 py-4">
<div class="flex items-center gap-2">
<a href="?edit=<?= $p['id'] ?>" class="text-label-sm bg-surface-container-high text-on-surface px-3 py-1 rounded hover:bg-surface-container-highest transition-colors">Edit</a>
<form method="POST" onsubmit="return confirm('Delete this post?')">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<button class="text-label-sm bg-error-container text-on-error-container px-3 py-1 rounded hover:opacity-80" type="submit" name="delete" value="<?= $p['id'] ?>">Delete</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($posts)): ?>
<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No blog posts found.</td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-label-sm text-on-surface-variant">
<span>Showing <?= count($posts) ?> of <?= number_format($total) ?></span>
<div class="flex gap-2">
<?php if ($page > 1): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a><?php endif; ?>
<span class="px-3 py-1 rounded bg-primary text-on-primary"><?= $page ?></span>
<?php if ($page < $total_pages): ?><a class="px-3 py-1 rounded bg-surface-container-high hover:bg-surface-container-highest transition-colors" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a><?php endif; ?>
</div>
</div>
</section>
</main>
</body>
</html>
