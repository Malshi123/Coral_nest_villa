<?php
session_start();
require_once 'includes/db.php';

// Already logged in? go home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify checks the hashed password
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login | Coral Nest Villa';
include 'includes/header.php';
?>
<div class="container my-5" style="max-width:460px;">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
      <h3 class="section-title text-center">Login</h3>

      <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">Registration successful. Please login.</div>
      <?php endif; ?>
      <?php if (isset($_GET['loggedout'])): ?>
        <div class="alert alert-info">You have been logged out.</div>
      <?php endif; ?>
      <?php if (isset($_GET['need_login'])): ?>
        <div class="alert alert-warning">Please login to continue.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-coral w-100">Login</button>
      </form>
      <p class="text-center mt-3 mb-0">New here? <a href="register.php">Create an account</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
