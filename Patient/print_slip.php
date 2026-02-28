<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$appointment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, CONCAT(p.first_name,' ',p.last_name) as patient_name, p.email as patient_email, p.phone as patient_phone, p.address as patient_address, CONCAT(hw.first_name,' ',hw.last_name) as hw_name FROM appointments a JOIN users p ON a.patient_id=p.id LEFT JOIN users hw ON a.health_worker_id=hw.id WHERE a.id=$id AND a.patient_id=$uid"));
if (!$appointment) { echo '<p>Appointment not found.</p>'; exit(); }
$settings = [];
$sres = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
while ($srow = mysqli_fetch_assoc($sres)) { $settings[$srow['setting_key']] = $srow['setting_value']; }

renderHeader('Appointment Slip');
?>
<body class="bg-white">
<div class="max-w-2xl mx-auto p-8">
    <div class="text-center border-b-2 border-teal pb-4 mb-6">
        <h1 class="text-2xl font-bold text-teal"><?=htmlspecialchars($settings['system_name'] ?? 'Barangay Health Center')?></h1>
        <p class="text-sm text-gray-500"><?=htmlspecialchars($settings['barangay_name'] ?? '')?></p>
        <p class="text-sm text-gray-500"><?=htmlspecialchars($settings['contact_phone'] ?? '')?></p>
        <p class="text-xs text-gray-400 mt-1">Appointment Slip</p>
    </div>
    <div class="grid grid-cols-2 gap-6 text-sm mb-6">
        <div>
            <h3 class="font-semibold text-gray-700 mb-2 border-b pb-1">Patient Information</h3>
            <p><span class="text-gray-500">Name:</span> <strong><?=htmlspecialchars($appointment['patient_name'])?></strong></p>
            <p><span class="text-gray-500">Email:</span> <?=htmlspecialchars($appointment['patient_email'] ?? '')?></p>
            <p><span class="text-gray-500">Phone:</span> <?=htmlspecialchars($appointment['patient_phone'] ?? '')?></p>
            <p><span class="text-gray-500">Address:</span> <?=htmlspecialchars($appointment['patient_address'] ?? '')?></p>
        </div>
        <div>
            <h3 class="font-semibold text-gray-700 mb-2 border-b pb-1">Appointment Details</h3>
            <p><span class="text-gray-500">Date:</span> <strong><?=date('F d, Y',strtotime($appointment['appointment_date']))?></strong></p>
            <p><span class="text-gray-500">Time:</span> <strong><?=date('h:i A',strtotime($appointment['appointment_time']))?></strong></p>
            <p><span class="text-gray-500">Purpose:</span> <?=htmlspecialchars($appointment['purpose'])?></p>
            <p><span class="text-gray-500">Status:</span> <span class="font-semibold"><?=ucfirst($appointment['status'])?></span></p>
            <?php if($appointment['hw_name']): ?><p><span class="text-gray-500">Assigned To:</span> <?=htmlspecialchars($appointment['hw_name'])?></p><?php endif; ?>
        </div>
    </div>
    <?php if($appointment['notes']): ?><div class="text-sm mb-6"><h3 class="font-semibold text-gray-700 mb-1">Notes</h3><p class="text-gray-600"><?=htmlspecialchars($appointment['notes'])?></p></div><?php endif; ?>
    <div class="border-t pt-4 text-center text-xs text-gray-400">
        <p>Generated on <?=date('F d, Y h:i A')?></p>
        <p>This is a computer-generated document.</p>
    </div>
    <div class="flex justify-center gap-3 mt-6 no-print">
        <button onclick="window.print()" class="bg-teal text-white px-6 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-print mr-2"></i>Print</button>
        <button onclick="window.close()" class="border border-gray-300 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Close</button>
    </div>
</div>
</body>
</html>
