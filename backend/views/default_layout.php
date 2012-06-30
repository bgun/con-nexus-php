<!DOCTYPE html>
<html>
<head>
	<title>Con-Nexus</title>
	<link type="text/css" rel="stylesheet" href="/public/css/admin.css" />
	<script src="/public/js/jquery-1.7.1.min.js"></script>
	<script src="/public/js/underscore-min.js"></script>
	<script src="/public/js/jsrender.js"></script>
  <script src="/public/js/admin.js"></script>
  <script>
    var ConventionID = <?php echo $convention["ConventionID"]; ?>;
  </script>
</head>
<body>

<header id="header">
  <h1>Con-Nexus Admin</h1>
  <?php if(isset($convention)): ?>
  <h2><?php echo $convention["Name"]; ?></h2>
  <nav id="tab-menu">
    <ul>
      <li><a href="<?php echo url_for('admin', 'home'); ?>">Admin Home</a></li>
      <li><a href="<?php echo url_for('admin', $convention["ConventionID"], 'events'); ?>">Events</a></li>
      <li><a href="<?php echo url_for('admin', $convention["ConventionID"], 'guests'); ?>">Guests</a></li>
    </ul>
  </nav>
  <?php endif; ?>
  <nav id="user-menu">
    <ul>
      <li>User: <span class="username"><?php echo $user['name']; ?></span></li>
      <li><a href="<?php echo url_for('logout'); ?>">Logout</a></li>
    </ul>
  </nav>
  <div class="clear"></div>
</header>

<div id="page">
<?php echo $content; ?>
</div>

</body>
</html>
