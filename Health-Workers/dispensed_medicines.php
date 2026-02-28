<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'health_worker') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)$_POST['patient_id'];
    $medicine_id = (int)$_POST['medicine_id'];
    $quantity = (int)$_POST['quantity'];
    $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
    $frequency = mysqli_real_escape_string($conn, $_POST['frequency']);

    $med = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM medicines WHERE id=$medicine_id"));
    if (!$med) {
        $error = 'Medicine not found.';
    } elseif ($med['quantity'] < $quantity) {
        $error = 'Insufficient stock. Available: '.$med['quantity'];
    } else {
        mysqli_query($conn, "INSERT INTO dispensed_medicines (patient_id, medicine_id, dispensed_by, quantity_given, dosage, frequency, dispensed_date) VALUES ($patient_id, $medicine_id, {$_SESSION['user_id']}, $quantity, '$dosage', '$frequency', CURDATE())");
        mysqli_query($conn, "UPDATE medicines SET quantity=quantity-$quantity WHERE id=$medicine_id");
        $success = 'Medicine dispensed successfully.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $dm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM dispensed_medicines WHERE id=$id"));
    if ($dm) {
        mysqli_query($conn, "UPDATE medicines SET quantity=quantity+{$dm['quantity_given']} WHERE id={$dm['medicine_id']}");
        mysqli_query($conn, "DELETE FROM dispensed_medicines WHERE id=$id");
        $success = 'Record deleted and stock restored.';
    }
}

$dispensed = mysqli_query($conn, "SELECT dm.*, CONCAT(u.first_name,' ',u.last_name) as patient_name, m.name as medicine_name, CONCAT(hw.first_name,' ',hw.last_name) as dispensed_by_name FROM dispensed_medicines dm JOIN users u ON dm.patient_id=u.id JOIN medicines m ON dm.medicine_id=m.id JOIN users hw ON dm.dispensed_by=hw.id ORDER BY dm.dispensed_date DESC");
$patients = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',last_name) as name FROM users WHERE role='patient' AND status='active' ORDER BY first_name");
$medicines = mysqli_query($conn, "SELECT * FROM medicines WHERE quantity>0 ORDER BY name");

renderHeader('Dispense Medicine');
?>
<body class="bg-beige">
<?php include '../sidbar/healthWorkerSidebar.php'; ?>
<main class="ml-64 p-6">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-800">Dispense Medicine</h1><p class="text-sm text-gray-500">Track medicine dispensed to patients</p></div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition flex items-center gap-2"><i class="fas fa-plus"></i> Dispense</button>
    </div>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?=$error?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-600"><th class="px-5 py-3 font-medium">#</th><th class="px-5 py-3 font-medium">Patient</th><th class="px-5 py-3 font-medium">Medicine</th><th class="px-5 py-3 font-medium">Qty</th><th class="px-5 py-3 font-medium">Dosage</th><th class="px-5 py-3 font-medium">Frequency</th><th class="px-5 py-3 font-medium">Dispensed By</th><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium text-center">Action</th></tr></thead>
            <tbody>
            <?php $n=1; while($d=mysqli_fetch_assoc($dispensed)): ?>
            <tr class="border-t hover:bg-gray-50"><td class="px-5 py-3"><?=$n++?></td><td class="px-5 py-3 font-medium"><?=htmlspecialchars($d['patient_name'])?></td><td class="px-5 py-3"><?=htmlspecialchars($d['medicine_name'])?></td><td class="px-5 py-3"><?=$d['quantity_given']?></td><td class="px-5 py-3"><?=htmlspecialchars($d['dosage'])?></td><td class="px-5 py-3"><?=htmlspecialchars($d['frequency'])?></td><td class="px-5 py-3"><?=htmlspecialchars($d['dispensed_by_name'])?></td><td class="px-5 py-3"><?=date('M d, Y',strtotime($d['dispensed_date']))?></td>
            <td class="px-5 py-3 text-center"><a href="?delete=<?=$d['id']?>" onclick="return confirm('Delete and restore stock?')" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        <h2 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-pills text-teal mr-2"></i>Dispense Medicine</h2>
        <form method="POST" class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Patient *</label><select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"><option value="">Select Patient</option><?php mysqli_data_seek($patients,0); while($p=mysqli_fetch_assoc($patients)): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endwhile; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Medicine *</label><select name="medicine_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal""><option value="">Select Medicine</option><?php mysqli_data_seek($medicines,0); while($m=mysqli_fetch_assoc($medicines)): ?><option value="<?=$m['id']?>"><?=htmlspecialchars($m['name'])?> (Stock: <?=$m['quantity']?>)</option><?php endwhile; ?></select></div>
            <div class="grid grid-cols-3 gap-3">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label><input type="number" name="quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Dosage *</label><input type="text" name="dosage" required placeholder="e.g. 500mg" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label><input type="text" name="frequency" required placeholder="e.g. 3x/day" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            </div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button><button type="submit" class="bg-teal text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">Dispense</button></div>
        </form>
    </div>
</div>
</body>
</html>
