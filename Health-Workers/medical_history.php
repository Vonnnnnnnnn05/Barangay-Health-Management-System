<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_history'])) {
    $pid = (int)$_POST['patient_id'];
    $cond = mysqli_real_escape_string($conn, trim($_POST['condition_name']));
    $ddate = mysqli_real_escape_string($conn, $_POST['diagnosis_date']);
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes']));
    if (mysqli_query($conn, "INSERT INTO medical_history (patient_id,condition_name,diagnosis_date,notes,recorded_by) VALUES ($pid,'$cond','$ddate','$notes',{$_SESSION['user_id']})")) {
        $success = 'Medical history recorded.';
    } else { $error = 'Failed to add record.'; }
}

if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM medical_history WHERE id=".(int)$_GET['delete']);
    $success = 'Record deleted.';
}

$patients = mysqli_query($conn, "SELECT id,first_name,last_name FROM users WHERE role='patient' ORDER BY first_name");
$records = mysqli_query($conn, "SELECT mh.*, CONCAT(u.first_name,' ',u.last_name) as patient_name, CONCAT(r.first_name,' ',r.last_name) as recorded_by_name FROM medical_history mh JOIN users u ON mh.patient_id=u.id LEFT JOIN users r ON mh.recorded_by=r.id ORDER BY mh.created_at DESC");

renderHeader('Medical History');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Medical History</h1><p class="text-sm text-gray-500">Record and manage patient medical history</p></div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition"><i class="fas fa-plus"></i> Add Record</button>
    </div>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-5 py-3 font-medium">#</th><th class="px-5 py-3 font-medium">Patient</th><th class="px-5 py-3 font-medium">Condition</th><th class="px-5 py-3 font-medium">Diagnosis Date</th><th class="px-5 py-3 font-medium">Notes</th><th class="px-5 py-3 font-medium">Recorded By</th><th class="px-5 py-3 font-medium text-center">Actions</th></tr></thead>
            <tbody>
            <?php $n=1; while($r=mysqli_fetch_assoc($records)): ?>
            <tr class="border-t hover:bg-gray-50"><td class="px-5 py-3"><?=$n++?></td><td class="px-5 py-3 font-medium"><?=htmlspecialchars($r['patient_name'])?></td><td class="px-5 py-3"><?=htmlspecialchars($r['condition_name'])?></td><td class="px-5 py-3"><?=$r['diagnosis_date']?date('M d, Y',strtotime($r['diagnosis_date'])):'N/A'?></td><td class="px-5 py-3 text-gray-500 max-w-xs truncate"><?=htmlspecialchars($r['notes']??'')?></td><td class="px-5 py-3"><?=htmlspecialchars($r['recorded_by_name']??'N/A')?></td>
            <td class="px-5 py-3 text-center"><a href="?delete=<?=$r['id']?>" onclick="return confirm('Delete?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded"><i class="fas fa-trash"></i></a></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold"><i class="fas fa-notes-medical text-teal mr-2"></i>Add Medical History</h3><button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button></div>
        <form method="POST">
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Patient</label><select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"><option value="">Select</option><?php mysqli_data_seek($patients,0); while($p=mysqli_fetch_assoc($patients)): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></option><?php endwhile; ?></select></div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Condition</label><input type="text" name="condition_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Diagnosis Date</label><input type="date" name="diagnosis_date" class="w-full px-3 py-2 border rounded-lg text-sm outline-none"></div>
            <div class="mb-4"><label class="block text-xs font-medium text-gray-600 mb-1">Notes</label><textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none resize-none"></textarea></div>
            <button type="submit" name="add_history" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-plus mr-1"></i> Save Record</button>
        </form>
    </div>
</div>
</body>
</html>
