<table id="events-list">
<thead>
  <tr>
    <th>Event Title</th>
    <th>Event Start</th>
    <th>Event Description</th>
    <th>Event Location</th>
    <th>Guests</th>
    <th>Action</th>
  </tr>
</head>
<tbody>
  <tr class="separator"></tr>
  <tr>
    <td><input type="text" class="title"     ></td>
    <td><input type="text" class="start-date"></td>
    <td><textarea class="description"></textarea></td>
    <td><input type="text" class="location"  ></td>
    <td></td>
    <td><button>Add New</button></td>
  </tr>
  <tr class="separator"></tr>

<?php foreach($data as $d): ?>
  <tr data-eventid="<?php echo $d['EventID']; ?>" title="Click row to edit">
    <td><?php echo $d["Title"]; ?></td>
    <td><?php echo date("l, m/d/Y g:i", strtotime($d["StartDate"])); ?></td>
    <td><?php echo $d["Description"]; ?></td>
    <td><?php echo $d["Location"]; ?></td>
    <td>
      <ul>
      <?php foreach($d["Guests"] as $g): ?>
        <li><?php echo $g["FirstName"]." ".$g["LastName"]; ?></li>
      <?php endforeach; ?>
      </ul>
    </td>
    <td><button>Delete</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
