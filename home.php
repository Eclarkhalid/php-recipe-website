<?php
session_start();

$serverName = "localhost";
$dBUsername = "root";
$dBPassword = "";
$dBName = "php";

$conn = mysqli_connect($serverName, $dBUsername, $dBPassword, $dBName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to add a new recipe
function addRecipe($title, $content) {
    global $conn;
    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);
    $userId = $_SESSION['user_id'];

    $sql = "INSERT INTO posts (title, content, user_id) VALUES ('$title', '$content', '$userId')";
    mysqli_query($conn, $sql);
}

// Function to edit a recipe
function editRecipe($postId, $title, $content) {
    global $conn;
    $postId = mysqli_real_escape_string($conn, $postId);
    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);

    $sql = "UPDATE posts SET title = '$title', content = '$content' WHERE id = '$postId'";
    mysqli_query($conn, $sql);
}

// Function to delete a recipe
function deleteRecipe($postId) {
    global $conn;
    $postId = mysqli_real_escape_string($conn, $postId);

    $sql = "DELETE FROM posts WHERE id = '$postId'";
    mysqli_query($conn, $sql);
}

// Logout function
function logout() {
    session_destroy();
    header("Location: register.php");
    exit;
}

if (isset($_GET['logout'])) {
    logout();
}

if (isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_recipe'])) {
            $title = $_POST['title'];
            $content = $_POST['content'];
            addRecipe($title, $content);

            // Redirect to clear form data and prevent resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }

        if (isset($_POST['edit_recipe'])) {
            $postId = $_POST['post_id'];
            $title = $_POST['edit_title'];
            $content = $_POST['edit_content'];
            editRecipe($postId, $title, $content);

            // Redirect to clear form data and prevent resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }

        if (isset($_POST['delete_recipe'])) {
            $postId = $_POST['post_id'];
            deleteRecipe($postId);

            // Redirect to prevent resubmission of delete action
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipe Management</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Welcome to Recipe Management</h1>
        
        <div class="d-flex justify-contnet-around">
        <a href="?logout=true" class="btn btn-danger mb-3">Logout</a>
        <a href="authors.php" class="btn btn-primary mb-3 mx-4">Authors</a>
        </div>

        <h2>Add Recipe</h2>
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content:</label>
                <textarea name="content" class="form-control" required rows="5"></textarea>
            </div>
            <button type="submit" name="add_recipe" class="btn btn-primary">Add Recipe</button>
        </form>
    </div>

    <!-- Fetch and display posts -->
    <div class="container mt-5">
        <h2>Recipes</h2>
        <?php
        $query = "SELECT p.*, u.username FROM posts p
                  JOIN users u ON p.user_id = u.id";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<h3 class='card-title'>" . $row['title'] . "</h3>";
                echo "<p class='card-text'>" . $row['content'] . "</p>";
                echo "<p class='card-text'>Author: " . $row['username'] . "</p>";
                
                // Check if the logged-in user is the author
                if ($_SESSION['user_id'] == $row['user_id']) {
                    echo "<div class='d-flex justify-content-between align-items-center'>";
                    echo "<div>";
                    echo "<button onclick='openEditModal(" . $row['id'] . ")' class='btn btn-primary'>Edit Recipe</button>";
                    echo "</div>";
                    echo "<div>";
                    // Delete form
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='post_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='delete_recipe' class='btn btn-danger'>Delete Recipe</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<p class='text-muted'>You cannot edit or delete this post.</p>";
                }

                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "No recipes found.";
        }
        ?>
    </div>

    <!-- Modal HTML for editing recipe -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Recipe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeEditModal()"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit_post_id" name="post_id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Edit Title:</label>
                            <input type="text" id="edit_title" name="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_content" class="form-label">Edit Content:</label>
                            <textarea id="edit_content" name="edit_content" class="form-control" required rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_recipe" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript functions to open and close the modal
        function openEditModal(postId) {
            var modal = document.getElementById('editModal');
            var titleInput = document.getElementById('edit_title');
            var contentInput = document.getElementById('edit_content');
            var postTitle = document.querySelector('.card-title');
            var postContent = document.querySelector('.card-text');
            
            // Populate modal with recipe data
            titleInput.value = postTitle.textContent.trim();
            contentInput.value = postContent.textContent.trim();
            document.getElementById('edit_post_id').value = postId;

            modal.style.display = 'block';
        }

        function closeEditModal() {
            var modal = document.getElementById('editModal');
            modal.style.display = 'none';
        }
    </script>
</body>
</html>
