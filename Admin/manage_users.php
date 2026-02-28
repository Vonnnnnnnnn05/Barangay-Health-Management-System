<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $fn = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $ln = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $em = mysqli_real_escape_string($conn, trim($_POST['email']));
    $ph = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $pw = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$em'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Email already exists.';
    } else {
        $q = "INSERT INTO users (first_name,last_name,email,password,role,phone) VALUES ('$fn','$ln','$em','$pw','$role','$ph')";
        if (mysqli_query($conn, $q)) {
            $success = 'User added successfully.';
        } else {
            $error = 'Failed to add user.';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $success = 'User deleted successfully.';
    }
}

// Handle Status Toggle
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id=$id"));
    $new_status = $user['status'] === 'active' ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE users SET status='$new_status' WHERE id=$id");
    $success = 'User status updated.';
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid = (int)$_POST['user_id'];
    $fn = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $ln = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $em = mysqli_real_escape_string($conn, trim($_POST['email']));
    $ph = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    $q = "UPDATE users SET first_name='$fn', last_name='$ln', email='$em', phone='$ph', role='$role' WHERE id=$uid";
    if (!empty($_POST['password'])) {
        $pw = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $q = "UPDATE users SET first_name='$fn', last_name='$ln', email='$em', phone='$ph', role='$role', password='$pw' WHERE id=$uid";
    }
    if (mysqli_query($conn, $q)) {
        $success = 'User updated successfully.';
    } else {
        $error = 'Failed to update user.';
    }
}

// Fetch users
$filter_role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = "WHERE 1=1";
if ($filter_role) $where .= " AND role='$filter_role'";
if ($search) $where .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";

$users = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");

renderHeader('Manage Users');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manage Users</h1>
            <p class="text-sm text-gray-500">Add, edit, or remove system users</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-teal hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> <?= $success ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-search"></i></span>
                <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal focus:border-teal outline-none">
            </div>
            <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                <option value="">All Roles</option>
                <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="health_worker" <?= $filter_role === 'health_worker' ? 'selected' : '' ?>>Health Worker</option>
                <option value="patient" <?= $filter_role === 'patient' ? 'selected' : '' ?>>Patient</option>
            </select>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="manage_users.php" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-6 py-3 font-medium">#</th>
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Email</th>
                        <th class="px-6 py-3 font-medium">Role</th>
                        <th class="px-6 py-3 font-medium">Phone</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Registered</th>
                        <th class="px-6 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($u = mysqli_fetch_assoc($users)): ?>
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-6 py-3"><?= $n++ ?></td>
                        <td class="px-6 py-3 font-medium"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="px-6 py-3">
                            <?php
                            $roleBadge = match($u['role']) {
                                'admin' => 'bg-purple-100 text-purple-700',
                                'health_worker' => 'bg-blue-100 text-blue-700',
                                'patient' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            $roleLabel = match($u['role']) {
                                'health_worker' => 'Health Worker',
                                default => ucfirst($u['role'])
                            };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $roleBadge ?>"><?= $roleLabel ?></span>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $u['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                <?= ucfirst($u['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick='editUser(<?= json_encode($u) ?>)'
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?toggle=<?= $u['id'] ?>" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded" title="Toggle Status">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Are you sure?')"
                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add User Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><i class="fas fa-user-plus text-teal mr-2"></i>Add New User</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                        <option value="patient">Patient</option>
                        <option value="health_worker">Health Worker</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <button type="submit" name="add_user" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-1"></i> Add User
            </button>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><i class="fas fa-user-edit text-teal mr-2"></i>Edit User</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" id="edit_fn" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="edit_ln" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input type="email" name="email" id="edit_em" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" id="edit_ph" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" id="edit_role" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
                        <option value="patient">Patient</option>
                        <option value="health_worker">Health Worker</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <button type="submit" name="update_user" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-save mr-1"></i> Update User
            </button>
        </form>
    </div>
</div>

<script>
function editUser(u) {
    document.getElementById('edit_user_id').value = u.id;
    document.getElementById('edit_fn').value = u.first_name;
    document.getElementById('edit_ln').value = u.last_name;
    document.getElementById('edit_em').value = u.email;
    document.getElementById('edit_ph').value = u.phone || '';
    document.getElementById('edit_role').value = u.role;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
</body>
</html>
