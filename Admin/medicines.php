<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

// Add Medicine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medicine'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    $qty = (int)$_POST['quantity'];
    $unit = mysqli_real_escape_string($conn, trim($_POST['unit']));
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    if (mysqli_query($conn, "INSERT INTO medicines (name,description,quantity,unit,expiry_date,added_by) VALUES ('$name','$desc',$qty,'$unit','$expiry',{$_SESSION['user_id']})")) {
        $success = 'Medicine added successfully.';
    } else { $error = 'Failed to add medicine.'; }
}

// Update Medicine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_medicine'])) {
    $mid = (int)$_POST['medicine_id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    $qty = (int)$_POST['quantity'];
    $unit = mysqli_real_escape_string($conn, trim($_POST['unit']));
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    if (mysqli_query($conn, "UPDATE medicines SET name='$name', description='$desc', quantity=$qty, unit='$unit', expiry_date='$expiry' WHERE id=$mid")) {
        $success = 'Medicine updated.';
    } else { $error = 'Failed to update.'; }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM medicines WHERE id=$id");
    $success = 'Medicine deleted.';
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = $search ? "WHERE name LIKE '%$search%'" : '';
$medicines = mysqli_query($conn, "SELECT * FROM medicines $where ORDER BY name ASC");

renderHeader('Medicine Inventory');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Medicine Inventory</h1>
            <p class="text-sm text-gray-500">Manage medicine stock and details</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Add Medicine
        </button>
    </div>

    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex items-center gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-search"></i></span>
                <input type="text" name="search" placeholder="Search medicines..." value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none">
            </div>
            <button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-search mr-1"></i> Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Description</th>
                        <th class="px-5 py-3 font-medium">Quantity</th>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Expiry Date</th>
                        <th class="px-5 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1; while ($m = mysqli_fetch_assoc($medicines)): ?>
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-5 py-3"><?= $n++ ?></td>
                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($m['name']) ?></td>
                        <td class="px-5 py-3 text-gray-500"><?= htmlspecialchars($m['description'] ?? '') ?></td>
                        <td class="px-5 py-3">
                            <span class="<?= $m['quantity'] <= 10 ? 'text-red-600 font-bold' : '' ?>"><?= $m['quantity'] ?></span>
                            <?php if ($m['quantity'] <= 10): ?><i class="fas fa-exclamation-triangle text-red-400 ml-1" title="Low stock"></i><?php endif; ?>
                        </td>
                        <td class="px-5 py-3"><?= htmlspecialchars($m['unit']) ?></td>
                        <td class="px-5 py-3 <?= ($m['expiry_date'] && strtotime($m['expiry_date']) < time()) ? 'text-red-600' : '' ?>">
                            <?= $m['expiry_date'] ? date('M d, Y', strtotime($m['expiry_date'])) : 'N/A' ?>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button onclick='editMed(<?= json_encode($m) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded"><i class="fas fa-edit"></i></button>
                            <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this medicine?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><i class="fas fa-pills text-teal mr-2"></i>Add Medicine</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Medicine Name</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Description</label><textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none resize-none"></textarea></div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label><input type="number" name="quantity" min="0" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Unit</label><input type="text" name="unit" value="pcs" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            </div>
            <div class="mb-4"><label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label><input type="date" name="expiry_date" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <button type="submit" name="add_medicine" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-plus mr-1"></i> Add Medicine</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><i class="fas fa-edit text-teal mr-2"></i>Edit Medicine</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="medicine_id" id="em_id">
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Name</label><input type="text" name="name" id="em_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <div class="mb-3"><label class="block text-xs font-medium text-gray-600 mb-1">Description</label><textarea name="description" id="em_desc" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none resize-none"></textarea></div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label><input type="number" name="quantity" id="em_qty" min="0" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Unit</label><input type="text" name="unit" id="em_unit" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            </div>
            <div class="mb-4"><label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label><input type="date" name="expiry_date" id="em_exp" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-teal outline-none"></div>
            <button type="submit" name="update_medicine" class="w-full bg-teal hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-save mr-1"></i> Update</button>
        </form>
    </div>
</div>

<script>
function editMed(m) {
    document.getElementById('em_id').value = m.id;
    document.getElementById('em_name').value = m.name;
    document.getElementById('em_desc').value = m.description || '';
    document.getElementById('em_qty').value = m.quantity;
    document.getElementById('em_unit').value = m.unit;
    document.getElementById('em_exp').value = m.expiry_date || '';
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
</body>
</html>
