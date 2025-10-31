<!DOCTYPE html>
<html>
<head>
    <title>Generate Password Hash</title>
</head>
<body>
    <h2>Generate Password Hash</h2>
    <form method="POST">
        <label for="password">Password:</label>
        <input type="text" id="password" name="password" value="admin123" required>
        <button type="submit">Generate Hash</button>
    </form>

    <?php
    if ($_POST) {
        $password = $_POST['password'];
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        echo "<h3>Generated Hash:</h3>";
        echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
        echo "<p><strong>Hash:</strong> " . htmlspecialchars($hash) . "</p>";
        echo "<p><strong>SQL Query:</strong></p>";
        echo "<pre>INSERT INTO users (username, email, password_hash) VALUES ('admin', 'admin@sqi.local', '" . $hash . "');</pre>";
    }
    ?>
</body>
</html>