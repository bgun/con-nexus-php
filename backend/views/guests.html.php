<table id="guests-list">
<tbody>
  <tr>
    <td><input type="text" class="first-name" value="First Name"></td>
    <td><input type="text" class="last-name"  value="Last Name"></td>
    <td><textarea class="description">Guest Bio</textarea></td>
    <td><input type="text" class="role"       value="Role"></td>
    <td><button>Add New</button></td>
  </tr>
  <tr class="separator"></tr>
<?php foreach($data as $d): ?>
  <tr data-guestid="<?php echo $d['GuestID']; ?>" title="Click row to edit">
    <td><?php echo $d["FirstName"]; ?></td>
    <td><?php echo $d["LastName"]; ?></td>
    <td><?php echo $d["Bio"]; ?></td>
    <td><?php echo $d["ConventionRole"]; ?></td>
    <td><button>Delete</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
