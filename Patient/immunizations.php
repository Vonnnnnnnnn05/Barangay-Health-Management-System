<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$immunizations = mysqli_query($conn, "SELECT i.*, CONCAT(hw.first_name,' ',hw.last_name) as administered_by_name FROM immunizations i LEFT JOIN users hw ON i.administered_by=hw.id WHERE i.patient_id=$uid ORDER BY i.scheduled_date DESC");

renderHeader('My Immunizations');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Immunization Schedule</h1>
    <p class="text-sm text-gray-500 mb-6">View your vaccination records and upcoming schedules</p>

    <div class="grid gap-4">
    <?php while($i=mysqli_fetch_assoc($immunizations)): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center <?=match($i['status']){'scheduled'=>'bg-yellow-50','completed'=>'bg-green-50','missed'=>'bg-red-50',default=>'bg-gray-50'}?>">
                <i class="fas fa-syringe text-lg <?=match($i['status']){'scheduled'=>'text-yellow-600','completed'=>'text-green-600','missed'=>'text-red-600',default=>'text-gray-600'}?>"></i>
            </div>
            <div>
                <p class="font-semibold text-sm"><?=htmlspecialchars($i['vaccine_name'])?></p>
                <p class="text-xs text-gray-500"><i class="fas fa-calendar mr-1"></i>Scheduled: <?=date('M d, Y',strtotime($i['scheduled_date']))?></p>
                <?php if($i['administered_date']): ?><p class="text-xs text-green-600"><i class="fas fa-check mr-1"></i>Administered: <?=date('M d, Y',strtotime($i['administered_date']))?></p><?php endif; ?>
                <?php if($i['administered_by_name']): ?><p class="text-xs text-gray-400">By: <?=htmlspecialchars($i['administered_by_name'])?></p><?php endif; ?>
                <?php if($i['notes']): ?><p class="text-xs text-gray-400 mt-1"><?=htmlspecialchars($i['notes'])?></p><?php endif; ?>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium <?=match($i['status']){'scheduled'=>'bg-yellow-100 text-yellow-700','completed'=>'bg-green-100 text-green-700','missed'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($i['status'])?></span>
    </div>
    <?php endwhile; ?>
    </div>
</main>
</body>
</html>
