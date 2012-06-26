<!DOCTYPE html>
<html>
<head>
<title>Con-Nexus Login</title>
<link type="text/css" rel="stylesheet" href="/backend/public/css/admin.css" />
</head>
<body>

<?php if(isset($error)): ?>
<div id="error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="./login" method="post">
  <label for="username">User</label>
  <input name="username" type="text" value="" />
  <label for="password">Password</label>
  <input name="password" type="password" value="" />
  <button type="submit">Login</button>
</form>

</body>
</html>
