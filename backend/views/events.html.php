<form id="events-form">
<table id="events-grid" class="admin-grid">
<thead>
  <tr>
    <th>Event Title</th>
    <th>Event Start</th>
    <th>Event Description</th>
    <th>Event Location</th>
    <th class="guests">Guests</th>
    <th>Action</th>
  </tr>
</head>
<tbody>
  <tr class="separator"></tr>
  <tr>
    <td><input type="text" name="Title"       class="title"     ></td>
    <td><input type="text" name="StartDate"   class="start-date"></td>
    <td><textarea          name="Description" class="description"></textarea></td>
    <td><input type="text" name="Location"    class="location"  ></td>
    <td></td>
    <td><button class="button-add">Add New</button></td>
  </tr>
  <tr class="separator"></tr>

<?php foreach($data as $d): ?>
  <tr data-eventid="<?php echo $d['EventID']; ?>" title="Click row to edit">
    <td><?php echo $d["Title"]; ?></td>
    <td>
      <?php
        echo date("l", strtotime($d["StartDate"]))."<br />";
        echo date("m/d/Y g:i", strtotime($d["StartDate"]));
      ?>
    </td>
    <td><?php echo $d["Description"]; ?></td>
    <td><?php echo $d["Location"]; ?></td>
    <td class="guests">
      <ul>
      <?php foreach($d["Guests"] as $g): ?>
        <li><button class="button-remove">x</button><?php echo $g["FirstName"]." ".$g["LastName"]; ?></li>
      <?php endforeach; ?>
      </ul>
      <button class="button-add-guest">Add Guest</button>
    </td>
    <td><button class="button-delete">Delete</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</form>

<div id="overlay"></div>

<script id="event-edit-template" type="text/x-jquery-tmpl">
<div id="event-edit-popup" class="edit-popup">
  <h3>Edit Event</h3>
  <label  for="event-title">Title</label>
  <input name="event-title" type="text" value="{{:Title}}" />
  <label  for="event-start-date">Date/Time</label>
  <input name="event-start-date" type="text" value="{{:StartDate}}" />
  <label  for="event-description">Description</label>
  <input name="event-description" type="text" value="{{:Description}}" />
  <label  for="event-location">Location</label>
  <input name="event-location" type="text" value="{{:Location}}" />
  <button class="button-save">Save</button>
</div>
</script>
