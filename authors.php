<?php
$serverName = "localhost";
$dBUsername = "root";
$dBPassword = "";
$dBName = "php";

$conn = mysqli_connect($serverName, $dBUsername, $dBPassword, $dBName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT u.username, COUNT(p.id) AS recipe_count FROM users u
          LEFT JOIN posts p ON u.id = p.user_id
          GROUP BY u.username";
$result = mysqli_query($conn, $query);

$authors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $authors[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Authors</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Authors</h1>
        
        <a href="home.php" class="btn btn-primary mb-3">Back to Home</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Author</th>
                    <th>Number of Recipes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $index => $author) { ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $author['username']; ?></td>
                        <td><?php echo $author['recipe_count']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
