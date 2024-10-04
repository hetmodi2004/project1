<?php
// Database connection
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "product_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for inserting or updating a product
if (isset($_POST['submit'])) {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $rate = $_POST['rate'];

    $target_dir = ""; 
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validation
    if ($name == '' || $category == '' || $rate == '') {
        $message = "<p style='color:red'>Please fill all required fields!</p>";
    } elseif (!in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
        $message = "<p style='color:red'>Only JPG, JPEG, PNG, and GIF files are allowed!</p>";
    } elseif ($_FILES["image"]["size"] > 500000) {
        $message = "<p style='color:red'>File is too large. Maximum size allowed is 500KB.</p>";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            if ($id) {
                // Update existing product
                $stmt = $conn->prepare("UPDATE Product_Master SET Product_name=?, Description=?, Category=?, Product_Image=?, Rate=? WHERE id=?");
                $stmt->bind_param("ssssdi", $name, $description, $category, $target_file, $rate, $id);
                if ($stmt->execute()) {
                    $message = "<p style='color:green'>Product updated successfully</p>";
                } else {
                    $message = "<p style='color:red'>Error: " . $stmt->error . "</p>";
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO Product_Master (Product_name, Description, Category, Product_Image, Rate) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssd", $name, $description, $category, $target_file, $rate);
                if ($stmt->execute()) {
                    $message = "<p style='color:green'>New product added successfully</p>";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $message = "<p style='color:red'>Error: " . $stmt->error . "</p>";
                }
            }
            $stmt->close();
        } else {
            $message = "<p style='color:red'>Sorry, there was an error uploading your file.</p>";
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Product_Master WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "<p style='color:green'>Product deleted successfully</p>";
    } else {
        $message = "<p style='color:red'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
$product = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM Product_Master WHERE id=$id");
    $product = $result->fetch_assoc();
}

// Handle category search
$search_category = isset($_POST['search_category']) ? $_POST['search_category'] : '';
$product_query = "SELECT * FROM Product_Master";
if ($search_category && $search_category !== "All Items") {
    $product_query .= " WHERE Category = ?";
}

$stmt = $conn->prepare($product_query);
if ($search_category && $search_category !== "All Items") {
    $stmt->bind_param("s", $search_category);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-container {
            width: 50%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
        }
        input[type="file"] {
            margin: 5px 0 15px 0;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        .message {
            margin: 15px 0;
            font-size: 1.2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2><?php echo $product ? "Edit Product" : "Add New Product"; ?></h2>
    <?php if (isset($message)) { echo "<div class='message'>$message</div>"; } ?>
    <form action="#" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $product['id'] ?? ''; ?>">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?php echo $product['Product_name'] ?? ''; ?>" required>

        <label for="description">Description:</label>
        <input type="text" name="description" value="<?php echo $product['Description'] ?? ''; ?>">

        <label for="category">Category:</label>
        <select name="category" required>
            <option value="Electronics" <?php echo (isset($product) && $product['Category'] == 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
            <option value="Books" <?php echo (isset($product) && $product['Category'] == 'Books') ? 'selected' : ''; ?>>Books</option>
            <option value="Sports Items" <?php echo (isset($product) && $product['Category'] == 'Sports Items') ? 'selected' : ''; ?>>Sports Items</option>
        </select>

        <label for="rate">Rate:</label>
        <input type="number" name="rate" step="0.01" value="<?php echo $product['Rate'] ?? ''; ?>" required>

        <label for="image">Product Image:</label>
        <input type="file" name="image" <?php echo $product ? '' : 'required'; ?>>

        <input type="submit" name="submit" value="<?php echo $product ? 'Update Product' : 'Add Product'; ?>">
    </form>
</div>

<!-- Search by Category -->
<div class="form-container">
    <h2>Search Products by Category</h2>
    <form action="#" method="post">
        <label for="search_category">Select Category:</label>
        <select name="search_category" required>
            <option value="">Select a category</option>
            <option value="All Items" <?php echo ($search_category == 'All Items') ? 'selected' : ''; ?>>All Items</option>
            <option value="Electronics" <?php echo ($search_category == 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
            <option value="Books" <?php echo ($search_category == 'Books') ? 'selected' : ''; ?>>Books</option>
            <option value="Sports Items" <?php echo ($search_category == 'Sports Items') ? 'selected' : ''; ?>>Sports Items</option>
        </select>
        <input type="submit" value="Search">
    </form>
</div>

<!-- Display Products in Table -->
<div class="form-container">
    <h2>Product List</h2>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Image</th>
                <th>Rate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['Product_name']; ?></td>
                    <td><?php echo $row['Description']; ?></td>
                    <td><?php echo $row['Category']; ?></td>
                    <td><img src="<?php echo $row['Product_Image']; ?>" alt="Product Image" width="50"></td>
                    <td><?php echo $row['Rate']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
?>

</body>
</html>
