<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Update status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    mysqli_query($conn, "UPDATE appointments SET status='$status' WHERE id=$id");
    $success = 'Appointment status updated.';
}

// Filters
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($conn, $_GET['filter_status']) : '';
$filter_date = isset($_GET['filter_date']) ? mysqli_real_escape_string($conn, $_GET['filter_date']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$where = "WHERE 1=1";
if ($filter_status) $where .= " AND a.status='$filter_status'";
if ($filter_date) $where .= " AND a.appointment_date='$filter_date'";
if ($search) $where .= " AND (u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";

$appointments = mysqli_query($conn, "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name, u.email as patient_email,
    CONCAT(hw.first_name,' ',hw.last_name) as hw_name
    FROM appointments a 
    JOIN users u ON a.patient_id=u.id 
    LEFT JOIN users hw ON a.health_worker_id=hw.id 
    $where ORDER BY a.appointment_date DESC, a.appointment_time DESC");

renderHeader('Appointments');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Appointments</h1>
            <p class="text-sm text-gray-500">Manage all patient appointments</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-search"></i></span>
                <input type="text" name="search" placeholder="Search by patient name..." value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <select name="filter_status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none">
                <option value="">All Status</option>
                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none">
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-filter mr-1"></i> Filter</button>
            <a href="appointments.php" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Patient</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Time</th>
                        <th class="px-5 py-3 font-medium">Purpose</th>
                        <th class="px-5 py-3 font-medium">Assigned HW</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($a = mysqli_fetch_assoc($appointments)): ?>
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-5 py-3"><?= $n++ ?></td>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($a['patient_name']) ?></td>
                        <td class="px-5 py-3"><?= date('M d, Y', strtotime($a['appointment_date'])) ?></td>
                        <td class="px-5 py-3"><?= date('h:i A', strtotime($a['appointment_time'])) ?></td>
                        <td class="px-5 py-3"><?= htmlspecialchars($a['purpose']) ?></td>
                        <td class="px-5 py-3"><?= htmlspecialchars($a['hw_name'] ?? 'Unassigned') ?></td>
                        <td class="px-5 py-3">
                            <?php $badge = match($a['status']){'pending'=>'bg-yellow-100 text-yellow-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-700'}; ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if ($a['status'] === 'pending'): ?>
                                <a href="?id=<?= $a['id'] ?>&status=confirmed" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded text-xs" title="Confirm"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <?php if ($a['status'] === 'confirmed'): ?>
                                <a href="?id=<?= $a['id'] ?>&status=completed" class="p-1.5 text-green-600 hover:bg-green-50 rounded text-xs" title="Complete"><i class="fas fa-check-double"></i></a>
                                <?php endif; ?>
                                <?php if ($a['status'] !== 'cancelled' && $a['status'] !== 'completed'): ?>
                                <a href="?id=<?= $a['id'] ?>&status=cancelled" class="p-1.5 text-red-600 hover:bg-red-50 rounded text-xs" title="Cancel"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                                <a href="print_slip.php?id=<?= $a['id'] ?>" target="_blank" class="p-1.5 text-teal hover:bg-teal-50 rounded text-xs" title="Print Slip"><i class="fas fa-print"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
