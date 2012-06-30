var App = {};
var daysofweek = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
var tkey = 'todo5';
var feedbackSubject = null;
var scheduleRendered = false;
var Data = {
	Model: {}
};

// JQM options
$(document).bind("mobileinit", function(){
  $.mobile.defaultPageTransition = 'none';
});

function parseDate(input) {
  var parts = input.match(/(\d+)/g);
  return new Date(parts[0], parts[1]-1, parts[2]-1);
}

function buildSchedule() {
	var d = []; // sorted events array
	var eventdays = [];
	var output = [];
	var day, dow, o;
	var dat;
	if(Data.events.count > 0) {
		// massage Data.events
		for(var i in Data.events.items) {
			// add DayAndTime
			Data.events.items[i].DayAndTime = getDayTime(Data.events.items[i].StartDate);
			d.push(Data.events.items[i]);
		}
		d = _.sortBy(d, function(n) {
			return n.StartDate;
		});
		for(var j = 0; j < d.length; j++) {
			day = d[j].StartDate.split(' ')[0];
			if(!_.include(eventdays, day)) {
				eventdays.push(day);
				dow = parseDate(day);
				dow = dow.getDay();
				pi = eventdays.length - 1;
				ni = eventdays.length + 1;
				output.push({
					index: eventdays.length, 
					previndex: pi,
					nextindex: ni,
					date: day,
					dayofweek: daysofweek[dow],
					events: []
				});
			}
			o = _.find(output, function(r) { return r.date == day; });
			o.events.push(d[j]);
		}
		output[output.length-1].nextindex = null;
		
		for(var k = 0; k < output.length; k++) {
			var tmp  = '';
			var time = '';
			for(var kk = 0; kk < output[k].events.length; kk++) {
				time = getShortTime(output[k].events[kk].StartDate.split(' ')[1]);
				if(tmp != output[k].events[kk].StartDate) {
					tmp = output[k].events[kk].StartDate;
					output[k].events.splice(kk, 0, { divider: true, time: time });
					kk++;
				}
			}
		}
	} else {
		alert("Error: no events available");
	}
	var html = $('#schedule-template').render(output);
  $('.schedule').trigger('updatelayout');
  $('body').append(html);
}

function getShortTime(timestr) {
  // 14:30:00 to 2:30
  var a = timestr.split(':');
  var b = 'AM';
  if(a[0] > 12) {
    a[0] = a[0]-12;
    b = 'PM';
  }
  return a[0]+':'+a[1]+' '+b;
}
function getDayTime(dt) {
  // dt format: 2012-04-28 10:45:00
  var ds = dt.split(' ');
  newdate = parseDate(ds[0]);
  newdate = newdate.getDay();
  return daysofweek[newdate] + " at " + getShortTime(ds[1]);
}

App.Notify = function(msg, callback, title, buttons) {
  if(navigator.notification) {
    navigator.notification.alert(msg, callback, title, buttons);
  } else {
    alert(msg);
  }
}

if( localStorage[tkey] == null ) {
  localStorage[tkey] = '';
}
function getToDo() {
  // returns an array of all the user's ToDo events
  var ts = ''+localStorage[tkey];
  var ta = ts.split(',');
  ta = _.compact(ta);
  return ta;
}
function addToDo(eid) {
  // add event to ToDo list
  if(searchToDo(eid) < 0) {
    var a = getToDo();
    a.push(eid);
    App.Notify("Event added.", null, "My ToDo", "Awesome!");
    localStorage[tkey] = a;
  } else {
    App.Notify("This event is already in your to-do list.", null, "My ToDo", "My bad.");
  }
}
function searchToDo(eid) {
  // returns index of event or -1 if not found
  var tempStr = localStorage[tkey];
  var tempArr = tempStr.split(',');
  var r = $.inArray(eid, tempArr)
  return r;
}
function removeToDo(eid) {
  // removes event from user's ToDo list
  var i = searchToDo(eid);
  var a = getToDo();
  if(i > -1) {
    a.splice(i, 1);
    localStorage[tkey] = a;
    if(a.length > 0) {
      renderToDo();
    } else {
      clearToDo();
    }
    App.Notify("Event removed.", null, "My ToDo");
  } else {
    App.Notify("Invalid Event ID.", null, "My ToDo");
  }
}
function renderToDo() {
  var todoArray = getToDo();
  var todoObjects = [];
  var newobj, html;
  for(var i = 0; i < todoArray.length; i++) {
    newobj = Data.events.items[todoArray[i]];
    todoObjects.push(newobj);
  }
  if(todoObjects.length) {
    todoObjects = _.sortBy(todoObjects, function(n) {
      return n.StartDate;
    });
		html = $('#todo-list-template').render(todoObjects);
    $('.todo-empty').hide();
    $('.todo-clear').show();
	} else {
		html = '';
    $('.todo-empty').show();
    $('.todo-clear').hide();
	}
  $('#todo').trigger('updatelayout');
  $('#todo-list').html(html).listview('refresh').trigger('updatelayout');
  
  if($('#todo-list li').length > 1) {
    $('#todo-list li:first').addClass('ui-corner-top');
    $('#todo-list li:last' ).addClass('ui-corner-bottom');
  } else {
    $('#todo-list li').addClass('ui-corner-top ui-corner-bottom');
  }
}
function clearToDo() {
  localStorage[tkey] = '';
  $('#todo-list').html('');
  $('.todo-empty').show();
  $('.todo-clear').hide();
}

/* Page init functions */

$(function() {
  buildSchedule();
  document.addEventListener("deviceready", function() {
  }, false);

  // caching ye olde selectors
  var $eventDetail = $('#event-detail');
  var $eventDetailContent = $('#event-detail-content');

  var $feedback = $('#feedback');
  var $feedbackForm = $('#feedback-form');

  var $guests = $('#guests');
  var $guestDetail = $('#guest-detail');
  var $guestDetailContent = $('#guest-detail-content');
  var $guestsList = $('#guests-list');

  var $map = $('#map');
  var $mapImage = $('#map-image');
  var $mapZoomIn = $('#map-zoom-in');
  var $mapZoomOut = $('#map-zoom-out');

  var $todo = $('#todo');

  var $twitter = $('#twitter');
  var $tweetsList = $('#tweets-list');

  // create guests page
  (function() {
    var d = [];
    // make an array of all the guests
    for(var i in Data.guests.items) {
      d.push(Data.guests.items[i]);
    }
    // sort the array (yay Underscore!)
    d = _.sortBy(d, function(n) {
      return n.FirstName+" "+n.LastName;
    });
    // render the list template
    var html = $('#guests-template').render(d);
    $guestsList.html(html).trigger('updatelayout');
  }());

  $('.guest-detail-link').live('click',function(e) {
    // when a list item is selected, render the new guest detail page,
    // remove the old one from DOM, append the new one and switch.
    e.preventDefault();
    Data.Model.Guest = $(this).attr('data-guestid');
    
    var d = Data.guests.items[Data.Model.Guest];
    if(d.EventList) {
      d.GuestEvents = [];
      var tmp = d.EventList.split(',');
      for(var i in tmp) {
        if(Data.events.items[tmp[i]]) {
          d.GuestEvents.push(Data.events.items[tmp[i]]);
        }
      }
    }
    var html = $('#guest-detail-template').render(d);
    $guestDetailContent.empty().html(html);

    // switch to the new page, then JQM-enhance it  
    $.mobile.changePage('#guest-detail');
    $guestDetailContent.trigger('create');
  });

  $('.event-detail-link').live('click', function(e) {
    e.preventDefault();
    Data.Model.Event = $(this).attr('data-eventid');
    
    var d = Data.events.items[Data.Model.Event];
    if(d.GuestList) {
      d.EventGuests = [];
      var tmp = d.GuestList.split(',');
      for(var i in tmp) {
        if(Data.guests.items[tmp[i]]) {
          d.EventGuests.push(Data.guests.items[tmp[i]]);
        }
      }
    }
      
    var html = $('#event-detail-template').render(d);
    $eventDetailContent.empty().html(html);

    if(_.include(getToDo(), String(Data.Model.Event))) {
      $eventDetailContent.find('.todo-add').addClass('ui-disabled');
    } else {
      $eventDetailContent.find('.todo-add').removeClass('ui-disabled');
    }

    // switch to the new page, then JQM-enhance it  
    $.mobile.changePage('#event-detail');	
    $eventDetail.trigger('create');
  });

  $mapZoomIn.click(function(e) {
    e.preventDefault();
    var cw = $mapImage.outerWidth();
    $mapImage.css({
      width: cw * 1.2
    });
  });

  $mapZoomOut.click(function(e) {
    e.preventDefault();
    var cw = $mapImage.outerWidth();
    $mapImage.css({
      width: cw * 0.8
    });
  });

  $twitter.bind('pageinit', function(e) {
    $tweetsList.tweet({
      avatar_size: 48,
      count: 20,
      query: "jordancon",
      loading_text: "searching twitter...",
      template: "{avatar}{user}{text}{time}"
    });
    $tweetsList.on('click','a',function(e) {
      e.preventDefault();
      App.Notify('Clicking on links in tweets has been disabled for now. Sorry!', null, 'Drat!');
    });
  });


  /* Todo page */

  $('.todo-add').live('click', function(e){
    e.preventDefault();
    var id = $(this).attr('data-eventid');
    addToDo(id);
    $(this).addClass('ui-disabled');
    renderToDo();
  });

  $todo.bind('pageinit', function() {
    renderToDo();  
    $todo.on('click', '.todo-remove', function(e){
      e.preventDefault();
      var id = $(this).attr('data-eventid');
      removeToDo(id);
    });
    $todo.on('click', '.todo-clear', function(e){
      e.preventDefault();
      navigator.notification.confirm("Are you sure you want to delete all items from your to-do list?", function(i){
        if(i == 1) {
          clearToDo();
        }
      }, "Delete All ToDo Items", "Yes,No");
    });
  });


  /* Feedback page */

  $('#dashboard,#event-detail').on('click, .feedback-link',function(e) {
    feedbackSubject = "";
    if($(this).hasClass('dashboard')) {
      feedbackSubject = 'General Feedback for ' + Convention.Name;
      $('#rating').hide();
    } else {
      feedbackSubject = 'Feedback for "' + $eventDetail.find('h3').text() + '"';
      $('#rating').show();
    }
  });
  $feedbackForm.find('.submit').click(function(e) {
    e.preventDefault();
    $(this).addClass('ui-disabled');
    var content = $feedbackForm.find('.content').val();
    var rating  = $feedback.find('input:radio[name=rating]:checked').val();
    var meta    = feedbackSubject + " [cid "+Convention.ConventionID+", rating "+rating+"]";
    $.ajax({
      url: 'http://con-nexus.com/feedback',
      method: 'GET',
      dataType: 'jsonp',
      jsonp: 'callback',
      data: {
        content : content,
        meta    : meta
      },
      success: function(resp) {
        $feedbackForm.find('.submit').removeClass('ui-disabled');
        $feedbackForm.find('.content').val('');
        $feedbackForm.find('.meta').val('');
        $feedback.find('input:radio').prop('checked',false);
        $feedback.find('.ui-btn').removeClass('ui-btn-active ui-radio-on');
        if(resp.status == 'OK') {
          App.Notify("Your feedback has been submitted.", null, "Thanks!");
        } else {
          App.Notify(resp.error, null, "Thank you!", "Great!");
        }
      },
      error: function() {
        App.Notify("There was an error submitting feedback.");
      }
    });
  });
  $feedback.bind('pagebeforeshow', function() {
    $feedback.find('h3').text(feedbackSubject);
  });

});
