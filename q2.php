<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Empdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$edit_mode = false;
$current_id = null;
$current_data = [];

// Insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insert'])) {
    $E_name = $_POST['E_name'];
    $Contact_No = $_POST['Contact_No'];
    $Designation = $_POST['Designation'];
    $Salary = $_POST['Salary'];

    error_log(print_r($_POST, true)); 

    if (!empty($E_name) && !empty($Contact_No) && !empty($Designation) && !empty($Salary)) {
        if (is_numeric($Contact_No) && strlen($Contact_No) == 10) {
            $sql = "INSERT INTO Emp (E_name, Contact_No, Designation, Salary) VALUES ('$E_name', '$Contact_No', '$Designation', '$Salary')";
            if ($conn->query($sql) === TRUE) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $message = "Error: " . $sql . "<br>" . $conn->error; 
            }
        } else {
            $message = "Contact No must be a 10-digit number!";
        }
    } else {
        $message = "All fields are required!";
    }
}

// Update
if (isset($_POST['update'])) {
    $update_id = $_POST['id'];
    $E_name = $_POST['E_name'];
    $Contact_No = $_POST['Contact_No'];
    $Designation = $_POST['Designation'];
    $Salary = $_POST['Salary'];

    $sql = "UPDATE Emp SET E_name='$E_name', Contact_No='$Contact_No', Designation='$Designation', Salary='$Salary' WHERE id=$update_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $message = "Error updating record: " . $conn->error;
    }
}

// Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM Emp WHERE id=$delete_id";
    if ($conn->query($sql) === TRUE) {
        // The message for deletion has been removed
        // $message = "Record deleted successfully!"; // Commented out or removed
    } else {
        $message = "Error deleting record: " . $conn->error;
    }
}

// Edit
if (isset($_GET['edit'])) {
    $current_id = $_GET['edit'];
    $sql = "SELECT * FROM Emp WHERE id=$current_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $current_data = $result->fetch_assoc();
        $edit_mode = true; // Set edit mode to true
    }
}

// Fetch all records
$sql = "SELECT * FROM Emp";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Information</title>
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
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"], .update-button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover, .update-button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .message {
            margin-top: 20px;
            font-size: 16px;
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        .update-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .update-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Employee Information</h2>
        
        <!-- Display messages -->
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form name="empForm" method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $edit_mode ? $current_data['id'] : ''; ?>"> <!-- Hidden field for ID in edit mode -->
            <label>Name:</label>
            <input type="text" name="E_name" value="<?php echo $edit_mode ? $current_data['E_name'] : ''; ?>" required>

            <label>Contact No:</label>
            <input type="text" name="Contact_No" value="<?php echo $edit_mode ? $current_data['Contact_No'] : ''; ?>" required>

            <label>Designation:</label>
            <input type="text" name="Designation" value="<?php echo $edit_mode ? $current_data['Designation'] : ''; ?>" required>

            <label>Salary:</label>
            <input type="number" step="0.01" name="Salary" value="<?php echo $edit_mode ? $current_data['Salary'] : ''; ?>" required>

            <input type="submit" name="<?php echo $edit_mode ? 'update' : 'insert'; ?>" value="<?php echo $edit_mode ? 'Update Employee' : 'Insert Employee'; ?>">
        </form>

        <h2>Employee List</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact No</th>
                    <th>Designation</th>
                    <th>Salary</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['E_name']; ?></td>
                            <td><?php echo $row['Contact_No']; ?></td>
                            <td><?php echo $row['Designation']; ?></td>
                            <td><?php echo $row['Salary']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>" class="update-button">Edit</a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="delete-button">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
