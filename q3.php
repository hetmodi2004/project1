<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "BookStore";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$bookToUpdate = null;

// Insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insert'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];

    if (!empty($title) && !empty($author) && !empty($price)) {
        $sql = "INSERT INTO Books (title, author, price) VALUES ('$title', '$author', '$price')";
        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $message = "All fields are required!";
    }
}

// Update
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM Books WHERE id='$id'";
    $result = $conn->query($sql);
    $bookToUpdate = $result->fetch_assoc();
}

// Handle the actual update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];

    if (!empty($title) && !empty($author) && !empty($price)) {
        $sql = "UPDATE Books SET title='$title', author='$author', price='$price' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $message = "All fields are required!";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Books WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $message = "Error deleting record: " . $conn->error;
    }
}

// Fetch all records
$sql = "SELECT * FROM Books";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    $message = "Error fetching records: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Book Store</title>
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
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
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
        .delete-button {
            display: inline-block;
            padding: 5px 10px;
            background-color: #dc3545; /* Bootstrap danger color */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .delete-button:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .update-button {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .update-button:hover {
            background-color: #0069d9; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Online Book Store</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo $bookToUpdate ? $bookToUpdate['title'] : ''; ?>" required>

            <label>Author:</label>
            <input type="text" name="author" value="<?php echo $bookToUpdate ? $bookToUpdate['author'] : ''; ?>" required>

            <label>Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $bookToUpdate ? $bookToUpdate['price'] : ''; ?>" required>

            <?php if ($bookToUpdate): ?>
                <input type="hidden" name="id" value="<?php echo $bookToUpdate['id']; ?>">
                <input type="submit" name="update" value="Update Book">
            <?php else: ?>
                <input type="submit" name="insert" value="Add Book">
            <?php endif; ?>
        </form>

        <h2>Book List</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($result) && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['author']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>" class="update-button">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="delete-button">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
