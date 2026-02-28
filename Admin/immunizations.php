<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Add immunization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_imm'])) {
    $pid = (int)$_POST['patient_id'];
    $vaccine = mysqli_real_escape_string($conn, trim($_POST['vaccine_name']));
    $dose = (int)$_POST['dose_number'];
    $sched = mysqli_real_escape_string($conn, $_POST['scheduled_date']);
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes']));
    
    if (mysqli_query($conn, "INSERT INTO immunizations (patient_id,vaccine_name,dose_number,scheduled_date,notes) VALUES ($pid,'$vaccine',$dose,'$sched','$notes')")) {
        $success = 'Immunization schedule added.';
    } else { $error = 'Failed to add.'; }
}

// Update status
if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];
    mysqli_query($conn, "UPDATE immunizations SET status='completed', administered_date=CURDATE(), administered_by={$_SESSION['user_id']} WHERE id=$id");
    $success = 'Marked as completed.';
}
if (isset($_GET['miss'])) {
    $id = (int)$_GET['miss'];
    mysqli_query($conn, "UPDATE immunizations SET status='missed' WHERE id=$id");
    $success = 'Marked as missed.';
}

$patients = mysqli_query($conn, "SELECT id, first_name, last_name FROM users WHERE role='patient' ORDER BY first_name");
$imm = mysqli_query($conn, "SELECT i.*, CONCAT(u.first_name,' ',u.last_name) as patient_name FROM immunizations i JOIN users u ON i.patient_id=u.id ORDER BY i.scheduled_date ASC");

renderHeader('Immunizations');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Immunization Schedules</h1>
            <p class="text-sm text-gray-500">Track and manage immunization records</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Add Schedule
        </button>
    </div>

    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Patient</th>
                        <th class="px-5 py-3 font-medium">Vaccine</th>
                        <th class="px-5 py-3 font-medium">Dose</th>
                        <th class="px-5 py-3 font-medium">Scheduled</th>
                        <th class="px-5 py-3 font-medium">Administered</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($r = mysqli_fetch_assoc($imm)): ?>
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-5 py-3"><?= $n++ ?></td>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($r['patient_name']) ?></td>
                        <td class="px-5 py-3"><?= htmlspecialchars($r['vaccine_name']) ?></td>
                        <td class="px-5 py-3"><?= $r['dose_number'] ?></td>
                        <td class="px-5 py-3"><?= date('M d, Y', strtotime($r['scheduled_date'])) ?></td>
                        <td class="px-5 py-3"><?= $r['administered_date'] ? date('M d, Y', strtotime($r['administered_date'])) : '-' ?></td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= match($r['status']){'completed'=>'bg-green-100 text-green-700','missed'=>'bg-red-100 text-red-700',default=>'bg-yellow-100 text-yellow-700'} ?>"><?= ucfirst($r['status']) ?></span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <?php if ($r['status'] === 'scheduled'): ?>
                            <a href="?complete=<?= $r['id'] ?>" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Complete"><i class="fas fa-check"></i></a>
                            <a href="?miss=<?= $r['id'] ?>" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Missed"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><i class="fas fa-syringe text-teal mr-2"></i>Add Immunization</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Patient</label>
                <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                    <option value="">Select Patient</option>
                    <?php mysqli_data_seek($patients, 0); while ($p = mysqli_fetch_assoc($patients)): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Vaccine Name</label><input type="text" name="vaccine_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Dose Number</label><input type="number" name="dose_number" value="1" min="1" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Scheduled Date</label><input type="date" name="scheduled_date" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            </div>
            <div class="mb-4"><label class="block text-xs font-medium text-gray-600 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none resize-none"></textarea></div>
            <button type="submit" name="add_imm" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-plus mr-1"></i> Add Schedule</button>
        </form>
    </div>
</div>
</body>
</html>
