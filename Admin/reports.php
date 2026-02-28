<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$report_type = isset($_GET['type']) ? $_GET['type'] : '';
$data = null;

if ($report_type === 'appointments') {
    $data = mysqli_query($conn, "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name FROM appointments a JOIN users u ON a.patient_id=u.id ORDER BY a.appointment_date DESC");
} elseif ($report_type === 'patients') {
    $data = mysqli_query($conn, "SELECT * FROM users WHERE role='patient' ORDER BY first_name ASC");
} elseif ($report_type === 'medical' && isset($_GET['pid'])) {
    $pid = (int)$_GET['pid'];
    $patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$pid"));
    $med_hist = mysqli_query($conn, "SELECT * FROM medical_history WHERE patient_id=$pid ORDER BY created_at DESC");
    $treats = mysqli_query($conn, "SELECT * FROM treatments WHERE patient_id=$pid ORDER BY treatment_date DESC");
} elseif ($report_type === 'inventory') {
    $data = mysqli_query($conn, "SELECT * FROM medicines ORDER BY name ASC");
}

$all_patients = mysqli_query($conn, "SELECT id, first_name, last_name FROM users WHERE role='patient' ORDER BY first_name");

renderHeader('Reports');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Generate Reports</h1>
            <p class="text-sm text-gray-500">Select a report type to generate</p>
        </div>
        <?php if ($report_type): ?>
        <button onclick="window.print()" class="bg-orange hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition no-print">
            <i class="fas fa-print"></i> Print Report
        </button>
        <?php endif; ?>
    </div>

    <!-- Report Selection -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 no-print">
        <a href="?type=appointments" class="bg-white rounded-xl shadow-sm p-5 border-l-4 <?= $report_type === 'appointments' ? 'border-orange' : 'border-teal' ?> hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center"><i class="fas fa-calendar-check text-teal"></i></div>
                <div><p class="font-semibold text-sm">List of Appointments</p><p class="text-xs text-gray-500">All appointment records</p></div>
            </div>
        </a>
        <a href="?type=patients" class="bg-white rounded-xl shadow-sm p-5 border-l-4 <?= $report_type === 'patients' ? 'border-orange' : 'border-teal' ?> hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center"><i class="fas fa-users text-teal"></i></div>
                <div><p class="font-semibold text-sm">List of Patients</p><p class="text-xs text-gray-500">All registered patients</p></div>
            </div>
        </a>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 <?= $report_type === 'medical' ? 'border-orange' : 'border-teal' ?>">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center"><i class="fas fa-notes-medical text-teal"></i></div>
                <div><p class="font-semibold text-sm">Medical Record</p><p class="text-xs text-gray-500">Per patient</p></div>
            </div>
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="type" value="medical">
                <select name="pid" required class="flex-1 px-2 py-1 border rounded text-xs outline-none">
                    <option value="">Select patient</option>
                    <?php while ($p = mysqli_fetch_assoc($all_patients)): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="bg-teal text-white px-2 py-1 rounded text-xs"><i class="fas fa-arrow-right"></i></button>
            </form>
        </div>
        <a href="?type=inventory" class="bg-white rounded-xl shadow-sm p-5 border-l-4 <?= $report_type === 'inventory' ? 'border-orange' : 'border-teal' ?> hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center"><i class="fas fa-pills text-teal"></i></div>
                <div><p class="font-semibold text-sm">Medicine Inventory</p><p class="text-xs text-gray-500">Stock overview</p></div>
            </div>
        </a>
    </div>

    <!-- Report Content -->
    <?php if ($report_type === 'appointments' && $data): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-1">List of Appointments</h3>
        <p class="text-xs text-gray-500 mb-4">Generated on <?= date('F d, Y h:i A') ?></p>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-4 py-2 font-medium">#</th><th class="px-4 py-2 font-medium">Patient</th><th class="px-4 py-2 font-medium">Date</th><th class="px-4 py-2 font-medium">Time</th><th class="px-4 py-2 font-medium">Purpose</th><th class="px-4 py-2 font-medium">Status</th></tr></thead>
            <tbody>
            <?php $n=1; while($r=mysqli_fetch_assoc($data)): ?>
            <tr class="border-t"><td class="px-4 py-2"><?=$n++?></td><td class="px-4 py-2"><?=htmlspecialchars($r['patient_name'])?></td><td class="px-4 py-2"><?=date('M d, Y',strtotime($r['appointment_date']))?></td><td class="px-4 py-2"><?=date('h:i A',strtotime($r['appointment_time']))?></td><td class="px-4 py-2"><?=htmlspecialchars($r['purpose'])?></td><td class="px-4 py-2"><?=ucfirst($r['status'])?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($report_type === 'patients' && $data): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-1">List of Patients</h3>
        <p class="text-xs text-gray-500 mb-4">Generated on <?= date('F d, Y h:i A') ?></p>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-4 py-2 font-medium">#</th><th class="px-4 py-2 font-medium">Name</th><th class="px-4 py-2 font-medium">Email</th><th class="px-4 py-2 font-medium">Phone</th><th class="px-4 py-2 font-medium">Gender</th><th class="px-4 py-2 font-medium">Registered</th></tr></thead>
            <tbody>
            <?php $n=1; while($r=mysqli_fetch_assoc($data)): ?>
            <tr class="border-t"><td class="px-4 py-2"><?=$n++?></td><td class="px-4 py-2"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td class="px-4 py-2"><?=htmlspecialchars($r['email'])?></td><td class="px-4 py-2"><?=htmlspecialchars($r['phone']??'N/A')?></td><td class="px-4 py-2"><?=htmlspecialchars($r['gender']??'N/A')?></td><td class="px-4 py-2"><?=date('M d, Y',strtotime($r['created_at']))?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($report_type === 'medical' && isset($patient)): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-1">Medical Record: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h3>
        <p class="text-xs text-gray-500 mb-4">Generated on <?= date('F d, Y h:i A') ?></p>
        <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
            <div><span class="text-gray-500">Email:</span> <?= htmlspecialchars($patient['email']) ?></div>
            <div><span class="text-gray-500">Phone:</span> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></div>
            <div><span class="text-gray-500">Gender:</span> <?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></div>
        </div>
        <h4 class="font-semibold text-sm mb-2">Medical History</h4>
        <table class="w-full text-sm mb-6">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left font-medium">Condition</th><th class="px-4 py-2 text-left font-medium">Diagnosis Date</th><th class="px-4 py-2 text-left font-medium">Notes</th></tr></thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($med_hist)): ?>
            <tr class="border-t"><td class="px-4 py-2"><?=htmlspecialchars($r['condition_name'])?></td><td class="px-4 py-2"><?=$r['diagnosis_date']?date('M d, Y',strtotime($r['diagnosis_date'])):'N/A'?></td><td class="px-4 py-2"><?=htmlspecialchars($r['notes']??'')?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <h4 class="font-semibold text-sm mb-2">Treatments</h4>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left font-medium">Treatment</th><th class="px-4 py-2 text-left font-medium">Date</th><th class="px-4 py-2 text-left font-medium">Description</th></tr></thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($treats)): ?>
            <tr class="border-t"><td class="px-4 py-2"><?=htmlspecialchars($r['treatment_name'])?></td><td class="px-4 py-2"><?=date('M d, Y',strtotime($r['treatment_date']))?></td><td class="px-4 py-2"><?=htmlspecialchars($r['description']??'')?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($report_type === 'inventory' && $data): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-1">Medicine Inventory</h3>
        <p class="text-xs text-gray-500 mb-4">Generated on <?= date('F d, Y h:i A') ?></p>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-4 py-2 font-medium">#</th><th class="px-4 py-2 font-medium">Name</th><th class="px-4 py-2 font-medium">Description</th><th class="px-4 py-2 font-medium">Quantity</th><th class="px-4 py-2 font-medium">Unit</th><th class="px-4 py-2 font-medium">Expiry</th></tr></thead>
            <tbody>
            <?php $n=1; while($r=mysqli_fetch_assoc($data)): ?>
            <tr class="border-t"><td class="px-4 py-2"><?=$n++?></td><td class="px-4 py-2"><?=htmlspecialchars($r['name'])?></td><td class="px-4 py-2"><?=htmlspecialchars($r['description']??'')?></td><td class="px-4 py-2"><?=$r['quantity']?></td><td class="px-4 py-2"><?=htmlspecialchars($r['unit'])?></td><td class="px-4 py-2"><?=$r['expiry_date']?date('M d, Y',strtotime($r['expiry_date'])):'N/A'?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
