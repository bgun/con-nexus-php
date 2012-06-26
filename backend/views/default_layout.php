<!DOCTYPE html>
<html>
<head>
	<title>Con-Nexus</title>
	<link type="text/css" rel="stylesheet" href="/backend/public/css/admin.css" />
	<script src="/backend/public/js/jquery-1.7.1.min.js"></script>
	<script src="/backend/public/js/underscore-min.js"></script>
	<script src="/backend/public/js/jsrender.js"></script>
  <script src="/backend/public/js/admin.js"></script>
</head>
<body>

<header id="header">
  <h1>Con-Nexus Admin</h1>
  <h2><?php echo $convention["Name"]; ?></h2>
  <nav id="tab-menu">
    <ul>
      <li><a href="<?php echo url_for('admin', $convention["ConventionID"], 'events'); ?>">Events</a></li>
      <li><a href="<?php echo url_for('admin', $convention["ConventionID"], 'guests'); ?>">Guests</a></li>
    </ul>
  </nav>
</header>

<?php echo $content; ?>

</body>
</html>
