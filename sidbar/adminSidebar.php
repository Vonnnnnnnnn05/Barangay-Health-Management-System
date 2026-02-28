<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Admin Sidebar -->
<aside class="fixed top-0 left-0 h-full w-64 bg-teal text-white shadow-xl z-50 flex flex-col no-print" id="sidebar">
    <!-- Brand -->
    <div class="p-5 border-b border-white/20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-heartbeat text-orange text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-sm leading-tight">Barangay Health</h2>
                <span class="text-xs text-white/70">Admin Panel</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mb-2">Main</p>
        <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'dashboard.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-chart-pie w-5 text-center"></i> Dashboard
        </a>

        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mt-4 mb-2">Management</p>
        <a href="manage_users.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'manage_users.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-users-cog w-5 text-center"></i> Manage Users
        </a>
        <a href="manage_patients.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'manage_patients.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-hospital-user w-5 text-center"></i> Patients
        </a>
        <a href="appointments.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'appointments.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-calendar-check w-5 text-center"></i> Appointments
        </a>
        <a href="medicines.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'medicines.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-pills w-5 text-center"></i> Medicine Inventory
        </a>
        <a href="immunizations.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'immunizations.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-syringe w-5 text-center"></i> Immunizations
        </a>

        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mt-4 mb-2">Reports</p>
        <a href="reports.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'reports.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-file-alt w-5 text-center"></i> Generate Reports
        </a>

        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mt-4 mb-2">Communication</p>
        <a href="chat.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'chat.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-comments w-5 text-center"></i> Messages
        </a>
        <a href="feedback.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'feedback.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-comment-dots w-5 text-center"></i> Feedback
        </a>
    </nav>

    <!-- User / Logout -->
    <div class="p-4 border-t border-white/20">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-orange rounded-full flex items-center justify-center text-sm font-bold">
                <?= strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></p>
                <p class="text-xs text-white/60">Administrator</p>
            </div>
        </div>
        <a href="../logout.php" class="flex items-center justify-center gap-2 w-full py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</aside>
