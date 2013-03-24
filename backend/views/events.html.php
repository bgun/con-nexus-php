<form id="events-form">
<table id="events-grid" class="admin-grid">
<thead>
  <tr>
    <th class="col-title"      >Event Title</th>
    <th class="col-start-date" >Event Start</th>
    <th class="col-description">Event Description</th>
    <th class="col-location"   >Event Location</th>
    <th class="col-guests"     >Guests</th>
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
  <tr id="data-row-<?php echo $d['EventID']; ?>">
    <td><?php echo $d["Title"]; ?></td>
    <td>
      <?php
        echo date("Y/m/d g:i A", strtotime($d["StartDate"]));
        echo "<br />(".date("l", strtotime($d["StartDate"])).")";
      ?>
    </td>
    <td><?php echo $d["Description"]; ?></td>
    <td><?php echo $d["Location"]; ?></td>
    <td>
      <ul class="guests">
      <?php foreach($d["Guests"] as $g): ?>
        <li><button class="button-remove-guest" data-eventid="<?php echo $d['EventID']; ?>" data-guestid="<?php echo $g['GuestID']; ?>">x</button><?php echo $g["FirstName"]." ".$g["LastName"]; ?></li>
      <?php endforeach; ?>
      </ul>
      <div class="select-guest"></div>
      <button class="button-add-guest" data-eventid="<?php echo $d['EventID']; ?>">Add Guest</button>
    </td>
    <td><button class="button-edit" data-eventid="<?php echo $d['EventID']; ?>">Edit</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</form>

<div id="select-guest-template">
  <select id="select-guest">
    <option value="-1">Select a guest</option>
    <?php foreach($guests as $g): ?>
      <option value="<?php echo $g['GuestID']; ?>"><?php echo $g['FirstName']." ".$g['LastName']; ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div id="overlay"></div>

<div id="event-edit-popup" class="edit-popup"></div>
<script id="event-edit-template" type="text/x-jquery-tmpl">
<form id="event-edit-form">
  <h3>Edit Event</h3>
  <input type="hidden" name="_method" value="PUT" id="_method">
  <label  for="Title"      >Title</label>
  <input name="Title"       type="text" value="{{:Title}}" />
  <label  for="StartDate"  >Date/Time</label>
  <input name="StartDate"   type="text" value="{{:StartDate}}" />
  <label  for="Description">Description</label>
  <textarea name="Description">{{:Description}}</textarea>
  <label  for="Location"   >Location</label>
  <input name="Location"    type="text" value="{{:Location}}" />
</form>
<button class="button-save">Save</button>
</script>
