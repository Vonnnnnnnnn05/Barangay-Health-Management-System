<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Patient Sidebar -->
<aside class="fixed top-0 left-0 h-full w-64 bg-teal text-white shadow-xl z-50 flex flex-col no-print" id="sidebar">
    <!-- Brand -->
    <div class="p-5 border-b border-white/20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-heartbeat text-orange text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-sm leading-tight">Barangay Health</h2>
                <span class="text-xs text-white/70">Patient Portal</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mb-2">Main</p>
        <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'dashboard.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-home w-5 text-center"></i> Dashboard
        </a>
        <a href="profile.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'profile.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-user-edit w-5 text-center"></i> My Profile
        </a>

        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mt-4 mb-2">Health</p>
        <a href="appointments.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'appointments.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-calendar-check w-5 text-center"></i> My Appointments
        </a>
        <a href="immunizations.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'immunizations.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-syringe w-5 text-center"></i> Immunization Schedule
        </a>
        <a href="my_medicines.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'my_medicines.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-pills w-5 text-center"></i> My Medicines
        </a>

        <p class="text-xs text-white/50 uppercase tracking-wider px-3 mt-4 mb-2">Communication</p>
        <a href="chat.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'chat.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-comments w-5 text-center"></i> Messages
        </a>
        <a href="feedback.php" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm <?= $current_page == 'feedback.php' ? 'bg-white/15 border-l-3 border-orange' : 'hover:bg-white/10' ?>">
            <i class="fas fa-comment-dots w-5 text-center"></i> Send Feedback
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
                <p class="text-xs text-white/60">Patient</p>
            </div>
        </div>
        <a href="../logout.php" class="flex items-center justify-center gap-2 w-full py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</aside>
