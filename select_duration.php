<?php
session_start();

if (!isset($_GET['book_id']) || !isset($_GET['book_title']) || !isset($_SESSION['username'])) {
  die("Invalid request.");
}

$book_id = (int)$_GET['book_id'];
$book_title = htmlspecialchars($_GET['book_title']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Rent Duration</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    body {
      background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 50%, #0f0f23 100%);
      min-height: 100vh;
      color: #e0e0e0;
    }
    .duration-container {
      max-width: 420px;
      margin: 48px auto;
      background: rgba(42, 42, 66, 0.97);
      border-radius: 22px;
      box-shadow: 0 12px 36px rgba(0,0,0,0.22), 0 1px 0 rgba(95,158,160,0.08);
      border: 1.5px solid rgba(95, 158, 160, 0.22);
      padding: 36px 28px 28px 28px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }
    .duration-header {
      width: 100%;
      text-align: center;
      margin-bottom: 18px;
    }
    .duration-header h1 {
      font-size: 1.7em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0 0 8px 0;
      animation: glow 2s ease-in-out infinite alternate;
    }
    .duration-form {
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 18px;
    }
    .duration-label {
      color: #7fb3b5;
      font-size: 1.1em;
      font-weight: 600;
      margin-bottom: 8px;
      letter-spacing: 0.5px;
    }
    .duration-options {
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 100%;
      align-items: flex-start;
    }
    .duration-radio {
      accent-color: #5f9ea0;
      margin-right: 10px;
      transform: scale(1.2);
    }
    .duration-option-label {
      font-size: 1.05em;
      color: #e0e0e0;
      cursor: pointer;
      margin-bottom: 0;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: color 0.2s;
    }
    .duration-option-label:hover {
      color: #7fb3b5;
    }
    .duration-actions {
      display: flex;
      gap: 16px;
      width: 100%;
      justify-content: center;
      margin-top: 10px;
    }
    .duration-actions button {
      background: #5f9ea0;
      color: #1a1a1a;
      padding: 13px 26px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1em;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(95, 158, 160, 0.18);
      transition: all 0.3s;
    }
    .duration-actions button:hover {
      background: #7fb3b5;
      color: #232b2b;
      box-shadow: 0 8px 25px rgba(95, 158, 160, 0.28);
      transform: translateY(-2px);
    }
    @media (max-width: 600px) {
      .duration-container { padding: 14px 4vw; }
    }
  </style>
</head>
<body>
  <div class="duration-container">
    <div class="duration-header">
      <h1>Select Borrow Duration</h1>
    </div>
    <form class="duration-form" action="book_borrow.php" method="POST">
      <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
      <input type="hidden" name="book_title" value="<?php echo $book_title; ?>">
      <div class="duration-label">Select Duration:</div>
      <div class="duration-options">
        <label class="duration-option-label"><input class="duration-radio" type="radio" name="rent_duration" value="7" required> 7 Days</label>
        <label class="duration-option-label"><input class="duration-radio" type="radio" name="rent_duration" value="14"> 14 Days</label>
        <label class="duration-option-label"><input class="duration-radio" type="radio" name="rent_duration" value="21"> 21 Days</label>
      </div>
      <div class="duration-actions">
        <button type="submit">Confirm Borrow</button>
        <a href="user_dashboard.php" onclick="return confirm('Are you sure you want to cancel borrowing this book?');"><button type="button">Cancel</button></a>
      </div>
    </form>
  </div>
</body>
</html>
