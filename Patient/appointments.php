<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
    $time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    mysqli_query($conn, "INSERT INTO appointments (patient_id, appointment_date, appointment_time, purpose, notes) VALUES ($uid, '$date', '$time', '$purpose', '$notes')");
    $success = 'Appointment scheduled successfully! Please wait for confirmation.';
}

if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    mysqli_query($conn, "UPDATE appointments SET status='cancelled' WHERE id=$id AND patient_id=$uid AND status='pending'");
    $success = 'Appointment cancelled.';
}

$appointments = mysqli_query($conn, "SELECT a.*, CONCAT(hw.first_name,' ',hw.last_name) as hw_name FROM appointments a LEFT JOIN users hw ON a.health_worker_id=hw.id WHERE a.patient_id=$uid ORDER BY a.appointment_date DESC");

renderHeader('My Appointments');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">My Appointments</h1><p class="text-sm text-gray-500">Schedule and manage your appointments</p></div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition flex items-center gap-2"><i class="fas fa-plus"></i> Book Appointment</button>
    </div>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>

    <div class="grid gap-4">
    <?php while($a=mysqli_fetch_assoc($appointments)): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl flex flex-col items-center justify-center text-center <?=match($a['status']){'pending'=>'bg-yellow-50 text-yellow-700','confirmed'=>'bg-blue-50 text-blue-700','completed'=>'bg-green-50 text-green-700','cancelled'=>'bg-red-50 text-red-700',default=>'bg-gray-50 text-gray-700'}?>">
                <span class="text-lg font-bold leading-none"><?=date('d',strtotime($a['appointment_date']))?></span>
                <span class="text-xs"><?=date('M',strtotime($a['appointment_date']))?></span>
            </div>
            <div>
                <p class="font-semibold text-sm"><?=htmlspecialchars($a['purpose'])?></p>
                <p class="text-xs text-gray-500"><i class="fas fa-clock mr-1"></i><?=date('h:i A',strtotime($a['appointment_time']))?> <?php if($a['hw_name']): ?><span class="ml-2"><i class="fas fa-user-nurse mr-1"></i><?=htmlspecialchars($a['hw_name'])?></span><?php endif; ?></p>
                <?php if($a['notes']): ?><p class="text-xs text-gray-400 mt-1"><?=htmlspecialchars($a['notes'])?></p><?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-medium <?=match($a['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($a['status'])?></span>
            <?php if($a['status']==='pending'): ?><a href="?cancel=<?=$a['id']?>" onclick="return confirm('Cancel this appointment?')" class="text-red-500 hover:text-red-700 text-sm" title="Cancel"><i class="fas fa-times-circle"></i></a><?php endif; ?>
            <a href="print_slip.php?id=<?=$a['id']?>" target="_blank" class="text-teal hover:text-teal-700 text-sm" title="Print Slip"><i class="fas fa-print"></i></a>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
</main>

<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-calendar-plus text-teal mr-2"></i>Book Appointment</h2>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Date *</label><input type="date" name="appointment_date" required min="<?=date('Y-m-d')?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Time *</label><input type="time" name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Purpose *</label><input type="text" name="purpose" required placeholder="e.g. General Checkup, Follow-up" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea name="notes" rows="3" placeholder="Any additional details..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></textarea></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button><button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">Book Now</button></div>
        </form>
    </div>
</div>
</body>
</html>
