<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver = (int)$_POST['receiver_id'];
    $msg = mysqli_real_escape_string($conn, trim($_POST['message']));
    if (!empty($msg)) {
        mysqli_query($conn, "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES ({$_SESSION['user_id']}, $receiver, '$msg')");
    }
    header("Location: chat.php?user=$receiver");
    exit();
}

// Get users to chat with
$users = mysqli_query($conn, "SELECT u.*, 
    (SELECT COUNT(*) FROM chat_messages WHERE sender_id=u.id AND receiver_id={$_SESSION['user_id']} AND is_read=0) as unread
    FROM users u WHERE u.id != {$_SESSION['user_id']} ORDER BY u.first_name ASC");

$selected_user = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$messages = [];
if ($selected_user) {
    // Mark as read
    mysqli_query($conn, "UPDATE chat_messages SET is_read=1 WHERE sender_id=$selected_user AND receiver_id={$_SESSION['user_id']}");
    $messages = mysqli_query($conn, "SELECT * FROM chat_messages WHERE (sender_id={$_SESSION['user_id']} AND receiver_id=$selected_user) OR (sender_id=$selected_user AND receiver_id={$_SESSION['user_id']}) ORDER BY created_at ASC");
    $sel_user_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$selected_user"));
}

renderHeader('Messages');
?>
<body class="bg-beige">
<?php include '../sidbar/adminSidebar.php'; ?>

<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-comments text-teal mr-2"></i>Messages</h1>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden flex" style="height: calc(100vh - 160px);">
        <!-- Users List -->
        <div class="w-72 border-r border-gray-200 overflow-y-auto">
            <div class="p-4 border-b"><p class="text-sm font-semibold text-gray-600">Conversations</p></div>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <a href="?user=<?= $u['id'] ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100 <?= $selected_user == $u['id'] ? 'bg-teal-50' : '' ?>">
                <div class="w-9 h-9 bg-teal rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= ucfirst(str_replace('_',' ',$u['role'])) ?></p>
                </div>
                <?php if ($u['unread'] > 0): ?>
                <span class="bg-orange text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $u['unread'] ?></span>
                <?php endif; ?>
            </a>
            <?php endwhile; ?>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <?php if ($selected_user && isset($sel_user_info)): ?>
            <div class="p-4 border-b bg-gray-50 flex items-center gap-3">
                <div class="w-9 h-9 bg-teal rounded-full flex items-center justify-center text-white text-xs font-bold">
                    <?= strtoupper(substr($sel_user_info['first_name'],0,1).substr($sel_user_info['last_name'],0,1)) ?>
                </div>
                <div>
                    <p class="font-semibold text-sm"><?= htmlspecialchars($sel_user_info['first_name'].' '.$sel_user_info['last_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= ucfirst(str_replace('_',' ',$sel_user_info['role'])) ?></p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatMessages">
                <?php while ($m = mysqli_fetch_assoc($messages)): ?>
                <div class="flex <?= $m['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-xs px-4 py-2 rounded-2xl text-sm <?= $m['sender_id'] == $_SESSION['user_id'] ? 'bg-teal text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md' ?>">
                        <p><?= htmlspecialchars($m['message']) ?></p>
                        <p class="text-xs mt-1 <?= $m['sender_id'] == $_SESSION['user_id'] ? 'text-white/60' : 'text-gray-400' ?>"><?= date('h:i A', strtotime($m['created_at'])) ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <form method="POST" class="p-4 border-t flex gap-3">
                <input type="hidden" name="receiver_id" value="<?= $selected_user ?>">
                <input type="text" name="message" placeholder="Type a message..." required autocomplete="off"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full text-sm focus:ring-2 focus:ring-teal outline-none">
                <button type="submit" name="send_message" class="bg-teal hover:bg-teal-700 text-white px-5 py-2 rounded-full text-sm transition">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <?php else: ?>
            <div class="flex-1 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <i class="fas fa-comments text-5xl mb-3"></i>
                    <p>Select a conversation to start messaging</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
const chat = document.getElementById('chatMessages');
if (chat) chat.scrollTop = chat.scrollHeight;

// Auto refresh every 5 seconds
<?php if ($selected_user): ?>
setInterval(() => { location.reload(); }, 10000);
<?php endif; ?>
</script>
</body>
</html>
