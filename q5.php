<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 15px 0;
        }
        a {
            display: block;
            text-align: center;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
        .form-container {
            margin-top: 20px;
        }
        input[type="text"], input[type="number"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
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
        .tours-list {
            margin-top: 20px;
            display: none; /* Initially hidden */
        }
        .tour-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Tour Management System</h1>
        <ul>
            <li>
                <a href="register.php">Register</a>
            </li>
        </ul>

        <!-- Add Tour Form -->
        <div class="form-container">
            <h2>Add Tour</h2>
            <form action="" method="POST">
                <label for="tour_name">Tour Name:</label>
                <input type="text" name="tour_name" required>

                <label for="tour_description">Description:</label>
                <input type="text" name="tour_description" required>

                <label for="tour_price">Price:</label>
                <input type="number" name="tour_price" required step="0.01">

                <label for="tour_date">Date:</label>
                <input type="date" name="tour_date" required>

                <input type="submit" name="add_tour" value="Add Tour">
            </form>

            <?php
            // Database connection
            $host = "localhost";
            $user = "root"; // Update with your database username
            $password = ""; // Update with your database password
            $dbname = "tour_management"; // Update with your database name

            // Create connection
            $conn = new mysqli($host, $user, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Handle form submission
            if (isset($_POST['add_tour'])) {
                $tour_name = $_POST['tour_name'];
                $tour_description = $_POST['tour_description'];
                $tour_price = $_POST['tour_price'];
                $tour_date = $_POST['tour_date'];

                // Insert tour into database
                $stmt = $conn->prepare("INSERT INTO tours (tour_name, tour_description, tour_price, tour_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $tour_name, $tour_description, $tour_price, $tour_date);

                if ($stmt->execute()) {
                    echo "<p style='color:green'>Tour added successfully!</p>";
                } else {
                    echo "<p style='color:red'>Error: " . $stmt->error . "</p>";
                }

                $stmt->close();
            }

            // Display Tours
            $tours = [];
            if (isset($_POST['show_tours'])) {
                $result = $conn->query("SELECT * FROM tours");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $tours[] = $row; // Store tours in an array for display
                    }
                }
            }

            // Close connection
            $conn->close();
            ?>
        </div>

        <!-- Show Tours Button -->
        <form action="" method="POST">
            <input type="submit" name="show_tours" value="Show Tours" style="margin-top: 20px; width: 100%;">
        </form>

        <!-- Tours List -->
        <div class="tours-list" id="tours-list">
            <h2>List of Tours</h2>
            <?php if (!empty($tours)) : ?>
                <?php foreach ($tours as $row) : ?>
                    <div class='tour-item'>
                        <h3><?php echo htmlspecialchars($row['tour_name']); ?></h3>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['tour_description']); ?></p>
                        <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($row['tour_price']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($row['tour_date']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No tours available.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const showToursButton = document.querySelector('input[name="show_tours"]');
        const toursList = document.getElementById('tours-list');

        showToursButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission
            if (toursList.style.display === 'none' || toursList.style.display === '') {
                toursList.style.display = 'block';
                showToursButton.value = 'Hide Tours'; // Change button text
            } else {
                toursList.style.display = 'none';
                showToursButton.value = 'Show Tours'; // Change button text
            }
        });
    </script>
</body>
</html>
