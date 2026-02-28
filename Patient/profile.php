<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    mysqli_query($conn, "UPDATE users SET first_name='$first_name', last_name='$last_name', phone='$phone', address='$address', date_of_birth='$date_of_birth', gender='$gender' WHERE id=$uid");

    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id=$uid");
            $success = 'Profile and password updated.';
        }
    } else {
        $success = 'Profile updated successfully.';
    }
    $_SESSION['first_name'] = $first_name;
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

renderHeader('My Profile');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">My Profile</h1>
    <p class="text-sm text-gray-500 mb-6">Update your personal information</p>
    <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?=$success?></div><?php endif; ?>
    <?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?=$error?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b">
            <div class="w-16 h-16 rounded-full bg-teal flex items-center justify-center text-white text-2xl font-bold"><?=strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1))?></div>
            <div><p class="text-lg font-semibold"><?=htmlspecialchars($user['first_name'].' '.$user['last_name'])?></p><p class="text-sm text-gray-500"><?=htmlspecialchars($user['email'])?></p></div>
        </div>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label><input type="text" name="first_name" value="<?=htmlspecialchars($user['first_name'])?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label><input type="text" name="last_name" value="<?=htmlspecialchars($user['last_name'])?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input type="text" name="phone" value="<?=htmlspecialchars($user['phone'] ?? '')?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Gender</label><select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"><option value="">Select</option><option value="male" <?=($user['gender']??'')==='male'?'selected':''?>>Male</option><option value="female" <?=($user['gender']??'')==='female'?'selected':''?>>Female</option></select></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label><input type="date" name="date_of_birth" value="<?=$user['date_of_birth'] ?? ''?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"><?=htmlspecialchars($user['address'] ?? '')?></textarea></div>

            <hr class="my-4">
            <p class="text-sm font-semibold text-gray-700">Change Password <span class="font-normal text-gray-400">(leave blank to keep current)</span></p>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">New Password</label><input type="password" name="new_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label><input type="password" name="confirm_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal"></div>
            </div>
            <div class="flex justify-end pt-2"><button type="submit" class="bg-teal text-white px-6 py-2 rounded-lg text-sm hover:bg-teal-700 transition"><i class="fas fa-save mr-2"></i>Save Changes</button></div>
        </form>
    </div>
</main>
</body>
</html>
