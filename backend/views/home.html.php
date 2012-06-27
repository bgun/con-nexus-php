<h2>Select a convention:</h2>
<table id="conventions-list">
<tbody>
<?php foreach($data as $d): ?>
  <tr data-conid="<?php echo $d['ConventionID']; ?>">
    <td><a href="<?php echo url_for('admin', $d['ConventionID'], 'events'); ?>"><?php echo $d["Name"]; ?></a></td>
    <td><?php echo $d["StartDate"]; ?></td>
    <td><?php echo $d["EndDate"]; ?></td>
    <td><?php echo $d["Description"]; ?></td>
    <td><?php echo $d["Website"]; ?></td>
    <td><?php echo $d["Twitter"]; ?></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
