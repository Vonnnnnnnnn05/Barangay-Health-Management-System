<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../conn.php';
require_once '../header.php';

if (!isset($_GET['id'])) { header("Location: manage_patients.php"); exit(); }
$pid = (int)$_GET['id'];
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$pid AND role='patient'"));
if (!$patient) { header("Location: manage_patients.php"); exit(); }

$med_history = mysqli_query($conn, "SELECT mh.*, CONCAT(u.first_name,' ',u.last_name) as recorded_by_name FROM medical_history mh LEFT JOIN users u ON mh.recorded_by=u.id WHERE mh.patient_id=$pid ORDER BY mh.created_at DESC");
$treatments = mysqli_query($conn, "SELECT t.*, CONCAT(u.first_name,' ',u.last_name) as recorded_by_name FROM treatments t LEFT JOIN users u ON t.recorded_by=u.id WHERE t.patient_id=$pid ORDER BY t.treatment_date DESC");
$appointments = mysqli_query($conn, "SELECT * FROM appointments WHERE patient_id=$pid ORDER BY appointment_date DESC LIMIT 10");
$dispensed = mysqli_query($conn, "SELECT dm.*, m.name as medicine_name FROM dispensed_medicines dm JOIN medicines m ON dm.medicine_id=m.id WHERE dm.patient_id=$pid ORDER BY dm.dispensed_date DESC");
$imm = mysqli_query($conn, "SELECT * FROM immunizations WHERE patient_id=$pid ORDER BY scheduled_date DESC");

renderHeader('Patient Details');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="manage_patients.php" class="text-sm text-teal hover:underline"><i class="fas fa-arrow-left mr-1"></i> Back to Patients</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h1>
        </div>
    </div>

    <!-- Patient Info Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-gray-500">Email</p><p class="font-medium text-sm"><?= htmlspecialchars($patient['email']) ?></p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="font-medium text-sm"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p></div>
            <div><p class="text-xs text-gray-500">Gender</p><p class="font-medium text-sm"><?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></p></div>
            <div><p class="text-xs text-gray-500">Date of Birth</p><p class="font-medium text-sm"><?= $patient['date_of_birth'] ? date('M d, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500">Address</p><p class="font-medium text-sm"><?= htmlspecialchars($patient['address'] ?? 'N/A') ?></p></div>
            <div><p class="text-xs text-gray-500">Registered</p><p class="font-medium text-sm"><?= date('M d, Y', strtotime($patient['created_at'])) ?></p></div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-4 flex-wrap">
        <button onclick="showTab('history')" id="tab-history" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-teal text-white"><i class="fas fa-notes-medical mr-1"></i> Medical History</button>
        <button onclick="showTab('treatments')" id="tab-treatments" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-600"><i class="fas fa-stethoscope mr-1"></i> Treatments</button>
        <button onclick="showTab('appointments')" id="tab-appointments" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-600"><i class="fas fa-calendar mr-1"></i> Appointments</button>
        <button onclick="showTab('medicines')" id="tab-medicines" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-600"><i class="fas fa-pills mr-1"></i> Medicines</button>
        <button onclick="showTab('immunizations')" id="tab-immunizations" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-600"><i class="fas fa-syringe mr-1"></i> Immunizations</button>
    </div>

    <!-- Medical History Tab -->
    <div id="content-history" class="tab-content bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-medium">Condition</th><th class="px-6 py-3 font-medium">Diagnosis Date</th><th class="px-6 py-3 font-medium">Notes</th><th class="px-6 py-3 font-medium">Recorded By</th>
                </tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($med_history)): ?>
                <tr class="border-t hover:bg-gray-50"><td class="px-6 py-3"><?= htmlspecialchars($r['condition_name']) ?></td><td class="px-6 py-3"><?= $r['diagnosis_date'] ? date('M d, Y', strtotime($r['diagnosis_date'])) : 'N/A' ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['notes'] ?? '') ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['recorded_by_name'] ?? 'N/A') ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Treatments Tab -->
    <div id="content-treatments" class="tab-content bg-white rounded-xl shadow-sm overflow-hidden hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-medium">Treatment</th><th class="px-6 py-3 font-medium">Date</th><th class="px-6 py-3 font-medium">Description</th><th class="px-6 py-3 font-medium">By</th>
                </tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($treatments)): ?>
                <tr class="border-t hover:bg-gray-50"><td class="px-6 py-3"><?= htmlspecialchars($r['treatment_name']) ?></td><td class="px-6 py-3"><?= date('M d, Y', strtotime($r['treatment_date'])) ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['description'] ?? '') ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['recorded_by_name'] ?? 'N/A') ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Appointments Tab -->
    <div id="content-appointments" class="tab-content bg-white rounded-xl shadow-sm overflow-hidden hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-medium">Date</th><th class="px-6 py-3 font-medium">Time</th><th class="px-6 py-3 font-medium">Purpose</th><th class="px-6 py-3 font-medium">Status</th>
                </tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($appointments)): ?>
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-3"><?= date('M d, Y', strtotime($r['appointment_date'])) ?></td>
                    <td class="px-6 py-3"><?= date('h:i A', strtotime($r['appointment_time'])) ?></td>
                    <td class="px-6 py-3"><?= htmlspecialchars($r['purpose']) ?></td>
                    <td class="px-6 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium <?= match($r['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'} ?>"><?= ucfirst($r['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Medicines Tab -->
    <div id="content-medicines" class="tab-content bg-white rounded-xl shadow-sm overflow-hidden hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-medium">Medicine</th><th class="px-6 py-3 font-medium">Dosage</th><th class="px-6 py-3 font-medium">Frequency</th><th class="px-6 py-3 font-medium">Qty</th><th class="px-6 py-3 font-medium">Date</th>
                </tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($dispensed)): ?>
                <tr class="border-t hover:bg-gray-50"><td class="px-6 py-3"><?= htmlspecialchars($r['medicine_name']) ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['dosage']) ?></td><td class="px-6 py-3"><?= htmlspecialchars($r['frequency']) ?></td><td class="px-6 py-3"><?= $r['quantity_given'] ?></td><td class="px-6 py-3"><?= date('M d, Y', strtotime($r['dispensed_date'])) ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Immunizations Tab -->
    <div id="content-immunizations" class="tab-content bg-white rounded-xl shadow-sm overflow-hidden hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-medium">Vaccine</th><th class="px-6 py-3 font-medium">Dose #</th><th class="px-6 py-3 font-medium">Scheduled</th><th class="px-6 py-3 font-medium">Administered</th><th class="px-6 py-3 font-medium">Status</th>
                </tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($imm)): ?>
                <tr class="border-t hover:bg-gray-50"><td class="px-6 py-3"><?= htmlspecialchars($r['vaccine_name']) ?></td><td class="px-6 py-3"><?= $r['dose_number'] ?></td><td class="px-6 py-3"><?= date('M d, Y', strtotime($r['scheduled_date'])) ?></td><td class="px-6 py-3"><?= $r['administered_date'] ? date('M d, Y', strtotime($r['administered_date'])) : '-' ?></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium <?= match($r['status']){'completed'=>'bg-green-100 text-green-700','missed'=>'bg-red-100 text-red-700',default=>'bg-yellow-100 text-yellow-700'} ?>"><?= ucfirst($r['status']) ?></span></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function showTab(name) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => { el.classList.remove('bg-teal','text-white'); el.classList.add('bg-gray-200','text-gray-600'); });
    document.getElementById('content-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.classList.remove('bg-gray-200','text-gray-600');
    btn.classList.add('bg-teal','text-white');
}
</script>
</body>
</html>
