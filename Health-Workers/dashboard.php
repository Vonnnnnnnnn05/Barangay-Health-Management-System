<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='patient'"))['c'];
$total_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments"))['c'];
$pending_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE status='pending'"))['c'];
$today_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE appointment_date=CURDATE()"))['c'];

// Monthly data
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE DATE_FORMAT(appointment_date,'%Y-%m')='$month'"));
    $monthly_data[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => (int)$r['c']];
}

$monthly_patients = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='patient' AND DATE_FORMAT(created_at,'%Y-%m')='$month'"));
    $monthly_patients[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => (int)$r['c']];
}

$recent = mysqli_query($conn, "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name 
    FROM appointments a JOIN users u ON a.patient_id=u.id ORDER BY a.created_at DESC LIMIT 5");

renderHeader('Health Worker Dashboard');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-500">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</p>
        </div>
        <div class="text-sm text-gray-500"><i class="fas fa-calendar-alt mr-1"></i> <?= date('F d, Y') ?></div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal">
            <div class="flex items-center justify-between">
                <div><p class="text-xs text-gray-500 uppercase">Patients</p><p class="text-2xl font-bold"><?= $total_patients ?></p></div>
                <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center"><i class="fas fa-users text-teal"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange">
            <div class="flex items-center justify-between">
                <div><p class="text-xs text-gray-500 uppercase">Total Appointments</p><p class="text-2xl font-bold"><?= $total_appointments ?></p></div>
                <div class="w-10 h-10 bg-orange-50 rounded-full flex items-center justify-center"><i class="fas fa-calendar-check text-orange"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div><p class="text-xs text-gray-500 uppercase">Pending</p><p class="text-2xl font-bold"><?= $pending_appointments ?></p></div>
                <div class="w-10 h-10 bg-yellow-50 rounded-full flex items-center justify-center"><i class="fas fa-clock text-yellow-500"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div><p class="text-xs text-gray-500 uppercase">Today's Appts</p><p class="text-2xl font-bold"><?= $today_appointments ?></p></div>
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center"><i class="fas fa-calendar-day text-green-500"></i></div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-bar text-teal mr-2"></i>Appointments (Last 6 Months)</h3>
            <canvas id="apptChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-line text-orange mr-2"></i>New Patients (Last 6 Months)</h3>
            <canvas id="patChart" height="200"></canvas>
        </div>
    </div>

    <!-- Recent -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-clock text-teal mr-2"></i>Recent Appointments</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="pb-3">Patient</th><th class="pb-3">Date</th><th class="pb-3">Time</th><th class="pb-3">Purpose</th><th class="pb-3">Status</th></tr></thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($recent)): ?>
            <tr class="border-b border-gray-100"><td class="py-3"><?=htmlspecialchars($r['patient_name'])?></td><td class="py-3"><?=date('M d, Y',strtotime($r['appointment_date']))?></td><td class="py-3"><?=date('h:i A',strtotime($r['appointment_time']))?></td><td class="py-3"><?=htmlspecialchars($r['purpose'])?></td><td class="py-3"><span class="px-2 py-1 rounded-full text-xs font-medium <?=match($r['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}?>"><?=ucfirst($r['status'])?></span></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('apptChart').getContext('2d'), {
    type: 'bar', data: { labels: <?=json_encode(array_column($monthly_data,'month'))?>, datasets: [{ label: 'Appointments', data: <?=json_encode(array_column($monthly_data,'count'))?>, backgroundColor: '#0F766E', borderRadius: 6 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
new Chart(document.getElementById('patChart').getContext('2d'), {
    type: 'line', data: { labels: <?=json_encode(array_column($monthly_patients,'month'))?>, datasets: [{ label: 'Patients', data: <?=json_encode(array_column($monthly_patients,'count'))?>, borderColor: '#F97316', backgroundColor: 'rgba(249,115,22,0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#F97316' }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
</body>
</html>
