$(function() {

  var $eventsGrid = $('#events-grid');

  $eventsGrid.find('tr').hover(function() {
    $(this).addClass('hover');
  },function() {
    $(this).removeClass('hover');
  });

  $eventsGrid.find('tr').click(function() {
    var eid = $(this).attr('data-eventid');
    $.ajax({
      url: '/api/'+ConventionID+'/event/'+eid,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        var html = $('#event-edit-template').render(data);
        $('body').append(html);
        $('#event-edit-popup').css({
          top: $('body').scrollTop() + 100
        });
        $('#overlay').show();
      },
      error: function() {
        alert("Error loading event data.");
      }
    });
  });

  $eventsGrid.find('.button-add').click(function(e) {
    e.preventDefault();
    $.ajax({
      url: '/api/'+ConventionID+'/events',
      data: $('#events-form').serialize(),
      type: 'POST',
      success: function(resp) {
        alert(resp);
      }
    });
  });

  $eventsGrid.find('.button-delete').click(function(e) {
    e.preventDefault();
    if(confirm("Are you sure you wish to delete this event?")) {
      alert("OK then!");
    } else {
      alert("Whew!");
    }
  });

  $('#overlay').click(function() {
    $('.edit-popup').remove();
    $('#overlay').hide();
  });

});
