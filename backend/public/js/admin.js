
(function($) {

  $.fn.clickToRemoveGuest = function() {
    var cid = ConventionID;
    $(this).click(function(e) {
      e.preventDefault();
      var $self = $(this);
      var gid = $self.attr('data-guestid');
      var eid = $self.attr('data-eventid');
      $.ajax({
        url: '/api/'+cid+'/removeguest',
        data: {
          EventID: eid,
          GuestID: gid,
          _method: 'DELETE'
        },
        dataType: 'json',
        type: 'POST',
        success: function(resp) {
          $self.parent().hide();
        },
        error: function() {
        }
      });
    });
  };

}(jQuery));


$(function() {

  $('.admin-grid').find('tr').hover(function() {
    $(this).addClass('hover');
  },function() {
    $(this).removeClass('hover');
  });

  // Updating convention data timestamp

  $('#conventions-list,#header').find('.button-update').click(function(e) {
    var $t = $(this);
    var cid = $t.attr('data-convention-id');
    var msg = "This will force all mobile users to download an updated package of Events and Guests data. Are you sure?";
    if(confirm(msg)) {
      $.ajax({
        url: '/api/'+cid+'/update',
        type: 'POST',
        data: {
          _method: 'PUT'
        },
        dataType: 'json',
        success: function(data) {
          alert("OK! Apps will receive an update next time they are launched by the user.");
        },
        error: function() {
          alert("Error updating convention data.");
        }
      });
    }
  });

  /* Events grid */

  var $eventsGrid = $('#events-grid');
  var $eventEditPopup = $('#event-edit-popup');
  var activeEvent = null;

  $eventsGrid.find('.button-edit').click(function(e) {
    e.preventDefault();
    var $t = $(this);
    var eid = $t.attr('data-eventid');
    activeEvent = eid;
    if($t.index() === 1) {
      return;
    }
    $.ajax({
      url: '/api/'+ConventionID+'/event/'+eid,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        var html = $('#event-edit-template').render(data);
        $('#event-edit-popup').html(html).css({
          top: $('body').scrollTop() + 100
        }).show();
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
      dataType: 'json',
      type: 'POST',
      success: function(resp) {
        if(resp.success) {
          window.location.reload();
        } else {
          alert("Error writing to API");
        }
      }
    });
  });
  $eventsGrid.find('.button-add-guest').click(function(e) {
    e.preventDefault();
    var $t = $(this);
    var eid = $(this).attr('data-eventid');
    var cid = ConventionID;
    var $row = $('#data-row-'+eid);
    var $sg = $row.find('.select-guest');
    $sg.html( $('#select-guest-template').html());
    $t.hide();
    // bind to onchange
    $sg.find('select').change(function(e) {
      var gid = $(this).val();
      var gname = $(this).find('option[value='+gid+']').text();
      var data = {
        GuestID: gid,
        EventID: eid,
      };
      $.ajax({
        url: '/api/'+cid+'/addguest',
        data: data,
        dataType: 'json',
        type: 'POST',
        success: function(resp) {
          $row.find('.guests').append(
            '<li><button class="button-remove-guest" data-eventid="'+eid+'" data-guestid="'+gid+'">x</button>'+gname+'</li>'
          );
          $row.find('.button-remove-guest').clickToRemoveGuest();
        },
        error: function() {
          alert("There was a problem adding a guest to this event.");
        }
      });
      $sg.empty();
      $t.show();
    });
  });

  $eventsGrid.find('.button-remove-guest').clickToRemoveGuest();

  $eventEditPopup.on('click','.button-save',function(e) {
    e.preventDefault();
    var data = $eventEditPopup.find('form').serialize();
    $.ajax({
      url: '/api/'+ConventionID+'/event/'+activeEvent,
      data: data,
      dataType: 'json',
      type: 'POST',
      success: function(resp) {
        window.location.reload();
      },
      error: function() {
      }
    });
  });


  /* Guests grid */

  var $guestsGrid = $('#guests-grid');
  var $guestEditPopup = $('#guest-edit-popup');
  var activeGuest = null;

  $guestsGrid.find('.button-edit').click(function(e) {
    e.preventDefault();
    var $t = $(this);
    var gid = $t.attr('data-guestid');
    activeGuest = gid;
    if($t.index() === 1) {
      return;
    }
    $.ajax({
      url: '/api/'+ConventionID+'/guest/'+gid,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        var html = $('#guest-edit-template').render(data);
        $('#guest-edit-popup').html(html).css({
          top: $('body').scrollTop() + 100
        }).show();
        $('#overlay').show();
      },
      error: function() {
        alert("Error loading guest data.");
      }
    });
  });
  $guestsGrid.find('.button-add').click(function(e) {
    e.preventDefault();
    e.stopPropagation();
    $.ajax({
      url: '/api/'+ConventionID+'/guests',
      data: $('#guests-form').serialize(),
      dataType: 'json',
      type: 'POST',
      success: function(resp) {
        if(resp.success) {
          window.location.reload();
        } else {
          alert("Error writing to API");
        }
      },
      error: function() {
        alert("API error");
      }
    });
  });
  $guestsGrid.find('.button-delete').click(function(e) {
    e.preventDefault();
    e.stopPropagation();
    if(confirm("Are you sure you wish to delete this guest?")) {
      alert("OK then!");
    } else {
      alert("Whew!");
    }
  });
  $guestEditPopup.on('click','.button-save',function(e) {
    e.preventDefault();
    e.stopPropagation();
    var data = $guestEditPopup.find('form').serialize();
    $.ajax({
      url: '/api/'+ConventionID+'/guest/'+activeGuest,
      data: data,
      dataType: 'json',
      type: 'POST',
      success: function(resp) {
        window.location.reload();
      },
      error: function() {
      }
    });
  });

  $('#overlay').click(function() {
    $('.edit-popup').empty().hide();
    $('#overlay').hide();
    activeEvent = null;
    activeGuest = null;
  });

});
