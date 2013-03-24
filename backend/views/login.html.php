<!DOCTYPE html>
<html>
<head>
<title>Con-Nexus Login</title>
<link type="text/css" rel="stylesheet" href="/public/css/admin.css" />
</head>
<body>

<div id="login">

  <h1>Con-Nexus Login</h1>

  <?php if(isset($error)): ?>
  <div id="error"><?php echo $error; ?></div>
  <?php endif; ?>

  <form action="/login" method="post">
    <label for="username">User</label>
    <input name="username" type="text" value="" />
    <label for="password">Password</label>
    <input name="password" type="password" value="" />
    <button type="submit">Login</button>
  </form>

</div>

</body>
</html>
