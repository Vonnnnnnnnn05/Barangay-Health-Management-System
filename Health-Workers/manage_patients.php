<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Add patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $fn = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $ln = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $em = mysqli_real_escape_string($conn, trim($_POST['email']));
    $ph = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $gen = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $addr = mysqli_real_escape_string($conn, trim($_POST['address']));
    $pw = password_hash('patient123', PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$em'");
    if (mysqli_num_rows($check) > 0) { $error = 'Email already exists.'; }
    else {
        if (mysqli_query($conn, "INSERT INTO users (first_name,last_name,email,password,role,phone,gender,date_of_birth,address) VALUES ('$fn','$ln','$em','$pw','patient','$ph','$gen','$dob','$addr')")) {
            $success = 'Patient added. Default password: patient123';
        } else { $error = 'Failed to add patient.'; }
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = "WHERE role='patient'";
if ($search) $where .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
$patients = mysqli_query($conn, "SELECT * FROM users $where ORDER BY first_name ASC");

renderHeader('Patient Records');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Patient Records</h1><p class="text-sm text-gray-500">Manage patient information</p></div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition"><i class="fas fa-plus"></i> Add Patient</button>
    </div>

    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <div class="relative flex-1"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-search"></i></span>
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-search mr-1"></i> Search</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-5 py-3 font-medium">#</th><th class="px-5 py-3 font-medium">Name</th><th class="px-5 py-3 font-medium">Email</th><th class="px-5 py-3 font-medium">Phone</th><th class="px-5 py-3 font-medium">Gender</th><th class="px-5 py-3 font-medium">DOB</th><th class="px-5 py-3 font-medium text-center">Actions</th></tr></thead>
            <tbody>
            <?php $n=1; while($p=mysqli_fetch_assoc($patients)): ?>
            <tr class="border-t hover:bg-gray-50"><td class="px-5 py-3"><?=$n++?></td><td class="px-5 py-3 font-medium"><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></td><td class="px-5 py-3"><?=htmlspecialchars($p['email'])?></td><td class="px-5 py-3"><?=htmlspecialchars($p['phone']??'N/A')?></td><td class="px-5 py-3"><?=htmlspecialchars($p['gender']??'N/A')?></td><td class="px-5 py-3"><?=$p['date_of_birth']?date('M d, Y',strtotime($p['date_of_birth'])):'N/A'?></td>
            <td class="px-5 py-3 text-center"><a href="view_patient.php?id=<?=$p['id']?>" class="p-1.5 text-teal hover:bg-teal-50 rounded" title="View"><i class="fas fa-eye"></i></a></td></tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add Patient Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold"><i class="fas fa-user-plus text-teal mr-2"></i>Add Patient</h3><button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button></div>
        <form method="POST">
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">First Name</label><input type="text" name="first_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label><input type="text" name="last_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            </div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Email</label><input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Phone</label><input type="text" name="phone" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Gender</label><select name="gender" class="w-full px-3 py-2 border rounded-lg text-sm outline-none"><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of Birth</label><input type="date" name="date_of_birth" class="w-full px-3 py-2 border rounded-lg text-sm outline-none"></div>
            </div>
            <div class="mb-4"><label class="block text-xs font-medium text-gray-600 mb-1">Address</label><textarea name="address" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none resize-none"></textarea></div>
            <p class="text-xs text-gray-400 mb-3"><i class="fas fa-info-circle mr-1"></i>Default password: patient123</p>
            <button type="submit" name="add_patient" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-plus mr-1"></i> Add Patient</button>
        </form>
    </div>
</div>
</body>
</html>
