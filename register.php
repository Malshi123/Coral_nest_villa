<?php
session_start();
require_once 'includes/db.php';

$errors = [];
$name = $email = $contact = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $contact  = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if ($name === '' || strlen($name) < 3)            $errors[] = 'Name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $contact))       $errors[] = 'Contact number must be 10 digits.';
    if (strlen($password) < 6)                        $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                       $errors[] = 'Passwords do not match.';

    // Email already used?
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'This email is already registered.';
    }

    // Save user (password hashed)
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, contact_number, password, role) VALUES (?, ?, ?, ?, 'guest')");
        $stmt->execute([$name, $email, $contact, $hash]);
        header('Location: login.php?registered=1');
        exit;
    }
}

$page_title = 'Register | Coral Nest Villa';
include 'includes/header.php';
?>
<div class="container my-5" style="max-width:520px;">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
      <h3 class="section-title text-center">Create Account</h3>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul></div>
      <?php endif; ?>

      <div id="js-errors" class="alert alert-danger d-none"></div>

      <form method="post" id="registerForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" id="contact" class="form-control" placeholder="0771234567" value="<?= htmlspecialchars($contact) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm" class="form-control">
        </div>
        <button type="submit" class="btn btn-coral w-100">Register</button>
      </form>
      <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>
<script src="js/auth.js"></script>
<?php include 'includes/footer.php'; ?>
