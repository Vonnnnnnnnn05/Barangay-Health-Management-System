<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = '';
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    mysqli_query($conn, "UPDATE appointments SET status='$status', health_worker_id={$_SESSION['user_id']} WHERE id=$id");
    $success = 'Appointment updated.';
}

$filter = isset($_GET['filter_status']) ? mysqli_real_escape_string($conn, $_GET['filter_status']) : '';
$where = "WHERE 1=1";
if ($filter) $where .= " AND a.status='$filter'";

$appointments = mysqli_query($conn, "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name FROM appointments a JOIN users u ON a.patient_id=u.id $where ORDER BY a.appointment_date DESC");

renderHeader('Appointments');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Appointments</h1><p class="text-sm text-gray-500">Manage patient appointments</p></div>
    </div>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <select name="filter_status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none">
                <option value="">All</option><option value="pending" <?=$filter==='pending'?'selected':''?>>Pending</option><option value="confirmed" <?=$filter==='confirmed'?'selected':''?>>Confirmed</option><option value="completed" <?=$filter==='completed'?'selected':''?>>Completed</option><option value="cancelled" <?=$filter==='cancelled'?'selected':''?>>Cancelled</option>
            </select>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-filter mr-1"></i> Filter</button>
            <a href="appointments.php" class="text-sm text-gray-500">Clear</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-5 py-3 font-medium">#</th><th class="px-5 py-3 font-medium">Patient</th><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Time</th><th class="px-5 py-3 font-medium">Purpose</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium text-center">Actions</th></tr></thead>
            <tbody>
            <?php $n=1; while($a=mysqli_fetch_assoc($appointments)): ?>
            <tr class="border-t hover:bg-gray-50"><td class="px-5 py-3"><?=$n++?></td><td class="px-5 py-3 font-medium"><?=htmlspecialchars($a['patient_name'])?></td><td class="px-5 py-3"><?=date('M d, Y',strtotime($a['appointment_date']))?></td><td class="px-5 py-3"><?=date('h:i A',strtotime($a['appointment_time']))?></td><td class="px-5 py-3"><?=htmlspecialchars($a['purpose'])?></td>
            <td class="px-5 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium <?=match($a['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($a['status'])?></span></td>
            <td class="px-5 py-3 text-center">
                <?php if($a['status']==='pending'): ?><a href="?id=<?=$a['id']?>&status=confirmed" class="p-1 text-blue-600 hover:bg-blue-50 rounded"><i class="fas fa-check"></i></a><?php endif; ?>
                <?php if($a['status']==='confirmed'): ?><a href="?id=<?=$a['id']?>&status=completed" class="p-1 text-green-600 hover:bg-green-50 rounded"><i class="fas fa-check-double"></i></a><?php endif; ?>
                <?php if($a['status']!=='cancelled'&&$a['status']!=='completed'): ?><a href="?id=<?=$a['id']?>&status=cancelled" class="p-1 text-red-600 hover:bg-red-50 rounded"><i class="fas fa-times"></i></a><?php endif; ?>
                <a href="print_slip.php?id=<?=$a['id']?>" target="_blank" class="p-1 text-teal hover:bg-teal-50 rounded"><i class="fas fa-print"></i></a>
            </td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
