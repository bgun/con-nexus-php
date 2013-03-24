<table id="feedback-grid" class="admin-grid">
<thead>
  <tr>
    <th class="col-date"   >Submitted</th>
    <th class="col-content">Feedback Text</th>
    <th class="col-meta"   >[Rating] Subject</th>
  </tr>
</head>
<tbody>
<?php foreach($data as $d): ?>
  <tr>
    <td>
      <?php
        echo date("Y/m/d g:i A", strtotime($d["SubmitDate"]));
        echo "<br />(".date("l", strtotime($d["SubmitDate"])).")";
      ?>
    </td>
    <td><?php echo $d["Content"]; ?></td>
    <td><?php echo $d["Meta"]; ?></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
