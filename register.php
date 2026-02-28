<?php
session_start();
require_once 'conn.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email address already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (first_name, last_name, email, password, role, phone, gender, date_of_birth, address)
                      VALUES ('$first_name', '$last_name', '$email', '$hashed', 'patient', '$phone', '$gender', '$dob', '$address')";
            if (mysqli_query($conn, $query)) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Barangay Health Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { teal: { DEFAULT: '#0F766E', 700: '#0A5550' }, beige: { DEFAULT: '#F5F5DC' }, orange: { DEFAULT: '#F97316', 600: '#E06612' } } } }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-beige min-h-screen flex items-center justify-center py-8">
    <div class="w-full max-w-lg">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-teal rounded-full mb-3">
                <i class="fas fa-heartbeat text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-teal">Patient Registration</h1>
            <p class="text-gray-500 text-sm mt-1">Create your account to get started</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span class="text-sm"><?= $error ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span class="text-sm"><?= $success ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                            value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                            value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                            value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none">
                            <option value="">Select</option>
                            <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                        value="<?= isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : '' ?>">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none resize-none"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                            placeholder="Min 6 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="confirm_password" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal focus:border-teal outline-none"
                            placeholder="Re-enter password">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-teal hover:bg-teal-700 text-white font-semibold py-2.5 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">Already have an account?
                    <a href="login.php" class="text-orange font-semibold hover:underline">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
