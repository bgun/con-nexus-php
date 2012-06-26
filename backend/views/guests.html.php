<table id="guests-list">
<tbody>
  <tr>
    <td><input type="text" class="first-name" value="First Name"></td>
    <td><input type="text" class="last-name"  value="Last Name"></td>
    <td><textarea class="description">Guest Bio</textarea></td>
    <td><input type="text" class="location"   value=""></td>
    <td><button>Add New</button></td>
  </tr>
  <tr class="separator"></tr>
<?php foreach($data as $d): ?>
  <tr data-eventid="<?php echo $d['EventID']; ?>" title="Click row to edit">
    <td><?php echo $d["Title"]; ?></td>
    <td><?php echo $d["StartDate"]; ?></td>
    <td><?php echo $d["Description"]; ?></td>
    <td><?php echo $d["Location"]; ?></td>
    <td><button>Delete</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
