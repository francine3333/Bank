<?php
session_start();
require_once "config.php";

// Ensure database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check for user session or redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (isset($_COOKIE['auth_token'])) {@
        $_SESSION['username'] = $_COOKIE['auth_token'];
    } else {
        header("location: login.php");
        exit;
    }
}

$username = $_SESSION['username'];

// Handle the search functionality
if (isset($_POST['receiver_email'])) {
    $receiver_email = mysqli_real_escape_string($conn, $_POST['receiver_email']);
    $query = "SELECT * FROM users WHERE username = '$receiver_email' OR email = '$receiver_email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 0) {
        $error_message = "User not found.";
    } else {
        $_SESSION['receiver_email'] = $receiver_email;
    }
}

if (isset($_POST['send_message']) && !empty($_POST['message']) && isset($_POST['receiver'])) {
    $sender_username = $username;
    $receiver_username = mysqli_real_escape_string($conn, $_POST['receiver']);
    $message = mysqli_real_escape_string($conn, str_replace(["\r", "\n"], '', $_POST['message']));

    $stmt = $conn->prepare("INSERT INTO messages (sender_username, receiver_username, message, sent_at, status) VALUES (?, ?, ?, NOW(), 'sent')");
    if ($stmt) {
        $stmt->bind_param("sss", $sender_username, $receiver_username, $message);
        if ($stmt->execute()) {
            header("Location: chat.php?receiver_email=" . urlencode($receiver_username));
            exit;
        } else {
            $error_message = "Error sending message.";
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing the statement.";
    }
}

// Retrieve receiver email
$receiver_email = isset($_SESSION['receiver_email']) ? $_SESSION['receiver_email'] : (isset($_GET['receiver_email']) ? $_GET['receiver_email'] : null);
if (isset($_GET['receiver_email'])) {
    $_SESSION['receiver_email'] = $_GET['receiver_email'];
    $receiver_email = $_SESSION['receiver_email'];
}

// Retrieve messages
if ($receiver_email) {
    $query = "SELECT * FROM messages WHERE (sender_username = '$username' AND receiver_username = '$receiver_email') OR (sender_username = '$receiver_email' AND receiver_username = '$username') ORDER BY sent_at ASC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        $error_message = "Error retrieving messages: " . mysqli_error($conn);
    } else {
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}    

// Fetch online users
$query = "SELECT username FROM users WHERE account_status = 'active' AND username != '$username'";
$online_result = mysqli_query($conn, $query);
$online_users = mysqli_fetch_all($online_result, MYSQLI_ASSOC);

// Fetch usertype
$userQuery = "SELECT usertype FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$stmt->bind_result($usertype);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['message_id']) && isset($_POST['status'])) {
        $message_id = $_POST['message_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $message_id);
        $stmt->execute();
        echo "Status updated to $status.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="50x50" href="logo.png">
    <title>Messenger</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<style>
        /* Global styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        /* Header styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 30px;
            text-align: left;
            font-size: 1.8em;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-menu ul {
            list-style: none;
            padding: 0;
            margin: 10px 0;
            display: flex;
            justify-content: flex-start;
            gap: 20px;
        }

        .header-menu li a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .header-menu li a:hover {
            color: #1abc9c;
        }

        /* Main container with sidebar and chat area */
        .container {
            display: flex;
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            flex-direction: row;
            gap: 20px;
        }

        /* Sidebar styles for online users */
        .sidebar {
            width: 270px;
            background-color: #34495e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .sidebar h4 {
            color: #ecf0f1;
            margin-bottom: 20px;
            font-size: 1.2em;
            text-align: center;
        }

        .sidebar .online-users ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar .online-users li {
            color: #bdc3c7;
            margin: 15px 0;
            font-size: 1em;
            transition: color 0.3s ease;
        }

        .sidebar .online-users li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
            padding: 8px;
            display: block;
            border-radius: 6px;
        }

        .sidebar .online-users li a:hover {
            background-color: #1abc9c;
            color: white;
        }

        /* Chat Window */
        .chat-window {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 500px; 
        }

        /* Chat Header */
        .chat-header {
            font-size: 1.4em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        /* Chat Area (messages) */
        .chat-area {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        /* Message styles */
        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 20px;
            max-width: 75%;
            font-size: 14px;
            position: relative;
        }

        .message.sent {
            background-color: #1abc9c;
            color: white;
            align-self: flex-end;
            border-radius: 20px 20px 0 20px;
        }

        .message.received {
            background-color: #ecf0f1;
            color: #2c3e50;
            align-self: flex-start;
            border-radius: 20px 20px 20px 0;
        }

        .message p {
            margin: 0;
            line-height: 1.4;
        }

        .message small {
            position: absolute;
            bottom: 6px;
            right: 10px;
            font-size: 12px;
            color: #7f8c8d;
        }

        .message .status {
            position: absolute;
            bottom: 6px;
            left: 10px;
            font-size: 12px;
        }

        .status .badge {
            padding: 6px 12px;
            font-size: 10px;
            border-radius: 16px;
        }

        .status .badge-warning {
            background-color: #f39c12;
            color: white;
        }

        .status .badge-info {
            background-color: #3498db;
            color: white;
        }

        .status .badge-success {
            background-color: #2ecc71;
            color: white;
        }

        /* Emoji Button */
        .emoji-btn {
            background: none;
            border: none;
            color: #f39c12;
            font-size: 20px;
            cursor: pointer;
            margin-left: 12px;
            margin-bottom: 12px;
        }

        .emoji-picker {
            display: none;
            position: absolute;
            background-color: white;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            top: -35px;
            left: 0;
            z-index: 999;
            font-size: 16px;
        }

        .emoji-picker .emoji {
            cursor: pointer;
            padding: 5px;
            transition: background-color 0.3s ease;
        }

        .emoji-picker .emoji:hover {
            background-color: #ecf0f1;
        }

        /* Input area styling */
        .input-area {
            display: flex;
            flex-direction: column;
            margin-top: 20px;
            align-items: flex-start;
        }

        .input-area textarea {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border-radius: 15px;
            border: 1px solid #ccc;
            resize: none;
            box-sizing: border-box;
            outline: none;
            margin-bottom: 12px;
            transition: border-color 0.3s ease;
        }

        .input-area textarea:focus {
            border-color: #1abc9c;
        }

        .input-area button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            max-width: 400px;
        }

        .input-area button:hover {
            background-color: #16a085;
        }

        /* Sidebar Toggle Button for mobile */
        .toggle-sidebar-btn {
            background-color: #2c3e50;
            color: white;
            font-size: 24px;
            border: none;
            border-radius: 50%;
            padding: 12px 18px;
            cursor: pointer;
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 10px;
            }

            .sidebar {
                width: 100%;
                display: none;
            }

            .sidebar.active {
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 999;
            }

            .chat-window {
                margin-top: 20px;
                width: 100%;
                padding: 15px;
            }

            .toggle-sidebar-btn {
                display: block;
                margin: 10px;
            }

            .input-area button {
                max-width: 100%;
            }

            .message {
                max-width: 90%;
            }

            .message.sent {
                align-self: flex-end;
            }

            .message.received {
                align-self: flex-start;
            }
        }
    </style>
<body>
<header>
    <h2>Wealth Finance Management</h2>
    <div class="header-menu">
        <ul class="nav">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="chat.php">Chat</a></li>
            <?php if ($usertype === "ADMINISTRATOR"): ?>
                <li><a href="manage-user.php">Accounts Management</a></li>
            <?php endif; ?>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</header>

<div class="container">
    <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar" id="sidebar">
        <h4>Online Users</h4>
        <div class="online-users">
            <ul>
                <?php foreach ($online_users as $user): ?>
                    <li><a href="?receiver_email=<?= urlencode($user['username']) ?>"><?= htmlspecialchars($user['username']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="chat-window">
        <div class="chat-header">Chat with <?= htmlspecialchars($receiver_email) ?></div>
        <div class="chat-area">
            <?php if (isset($messages) && !empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?= ($message['sender_username'] == $username) ? 'sent' : 'received' ?>" id="message-<?= $message['id'] ?>">
                        <p><?= htmlspecialchars($message['message']) ?></p>
                        <small><?= date("H:i", strtotime($message['sent_at'])) ?></small>
                        <div class="status">
                            <?php
                                if ($message['status'] == 'sent') {
                                    echo "<span class='badge badge-warning'>Sent</span>";
                                } elseif ($message['status'] == 'delivered') {
                                    echo "<span class='badge badge-info'>Delivered</span>";
                                } elseif ($message['status'] == 'seen') {
                                    echo "<span class='badge badge-success'>Seen</span>";
                                }
                            ?>
                        </div>

                        <!-- Emoji Button -->
                        <button class="emoji-btn" onclick="toggleEmojiPicker(<?= $message['id'] ?>)">😊</button>

                        <!-- Emoji Picker (Hidden by default) -->
                        <div class="emoji-picker" id="emoji-picker-<?= $message['id'] ?>" style="display: none;">
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '😊')">😊</span>
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '😂')">😂</span>
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '❤️')">❤️</span>
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '👍')">👍</span>
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '😢')">😢</span>
                            <span class="emoji" onclick="addEmojiToMessage(<?= $message['id'] ?>, '😡')">😡</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No messages yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="input-area">
        <form method="POST" action="">
            <textarea name="message" rows="3" placeholder="Type your message..." required></textarea>
            <input type="hidden" name="receiver" value="<?= htmlspecialchars($receiver_email) ?>" />
            <button type="submit" name="send_message" class="btn-send">Send</button>
        </form>
    </div>
</div>
<script>
    const chatArea = document.querySelector('.chat-area');

    // Automatically scroll to the latest message
    function scrollToBottom() {
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    // Call scrollToBottom when the page loads and when a new message is added
    window.addEventListener('load', scrollToBottom);
    
    // Ensure chat window scrolls to bottom whenever new content is added
    const observer = new MutationObserver(() => {
        scrollToBottom();
    });

    observer.observe(chatArea, { childList: true });

    // Function to handle scrolling up to view older messages
    chatArea.addEventListener('scroll', function () {
        if (chatArea.scrollTop === 0) {
            // Load previous messages (implement pagination or fetch logic here if needed)
            console.log("Scrolled to top, load older messages...");
        }
    });
</script>
<script>
    const chatArea = document.querySelector('.chat-area');
    chatArea.scrollTop = chatArea.scrollHeight;

    const observer = new MutationObserver(() => {
        chatArea.scrollTop = chatArea.scrollHeight;
    });
    observer.observe(chatArea, { childList: true });

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    // Toggle the emoji picker visibility for a message
    function toggleEmojiPicker(messageId) {
        const emojiPicker = document.getElementById('emoji-picker-' + messageId);
        emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
    }

    // Add emoji to the message content
    function addEmojiToMessage(messageId, emoji) {
        const messageContainer = document.getElementById('message-' + messageId);
        const messageText = messageContainer.querySelector('p');
        messageText.innerHTML += ' ' + emoji;

        const emojiPicker = document.getElementById('emoji-picker-' + messageId);
        emojiPicker.style.display = 'none';

        // Optionally, you can save this emoji reaction to the database via AJAX
    }
</script>
<script>
        // Toggle Sidebar for mobile view
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
