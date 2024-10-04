<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "banking";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetchUsers($conn) {
    $sql = "SELECT name, balance FROM users";
    $result = $conn->query($sql);
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function getUserWithMinBalance($users) {
    $minUser = null;
    if (!empty($users)) {
        $minUser = $users[0];
        foreach ($users as $user) {
            if ($user['balance'] < $minUser['balance']) {
                $minUser = $user;
            }
        }
    }
    return $minUser;
}

$users = fetchUsers($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transfer'])) {
    $from = $_POST['from'];
    $to = $_POST['to'];
    $amount = $_POST['amount'];
    $sender = null;
    $receiver = null;

    foreach ($users as &$user) {
        if ($user['name'] === $from) {
            $sender = &$user;
        }
        if ($user['name'] === $to) {
            $receiver = &$user;
        }
    }

    if ($sender && $receiver) {
        // Validation
        if ($from === $to) {
            $message = "Transfer failed: Cannot transfer to the same account.";
        } elseif ($amount <= 0) {
            $message = "Transfer failed: Amount must be a positive number.";
        } elseif ($sender['balance'] < $amount) {
            $message = "Transfer failed: Insufficient balance.";
        } else {
            $sender['balance'] -= $amount;
            $receiver['balance'] += $amount;

            $updateSender = $conn->prepare("UPDATE users SET balance = ? WHERE name = ?");
            $updateSender->bind_param("ds", $sender['balance'], $from);
            $updateReceiver = $conn->prepare("UPDATE users SET balance = ? WHERE name = ?");
            $updateReceiver->bind_param("ds", $receiver['balance'], $to);
            
            // Execute updates and check for success
            if ($updateSender->execute() && $updateReceiver->execute()) {
                $message = "Transfer successful! ₹$amount transferred from $from to $to.";
                // Fetch users again only if the transaction is successful
                $users = fetchUsers($conn);
            } else {
                $message = "Transfer failed: Database update error.";
            }

            $updateSender->close();
            $updateReceiver->close();
        }
    } else {
        $message = "Transfer failed: Invalid user(s).";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $balance = $_POST['balance'];

    if ($balance < 0) {
        $registerMessage = "Registration failed: Balance cannot be negative.";
    } else {
        $insertUser = $conn->prepare("INSERT INTO users (name, balance) VALUES (?, ?)");
        $insertUser->bind_param("sd", $name, $balance);
        
        // Execute the insert and check for success
        if ($insertUser->execute()) {
            $registerMessage = "Registration successful! User $name has been added.";
            // Fetch users again only if the registration is successful
            $users = fetchUsers($conn);
        } else {
            $registerMessage = "Registration failed: Database insert error.";
        }

        $insertUser->close();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $balance = $_POST['balance'];

    if ($balance < 0) {
        $registerMessage = "Registration failed: Balance cannot be negative.";
    } else {
        $insertUser = $conn->prepare("INSERT INTO users (name, balance) VALUES (?, ?)");
        $insertUser->bind_param("sd", $name, $balance);
        
        if ($insertUser->execute()) {
            $registerMessage = "Registration successful! User $name has been added.";
            $users = fetchUsers($conn);
        } else {
            $registerMessage = "Registration failed: Database insert error.";
        }

        $insertUser->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Transfer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Fund Transfer</h1>
    
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (isset($registerMessage)): ?>
        <p style="color: green;"><?php echo $registerMessage; ?></p>
    <?php endif; ?>

    <form action="#" method="POST">
        <label for="from">From Account:</label>
        <select name="from" required>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['name']); ?>">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">No registered users available</option>
            <?php endif; ?>
        </select>
        
        <label for="to">To Account:</label>
        <select name="to" required>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['name']); ?>">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">No registered users available</option>
            <?php endif; ?>
        </select>
        
        <label for="amount">Amount:</label>
        <input type="number" name="amount" required>
        
        <input type="submit" name="transfer" value="Transfer">
    </form>

    <h2>Current Balances</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td>₹<?php echo htmlspecialchars($user['balance']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No users found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>User with Minimum Balance</h2>
    <?php $minUser = getUserWithMinBalance($users); ?>
    <?php if ($minUser): ?>
        <p><?php echo htmlspecialchars($minUser['name']); ?> - Balance: ₹<?php echo htmlspecialchars($minUser['balance']); ?></p>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>

    <h2>User Registration</h2>
    <form action="#" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        
        <label for="balance">Initial Balance:</label>
        <input type="number" name="balance" required>
        
        <input type="submit" name="register" value="Register">
    </form>
</div>

</body>
</html>
