<form id="guests-form">
<table id="guests-grid" class="admin-grid">
<thead>
  <tr>
    <th>Guest First Name</th>
    <th>Guest Last Name</th>
    <th>Guest Bio</th>
    <th>Guest Website</th>
    <th>Action</th>
  </tr>
</head>
<tbody>
  <tr class="separator"></tr>
  <tr>
    <td><input type="text" class="first-name" value="First Name"></td>
    <td><input type="text" class="last-name"  value="Last Name"></td>
    <td><textarea class="description">Guest Bio</textarea></td>
    <td><input type="text" class="role"       value="Role"></td>
    <td><button class="button-add">Add New</button></td>
  </tr>
  <tr class="separator"></tr>
<?php foreach($data as $d): ?>
  <tr data-guestid="<?php echo $d['GuestID']; ?>" title="Click row to edit">
    <td><?php echo $d["FirstName"]; ?></td>
    <td><?php echo $d["LastName"]; ?></td>
    <td><?php echo $d["Bio"]; ?></td>
    <td><?php echo $d["ConventionRole"]; ?></td>
    <td><button class="button-delete">Delete</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</form>
