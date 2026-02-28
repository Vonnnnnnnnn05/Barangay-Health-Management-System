<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='patient'");
    $success = 'Patient deleted successfully.';
}

// Fetch patients
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = "WHERE role='patient'";
if ($search) $where .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";

$patients = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");

renderHeader('Manage Patients');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Patients</h1>
            <p class="text-sm text-gray-500">View and manage all patient records</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> <?= $success ?>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-search"></i></span>
                <input type="text" name="search" placeholder="Search patients..." value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-search mr-1"></i> Search</button>
            <a href="manage_patients.php" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        </form>
    </div>

    <!-- Patients Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-6 py-3 font-medium">#</th>
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Email</th>
                        <th class="px-6 py-3 font-medium">Phone</th>
                        <th class="px-6 py-3 font-medium">Gender</th>
                        <th class="px-6 py-3 font-medium">Date of Birth</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($p = mysqli_fetch_assoc($patients)): ?>
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-6 py-3"><?= $n++ ?></td>
                        <td class="px-6 py-3 font-medium"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($p['email']) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($p['phone'] ?? 'N/A') ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($p['gender'] ?? 'N/A') ?></td>
                        <td class="px-6 py-3"><?= $p['date_of_birth'] ? date('M d, Y', strtotime($p['date_of_birth'])) : 'N/A' ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $p['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="view_patient.php?id=<?= $p['id'] ?>" class="p-1.5 text-teal hover:bg-teal-50 rounded" title="View Records">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
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
