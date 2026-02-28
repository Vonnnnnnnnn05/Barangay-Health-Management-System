<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
require_once '../conn.php';

if (!isset($_GET['id'])) { echo "No appointment specified."; exit(); }
$id = (int)$_GET['id'];
$appt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) as patient_name, u.email, u.phone, u.address,
    CONCAT(hw.first_name,' ',hw.last_name) as hw_name
    FROM appointments a JOIN users u ON a.patient_id=u.id LEFT JOIN users hw ON a.health_worker_id=hw.id WHERE a.id=$id"));
if (!$appt) { echo "Appointment not found."; exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Slip</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print { .no-print { display: none !important; } body { padding: 0; } }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <!-- Print Button -->
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="bg-[#0F766E] hover:bg-[#0A5550] text-white px-6 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-print mr-2"></i> Print Slip
        </button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition ml-2">
            <i class="fas fa-times mr-2"></i> Close
        </button>
    </div>

    <!-- Appointment Slip -->
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-[#0F766E] text-white p-6 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-heartbeat text-2xl text-[#F97316]"></i>
            </div>
            <h1 class="text-xl font-bold">Barangay Health Center</h1>
            <p class="text-white/70 text-sm">Appointment Slip</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2 bg-[#F5F5DC] px-4 py-2 rounded-full">
                    <i class="fas fa-hashtag text-[#0F766E]"></i>
                    <span class="font-bold text-[#0F766E]">APPT-<?= str_pad($appt['id'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-user text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Patient Name</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($appt['patient_name']) ?></p>
                    </div>
                </div>

                <div class="flex items-start gap-3 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-calendar text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Appointment Date & Time</p>
                        <p class="font-semibold text-gray-800"><?= date('F d, Y', strtotime($appt['appointment_date'])) ?> at <?= date('h:i A', strtotime($appt['appointment_time'])) ?></p>
                    </div>
                </div>

                <div class="flex items-start gap-3 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-clipboard text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Purpose</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($appt['purpose']) ?></p>
                    </div>
                </div>

                <div class="flex items-start gap-3 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info-circle text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="font-semibold text-gray-800"><?= ucfirst($appt['status']) ?></p>
                    </div>
                </div>

                <?php if ($appt['hw_name']): ?>
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-user-nurse text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Assigned Health Worker</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($appt['hw_name']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($appt['notes']): ?>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-[#0F766E]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-sticky-note text-[#0F766E] text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="text-sm text-gray-700"><?= htmlspecialchars($appt['notes']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-4 text-center">
            <p class="text-xs text-gray-500">Please present this slip upon your visit.</p>
            <p class="text-xs text-gray-400 mt-1">Printed on <?= date('F d, Y h:i A') ?></p>
        </div>
    </div>
</body>
</html>
