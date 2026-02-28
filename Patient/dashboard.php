<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

$total_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE patient_id=$uid"))['c'];
$upcoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE patient_id=$uid AND status IN ('pending','confirmed') AND appointment_date >= CURDATE()"))['c'];
$medical_records = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM medical_history WHERE patient_id=$uid"))['c'];
$total_immunizations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM immunizations WHERE patient_id=$uid"))['c'];

$recent_appointments = mysqli_query($conn, "SELECT a.*, CONCAT(hw.first_name,' ',hw.last_name) as hw_name FROM appointments a LEFT JOIN users hw ON a.health_worker_id=hw.id WHERE a.patient_id=$uid ORDER BY a.appointment_date DESC LIMIT 5");
$upcoming_immunizations = mysqli_query($conn, "SELECT * FROM immunizations WHERE patient_id=$uid AND status='scheduled' ORDER BY scheduled_date ASC LIMIT 5");

renderHeader('My Dashboard');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, <?=htmlspecialchars($user['first_name'])?></h1>
        <p class="text-sm text-gray-500">Here's an overview of your health records</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal"><div class="flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase tracking-wider">Total Appointments</p><p class="text-2xl font-bold text-gray-800 mt-1"><?=$total_appointments?></p></div><div class="w-12 h-12 bg-teal-50 rounded-lg flex items-center justify-center"><i class="fas fa-calendar-check text-teal text-xl"></i></div></div></div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange"><div class="flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase tracking-wider">Upcoming</p><p class="text-2xl font-bold text-gray-800 mt-1"><?=$upcoming?></p></div><div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center"><i class="fas fa-clock text-orange text-xl"></i></div></div></div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500"><div class="flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase tracking-wider">Medical Records</p><p class="text-2xl font-bold text-gray-800 mt-1"><?=$medical_records?></p></div><div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center"><i class="fas fa-file-medical text-blue-500 text-xl"></i></div></div></div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500"><div class="flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase tracking-wider">Immunizations</p><p class="text-2xl font-bold text-gray-800 mt-1"><?=$total_immunizations?></p></div><div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center"><i class="fas fa-syringe text-green-500 text-xl"></i></div></div></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-calendar text-teal"></i> Recent Appointments</h3>
            <div class="space-y-3">
            <?php while($a=mysqli_fetch_assoc($recent_appointments)): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div><p class="text-sm font-medium"><?=htmlspecialchars($a['purpose'])?></p><p class="text-xs text-gray-500"><?=date('M d, Y',strtotime($a['appointment_date']))?> at <?=date('h:i A',strtotime($a['appointment_time']))?></p></div>
                <span class="px-2 py-1 rounded-full text-xs font-medium <?=match($a['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($a['status'])?></span>
            </div>
            <?php endwhile; ?>
            </div>
            <a href="appointments.php" class="inline-block mt-3 text-sm text-teal hover:underline">View all appointments <i class="fas fa-arrow-right text-xs"></i></a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-syringe text-teal"></i> Upcoming Immunizations</h3>
            <div class="space-y-3">
            <?php while($i=mysqli_fetch_assoc($upcoming_immunizations)): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div><p class="text-sm font-medium"><?=htmlspecialchars($i['vaccine_name'])?></p><p class="text-xs text-gray-500">Scheduled: <?=date('M d, Y',strtotime($i['scheduled_date']))?></p></div>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Scheduled</span>
            </div>
            <?php endwhile; ?>
            </div>
            <a href="immunizations.php" class="inline-block mt-3 text-sm text-teal hover:underline">View all immunizations <i class="fas fa-arrow-right text-xs"></i></a>
        </div>
    </div>
</main>
</body>
</html>
