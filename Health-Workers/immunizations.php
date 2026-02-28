<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)$_POST['patient_id'];
    $vaccine_name = mysqli_real_escape_string($conn, $_POST['vaccine_name']);
    $scheduled_date = mysqli_real_escape_string($conn, $_POST['scheduled_date']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    mysqli_query($conn, "INSERT INTO immunizations (patient_id, vaccine_name, scheduled_date, administered_by, notes) VALUES ($patient_id, '$vaccine_name', '$scheduled_date', {$_SESSION['user_id']}, '$notes')");
    $success = 'Immunization schedule added.';
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action === 'complete') {
        mysqli_query($conn, "UPDATE immunizations SET status='completed', administered_date=CURDATE() WHERE id=$id");
        $success = 'Marked as completed.';
    } elseif ($action === 'miss') {
        mysqli_query($conn, "UPDATE immunizations SET status='missed' WHERE id=$id");
        $success = 'Marked as missed.';
    }
}

$immunizations = mysqli_query($conn, "SELECT i.*, CONCAT(u.first_name,' ',u.last_name) as patient_name FROM immunizations i JOIN users u ON i.patient_id=u.id ORDER BY i.scheduled_date DESC");
$patients = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',last_name) as name FROM users WHERE role='patient' AND status='active' ORDER BY first_name");

renderHeader('Immunizations');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Immunizations</h1><p class="text-sm text-gray-500">Manage immunization schedules</p></div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition flex items-center gap-2"><i class="fas fa-plus"></i> Add Schedule</button>
    </div>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-5 py-3 font-medium">#</th><th class="px-5 py-3 font-medium">Patient</th><th class="px-5 py-3 font-medium">Vaccine</th><th class="px-5 py-3 font-medium">Scheduled</th><th class="px-5 py-3 font-medium">Administered</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Notes</th><th class="px-5 py-3 font-medium text-center">Actions</th></tr></thead>
            <tbody>
            <?php $n=1; while($i=mysqli_fetch_assoc($immunizations)): ?>
            <tr class="border-t hover:bg-gray-50"><td class="px-5 py-3"><?=$n++?></td><td class="px-5 py-3 font-medium"><?=htmlspecialchars($i['patient_name'])?></td><td class="px-5 py-3"><?=htmlspecialchars($i['vaccine_name'])?></td><td class="px-5 py-3"><?=date('M d, Y',strtotime($i['scheduled_date']))?></td><td class="px-5 py-3"><?=$i['administered_date']?date('M d, Y',strtotime($i['administered_date'])):'-'?></td>
            <td class="px-5 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium <?=match($i['status']){'scheduled'=>'bg-yellow-100 text-yellow-700','completed'=>'bg-green-100 text-green-700','missed'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($i['status'])?></span></td>
            <td class="px-5 py-3 text-gray-500"><?=htmlspecialchars($i['notes'] ?? '')?></td>
            <td class="px-5 py-3 text-center">
                <?php if($i['status']==='scheduled'): ?>
                <a href="?id=<?=$i['id']?>&action=complete" class="p-1 text-green-600 hover:bg-green-50 rounded" title="Complete"><i class="fas fa-check"></i></a>
                <a href="?id=<?=$i['id']?>&action=miss" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Missed"><i class="fas fa-times"></i></a>
                <?php else: ?><span class="text-gray-400 text-xs">—</span><?php endif; ?>
            </td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-syringe text-teal mr-2"></i>Add Immunization Schedule</h2>
        <form method="POST" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Patient *</label><select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"><option value="">Select Patient</option><?php mysqli_data_seek($patients,0); while($p=mysqli_fetch_assoc($patients)): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endwhile; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Vaccine Name *</label><input type="text" name="vaccine_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date *</label><input type="date" name="scheduled_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></textarea></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button><button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">Save</button></div>
        </form>
    </div>
</div>
</body>
</html>
