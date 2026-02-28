<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = '';
if (isset($_GET['review'])) {
    $id = (int)$_GET['review'];
    mysqli_query($conn, "UPDATE feedback SET status='reviewed' WHERE id=$id");
    $success = 'Feedback marked as reviewed.';
}

$feedbacks = mysqli_query($conn, "SELECT f.*, CONCAT(u.first_name,' ',u.last_name) as user_name FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC");

renderHeader('Feedback');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Feedback</h1>
    <p class="text-sm text-gray-500 mb-6">View patient feedback</p>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php while($f=mysqli_fetch_assoc($feedbacks)): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 <?=($f['status']==='reviewed')?'opacity-70':''?>">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal flex items-center justify-center text-white font-bold"><?=strtoupper(substr($f['user_name'],0,1))?></div>
                <div><p class="font-semibold text-sm"><?=htmlspecialchars($f['user_name'])?></p><p class="text-xs text-gray-400"><?=date('M d, Y h:i A',strtotime($f['created_at']))?></p></div>
            </div>
            <?php if($f['status']==='reviewed'): ?><span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Reviewed</span><?php endif; ?>
        </div>
        <div class="flex mb-2"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star text-sm <?=$i<=$f['rating']?'text-orange':'text-gray-300'?>"></i><?php endfor; ?></div>
        <p class="text-sm text-gray-700 mb-3"><?=htmlspecialchars($f['message'])?></p>
        <?php if($f['status']!=='reviewed'): ?><a href="?review=<?=$f['id']?>" class="text-xs text-teal hover:underline"><i class="fas fa-check mr-1"></i>Mark as Reviewed</a><?php endif; ?>
    </div>
    <?php endwhile; ?>
    </div>
</main>
</body>
</html>
