<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    if ($rating >= 1 && $rating <= 5 && $message) {
        mysqli_query($conn, "INSERT INTO feedback (user_id, subject, rating, message) VALUES ($uid, 'General Feedback', $rating, '$message')");
        $success = 'Thank you for your feedback!';
    }
}

$my_feedbacks = mysqli_query($conn, "SELECT * FROM feedback WHERE user_id=$uid ORDER BY created_at DESC");

renderHeader('Send Feedback');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Send Feedback</h1>
    <p class="text-sm text-gray-500 mb-6">Share your experience with us</p>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-star text-orange"></i> New Feedback</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                    <div class="flex gap-2" id="starRating">
                        <?php for($s=1;$s<=5;$s++): ?>
                        <button type="button" onclick="setRating(<?=$s?>)" class="text-3xl text-gray-300 hover:text-orange transition star-btn" data-star="<?=$s?>"><i class="fas fa-star"></i></button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="0" required>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Your Message *</label><textarea name="message" rows="5" required placeholder="Tell us about your experience..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></textarea></div>
                <button type="submit" class="bg-teal text-white px-6 py-2 rounded-lg text-sm hover:bg-teal-700 transition w-full"><i class="fas fa-paper-plane mr-2"></i>Submit Feedback</button>
            </form>
        </div>

        <div>
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-history text-teal"></i> My Previous Feedback</h3>
            <div class="space-y-3">
            <?php while($f=mysqli_fetch_assoc($my_feedbacks)): ?>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star text-sm <?=$i<=$f['rating']?'text-orange':'text-gray-300'?>"></i><?php endfor; ?></div>
                    <div class="flex items-center gap-2">
                        <?php if($f['status']==='reviewed'): ?><span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Reviewed</span><?php else: ?><span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full">Pending</span><?php endif; ?>
                        <span class="text-xs text-gray-400"><?=date('M d, Y',strtotime($f['created_at']))?></span>
                    </div>
                </div>
                <p class="text-sm text-gray-700"><?=htmlspecialchars($f['message'])?></p>
            </div>
            <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>
<script>
function setRating(r) {
    document.getElementById('ratingInput').value = r;
    document.querySelectorAll('.star-btn').forEach(b => {
        b.classList.toggle('text-orange', b.dataset.star <= r);
        b.classList.toggle('text-gray-300', b.dataset.star > r);
    });
}
</script>
</body>
</html>
