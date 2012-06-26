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

<header>
  <h2><?php echo $convention["Name"]; ?></h2>
  <nav id="tab-menu">
    <ul>
      <li><a href="/backend/admin/<?php echo $convention["ConventionID"]; ?>/events">Events</a></li>
      <li><a href="/backend/admin/<?php echo $convention["ConventionID"]; ?>/guests">Guests</a></li>
    </ul>
  </nav>
</header>

<?php echo $content; ?>

</body>
</html>
