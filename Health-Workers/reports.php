<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$report_type = isset($_GET['type']) ? $_GET['type'] : '';
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

$patients = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',last_name) as name FROM users WHERE role='patient' ORDER BY first_name");

renderHeader('Reports');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Reports</h1><p class="text-sm text-gray-500">Generate and print reports</p></div>
        <?php if ($report_type): ?><button onclick="window.print()" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition flex items-center gap-2 no-print"><i class="fas fa-print"></i> Print</button><?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 no-print">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Select Report Type</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="?type=appointments" class="border rounded-lg p-4 text-center hover:border-teal transition <?=$report_type==='appointments'?'border-teal bg-teal-50':''?>"><i class="fas fa-calendar-check text-2xl text-teal mb-2"></i><p class="text-sm font-medium">Appointments</p></a>
            <a href="?type=patients" class="border rounded-lg p-4 text-center hover:border-teal transition <?=$report_type==='patients'?'border-teal bg-teal-50':''?>"><i class="fas fa-users text-2xl text-teal mb-2"></i><p class="text-sm font-medium">Patient List</p></a>
            <a href="?type=medical" class="border rounded-lg p-4 text-center hover:border-teal transition <?=$report_type==='medical'?'border-teal bg-teal-50':''?>"><i class="fas fa-file-medical text-2xl text-teal mb-2"></i><p class="text-sm font-medium">Medical Record</p></a>
            <a href="?type=medicines" class="border rounded-lg p-4 text-center hover:border-teal transition <?=$report_type==='medicines'?'border-teal bg-teal-50':''?>"><i class="fas fa-pills text-2xl text-teal mb-2"></i><p class="text-sm font-medium">Medicine Inventory</p></a>
        </div>
        <?php if ($report_type === 'medical'): ?>
        <form method="GET" class="mt-4 flex items-center gap-3">
            <input type="hidden" name="type" value="medical">
            <select name="patient_id" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none"><option value="">Select Patient</option><?php mysqli_data_seek($patients,0); while($p=mysqli_fetch_assoc($patients)): ?><option value="<?=$p['id']?>" <?=$patient_id==$p['id']?'selected':''?>><?=htmlspecialchars($p['name'])?></option><?php endwhile; ?></select>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">Generate</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if ($report_type): ?>
    <div class="bg-white rounded-xl shadow-sm p-6" id="report">
        <div class="text-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-teal">Barangay Health Center</h2>
            <p class="text-sm text-gray-500">Generated on <?=date('F d, Y h:i A')?></p>
        </div>

        <?php if ($report_type === 'appointments'): ?>
        <h3 class="font-bold text-lg mb-4">Appointments Report</h3>
        <table class="w-full text-sm border-collapse">
            <thead><tr class="bg-gray-100"><th class="border px-3 py-2 text-left">Patient</th><th class="border px-3 py-2 text-left">Date</th><th class="border px-3 py-2 text-left">Time</th><th class="border px-3 py-2 text-left">Purpose</th><th class="border px-3 py-2 text-left">Status</th></tr></thead>
            <tbody>
            <?php $rows=mysqli_query($conn,"SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name FROM appointments a JOIN users u ON a.patient_id=u.id ORDER BY a.appointment_date DESC"); while($r=mysqli_fetch_assoc($rows)): ?>
            <tr><td class="border px-3 py-2"><?=htmlspecialchars($r['patient_name'])?></td><td class="border px-3 py-2"><?=date('M d, Y',strtotime($r['appointment_date']))?></td><td class="border px-3 py-2"><?=date('h:i A',strtotime($r['appointment_time']))?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['purpose'])?></td><td class="border px-3 py-2"><?=ucfirst($r['status'])?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <?php elseif ($report_type === 'patients'): ?>
        <h3 class="font-bold text-lg mb-4">Patient List</h3>
        <table class="w-full text-sm border-collapse">
            <thead><tr class="bg-gray-100"><th class="border px-3 py-2 text-left">#</th><th class="border px-3 py-2 text-left">Name</th><th class="border px-3 py-2 text-left">Email</th><th class="border px-3 py-2 text-left">Phone</th><th class="border px-3 py-2 text-left">Gender</th><th class="border px-3 py-2 text-left">Address</th></tr></thead>
            <tbody>
            <?php $n=1; $rows=mysqli_query($conn,"SELECT * FROM users WHERE role='patient' ORDER BY first_name"); while($r=mysqli_fetch_assoc($rows)): ?>
            <tr><td class="border px-3 py-2"><?=$n++?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['email'])?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['phone'] ?? '')?></td><td class="border px-3 py-2"><?=ucfirst($r['gender'] ?? '')?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['address'] ?? '')?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <?php elseif ($report_type === 'medical' && $patient_id): ?>
        <?php $pt=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$patient_id")); ?>
        <h3 class="font-bold text-lg mb-2">Medical Record: <?=htmlspecialchars($pt['first_name'].' '.$pt['last_name'])?></h3>
        <p class="text-sm text-gray-500 mb-4">DOB: <?=$pt['date_of_birth']?date('M d, Y',strtotime($pt['date_of_birth'])):'-'?> | Gender: <?=ucfirst($pt['gender'] ?? '-')?></p>
        <h4 class="font-semibold mt-4 mb-2">Medical History</h4>
        <table class="w-full text-sm border-collapse mb-4">
            <thead><tr class="bg-gray-100"><th class="border px-3 py-2 text-left">Condition</th><th class="border px-3 py-2 text-left">Diagnosis Date</th><th class="border px-3 py-2 text-left">Notes</th></tr></thead>
            <tbody><?php $rows=mysqli_query($conn,"SELECT * FROM medical_history WHERE patient_id=$patient_id ORDER BY diagnosis_date DESC"); while($r=mysqli_fetch_assoc($rows)): ?><tr><td class="border px-3 py-2"><?=htmlspecialchars($r['condition_name'])?></td><td class="border px-3 py-2"><?=date('M d, Y',strtotime($r['diagnosis_date']))?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['notes'] ?? '')?></td></tr><?php endwhile; ?></tbody>
        </table>
        <h4 class="font-semibold mt-4 mb-2">Treatments</h4>
        <table class="w-full text-sm border-collapse">
            <thead><tr class="bg-gray-100"><th class="border px-3 py-2 text-left">Treatment</th><th class="border px-3 py-2 text-left">Date</th><th class="border px-3 py-2 text-left">Description</th></tr></thead>
            <tbody><?php $rows=mysqli_query($conn,"SELECT * FROM treatments WHERE patient_id=$patient_id ORDER BY treatment_date DESC"); while($r=mysqli_fetch_assoc($rows)): ?><tr><td class="border px-3 py-2"><?=htmlspecialchars($r['treatment_name'])?></td><td class="border px-3 py-2"><?=date('M d, Y',strtotime($r['treatment_date']))?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['description'] ?? '')?></td></tr><?php endwhile; ?></tbody>
        </table>

        <?php elseif ($report_type === 'medicines'): ?>
        <h3 class="font-bold text-lg mb-4">Medicine Inventory</h3>
        <table class="w-full text-sm border-collapse">
            <thead><tr class="bg-gray-100"><th class="border px-3 py-2 text-left">#</th><th class="border px-3 py-2 text-left">Name</th><th class="border px-3 py-2 text-left">Unit</th><th class="border px-3 py-2 text-left">Quantity</th><th class="border px-3 py-2 text-left">Expiry</th></tr></thead>
            <tbody>
            <?php $n=1; $rows=mysqli_query($conn,"SELECT * FROM medicines ORDER BY name"); while($r=mysqli_fetch_assoc($rows)): ?>
            <tr><td class="border px-3 py-2"><?=$n++?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['name'])?></td><td class="border px-3 py-2"><?=htmlspecialchars($r['unit'] ?? '')?></td><td class="border px-3 py-2"><?=$r['quantity']?></td><td class="border px-3 py-2"><?=$r['expiry_date']?date('M d, Y',strtotime($r['expiry_date'])):'-'?></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
