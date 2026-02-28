<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') { header("Location: ../login.php"); exit(); }
require_once '../conn.php';
require_once '../header.php';

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    if ($message) {
        mysqli_query($conn, "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES ($uid, $receiver_id, '$message')");
    }
    header("Location: chat.php?user=$receiver_id");
    exit();
}

$active_chat = isset($_GET['user']) ? (int)$_GET['user'] : 0;
if ($active_chat) {
    mysqli_query($conn, "UPDATE chat_messages SET is_read=1 WHERE sender_id=$active_chat AND receiver_id=$uid");
}

$users = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM chat_messages WHERE sender_id=u.id AND receiver_id=$uid AND is_read=0) as unread FROM users u WHERE u.id != $uid AND u.role IN ('admin','health_worker') AND u.status='active' ORDER BY u.first_name");

$messages = [];
if ($active_chat) {
    $msgs = mysqli_query($conn, "SELECT cm.*, CONCAT(u.first_name,' ',u.last_name) as sender_name FROM chat_messages cm JOIN users u ON cm.sender_id=u.id WHERE (cm.sender_id=$uid AND cm.receiver_id=$active_chat) OR (cm.sender_id=$active_chat AND cm.receiver_id=$uid) ORDER BY cm.created_at ASC");
    while ($m = mysqli_fetch_assoc($msgs)) $messages[] = $m;
}

renderHeader('Messages');
?>
<body class="bg-beige">
<?php include '../sidbar/patientSidebar.php'; ?>
<main class="ml-64 p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Messages</h1>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden flex" style="height:calc(100vh - 160px)">
        <div class="w-72 border-r overflow-y-auto">
            <div class="p-4 border-b"><h3 class="font-semibold text-sm text-gray-700">Health Staff</h3></div>
            <?php while($u=mysqli_fetch_assoc($users)): ?>
            <a href="?user=<?=$u['id']?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b <?=$active_chat==$u['id']?'bg-teal-50 border-l-4 border-l-teal':''?>">
                <div class="w-9 h-9 rounded-full bg-teal flex items-center justify-center text-white text-sm font-bold"><?=strtoupper(substr($u['first_name'],0,1))?></div>
                <div class="flex-1 min-w-0"><p class="text-sm font-medium truncate"><?=htmlspecialchars($u['first_name'].' '.$u['last_name'])?></p><p class="text-xs text-gray-400"><?=ucfirst(str_replace('_',' ',$u['role']))?></p></div>
                <?php if($u['unread']>0): ?><span class="bg-orange text-white text-xs rounded-full px-2 py-0.5"><?=$u['unread']?></span><?php endif; ?>
            </a>
            <?php endwhile; ?>
        </div>

        <div class="flex-1 flex flex-col">
            <?php if ($active_chat): $chat_user=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$active_chat")); ?>
            <div class="p-4 border-b flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-teal flex items-center justify-center text-white text-sm font-bold"><?=strtoupper(substr($chat_user['first_name'],0,1))?></div>
                <div><p class="font-semibold text-sm"><?=htmlspecialchars($chat_user['first_name'].' '.$chat_user['last_name'])?></p><p class="text-xs text-gray-400"><?=ucfirst(str_replace('_',' ',$chat_user['role']))?></p></div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatBox">
                <?php foreach ($messages as $m): $mine = $m['sender_id'] == $uid; ?>
                <div class="flex <?=$mine?'justify-end':'justify-start'?>">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl text-sm <?=$mine?'bg-teal text-white rounded-br-md':'bg-gray-100 text-gray-800 rounded-bl-md'?>">
                        <p><?=htmlspecialchars($m['message'])?></p>
                        <p class="text-[10px] mt-1 <?=$mine?'text-teal-200':'text-gray-400'?>"><?=date('h:i A',strtotime($m['created_at']))?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" class="p-4 border-t flex items-center gap-3">
                <input type="hidden" name="receiver_id" value="<?=$active_chat?>">
                <input type="text" name="message" placeholder="Type a message..." required class="flex-1 px-4 py-2 border border-gray-300 rounded-full text-sm outline-none focus:ring-2 focus:ring-teal" autocomplete="off">
                <button type="submit" class="bg-teal text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-teal-700 transition"><i class="fas fa-paper-plane text-sm"></i></button>
            </form>
            <?php else: ?>
            <div class="flex-1 flex items-center justify-center text-gray-400"><div class="text-center"><i class="fas fa-comments text-5xl mb-3"></i><p>Select a health staff member to start messaging</p></div></div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>const cb=document.getElementById('chatBox');if(cb)cb.scrollTop=cb.scrollHeight;</script>
</body>
</html>
