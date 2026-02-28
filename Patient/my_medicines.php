<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$medicines = mysqli_query($conn, "SELECT dm.*, m.name as medicine_name, m.description as medicine_desc, CONCAT(hw.first_name,' ',hw.last_name) as dispensed_by_name FROM dispensed_medicines dm JOIN medicines m ON dm.medicine_id=m.id JOIN users hw ON dm.dispensed_by=hw.id WHERE dm.patient_id=$uid ORDER BY dm.dispensed_date DESC");

renderHeader('My Medicines');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">My Medicines</h1>
    <p class="text-sm text-gray-500 mb-6">View medicines dispensed to you</p>

    <div class="grid gap-4">
    <?php while($m=mysqli_fetch_assoc($medicines)): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center">
            <i class="fas fa-pills text-teal text-lg"></i>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-sm"><?=htmlspecialchars($m['medicine_name'])?></p>
            <p class="text-xs text-gray-500"><?=htmlspecialchars($m['medicine_desc'] ?? '')?></p>
            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                <span><i class="fas fa-hashtag mr-1"></i>Qty: <?=$m['quantity_given']?></span>
                <span><i class="fas fa-prescription mr-1"></i>Dosage: <?=htmlspecialchars($m['dosage'])?></span>
                <span><i class="fas fa-sync-alt mr-1"></i>Frequency: <?=htmlspecialchars($m['frequency'])?></span>
            </div>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p><?=date('M d, Y',strtotime($m['dispensed_date']))?></p>
            <p>By: <?=htmlspecialchars($m['dispensed_by_name'])?></p>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
</main>
</body>
</html>
