<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = '';
if (isset($_GET['review'])) {
    $id = (int)$_GET['review'];
    mysqli_query($conn, "UPDATE feedback SET status='reviewed' WHERE id=$id");
    $success = 'Feedback marked as reviewed.';
}

$feedback = mysqli_query($conn, "SELECT f.*, CONCAT(u.first_name,' ',u.last_name) as user_name, u.role 
    FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC");

renderHeader('Feedback');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-comment-dots text-teal mr-2"></i>Feedback</h1>

    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php while ($f = mysqli_fetch_assoc($feedback)): ?>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-teal rounded-full flex items-center justify-center text-white text-xs font-bold">
                        <?= strtoupper(substr($f['user_name'],0,2)) ?>
                    </div>
                    <div>
                        <p class="font-semibold text-sm"><?= htmlspecialchars($f['user_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= ucfirst(str_replace('_',' ',$f['role'])) ?> &middot; <?= date('M d, Y', strtotime($f['created_at'])) ?></p>
                    </div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= $f['status'] === 'reviewed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                    <?= ucfirst($f['status']) ?>
                </span>
            </div>
            <h4 class="font-semibold text-sm mb-1"><?= htmlspecialchars($f['subject']) ?></h4>
            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($f['message']) ?></p>
            <?php if ($f['rating']): ?>
            <div class="flex gap-1 mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star <?= $i <= $f['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> text-sm"></i>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php if ($f['status'] === 'pending'): ?>
            <a href="?review=<?= $f['id'] ?>" class="text-xs text-teal font-medium hover:underline"><i class="fas fa-check mr-1"></i> Mark as Reviewed</a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</main>
</body>
</html>
