<form id="guests-form">
<table id="guests-grid" class="admin-grid">
<thead>
  <tr>
    <th>Guest First Name</th>
    <th>Guest Last Name</th>
    <th>Guest Bio</th>
    <th>Guest Role</th>
    <th>Guest Website</th>
    <th>Action</th>
  </tr>
</head>
<tbody>
  <tr class="separator"></tr>
  <tr>
    <td><input type="text" name="FirstName" class="first-name"></td>
    <td><input type="text" name="LastName"  class="last-name" ></td>
    <td><textarea          name="Bio"       class="bio"       ></textarea></td>
    <td><input type="text" name="Role"      class="role"      ></td>
    <td><input type="text" name="Website"   class="website"   ></td>
    <td><button class="button-add">Add New</button></td>
  </tr>
  <tr class="separator"></tr>
<?php foreach($data as $d): ?>
  <tr id="data-row-<?php echo $d['GuestID']; ?>">
    <td><?php echo $d["FirstName"]; ?></td>
    <td><?php echo $d["LastName"]; ?></td>
    <td><?php echo $d["Bio"]; ?></td>
    <td><?php echo $d["Role"]; ?></td>
    <td><?php echo $d["Website"]; ?></td>
    <td><button class="button-edit" data-guestid="<?php echo $d['GuestID']; ?>">Edit</button></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</form>

<div id="overlay"></div>

<div id="guest-edit-popup" class="edit-popup"></div>
<script id="guest-edit-template" type="text/x-jquery-tmpl">
<form id="guest-edit-form">
  <h3>Edit Guest</h3>
  <input type="hidden" name="_method" value="PUT" id="_method">
  <label  for="FirstName">First Name</label>
  <input name="FirstName" type="text" value="{{:FirstName}}" />
  <label  for="LastName" >LastName</label>
  <input name="LastName"  type="text" value="{{:LastName}}" />
  <label  for="Bio"      >Bio</label>
  <textarea name="Bio"   >{{:Bio}}</textarea>
  <label  for="Role"     >Role</label>
  <input name="Role"      type="text" value="{{:Role}}" />
  <label  for="Website"  >Website</label>
  <input name="Website"   type="text" value="{{:Website}}" /> 
</form>
<button class="button-save">Save</button>
</script>
