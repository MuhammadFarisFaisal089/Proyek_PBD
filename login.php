<?php
require_once 'auth.php';
require_once 'koneksi.php';

if (!empty($_SESSION['status_login'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    $statement = $koneksi->prepare('SELECT iduser, username, password, idrole FROM user WHERE username = ? LIMIT 1');
    $statement->bind_param('s', $username);
    $statement->execute();
    $result = $statement->get_result();
    $data = $result->fetch_assoc();
    $statement->close();

    if ($data && password_verify($password, $data['password'])) {
        session_regenerate_id(true);
        $_SESSION['status_login'] = true;
        $_SESSION['iduser'] = (int) $data['iduser'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['idrole'] = (int) $data['idrole'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login POS & Inventori</title></head>
<body>
    <div style="width: 300px; margin: 100px auto; border: 1px solid #ccc; padding: 20px;">
        <h2>Login Sistem</h2>
        <?php if (isset($error)) { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
            <label>Username:</label><br>
            <input type="text" name="username" required style="width: 100%; margin-bottom: 10px;"><br>
            
            <label>Password:</label><br>
            <input type="password" name="password" required style="width: 100%; margin-bottom: 20px;"><br>
            
            <button type="submit" name="btn_login" style="width: 100%; padding: 10px;">Masuk</button>
        </form>
    </div>
</body>
</html>