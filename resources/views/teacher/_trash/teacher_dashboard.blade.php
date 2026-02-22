<?php
session_start();
if ($_SESSION['user_type'] != 'teacher') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>لوحة تحكم الأستاذ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2 class="text-center">لوحة تحكم الأستاذ</h2>
    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card p-4">
          <h5>إدارة الحصص</h5>
          <a href="manage_classes.php" class="btn btn-success w-100">إدارة الحصص</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4">
          <h5>إدارة الطلاب</h5>
          <a href="manage_students.php" class="btn btn-success w-100">إدارة الطلاب</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
