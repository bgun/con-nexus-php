// function namespace
var App = {};

// all convention data
var Model = {
  cid: Convention.ConventionID,
  events: {},
  guests: {}
};

// caching jQuery objects for speed
var DomCache = {};

var lsKeys = {
  todo: 'todo-list',
  events: 'events-data',
  guests: 'guests-data',
  lastUpdate: 'last-update'
};
var daysofweek = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
var feedbackSubject = null;
var feedbackId = null;
var scheduleRendered = false;
var RootDomain = 'con-nexus.com';

// JQM options
$(document).bind("mobileinit", function(){
  $.mobile.defaultPageTransition = 'none';
});

$(document).bind("orientationchange", resizeStyles);

function resizeStyles() {
  var $t = $('body');
  if($t.height() > $t.width()) {
    $('div.ui-body-c').css({
      backgroundSize: "auto 100%"
    });
  } else {
    $('div.ui-body-c').css({
      backgroundSize: "100% auto"
    });
  }
}

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
	if(Model.events.count > 0) {
		// massage Model.events
		for(var i in Model.events.items) {
			// add DayAndTime
			Model.events.items[i].DayAndTime = getDayTime(Model.events.items[i].StartDate);
			d.push(Model.events.items[i]);
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
		App.Notify("Error: no events available");
	}
	var html = $('#schedule-template').render(output);
  $('.schedule').trigger('updatelayout');
  $('body').append(html);
}

function renderGuests() {
  var d = [];
  // make an array of all the guests
  for(var i in Model.guests.items) {
    d.push(Model.guests.items[i]);
  }
  // sort the array (yay Underscore!)
  d = _.sortBy(d, function(n) {
    return n.FirstName+" "+n.LastName;
  });
  // render the list template
  var html = $('#guests-template').render(d);

  return html;
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

function getToDo() {
  // returns an array of all the user's ToDo events
  var ts = ''+localStorage.getItem(lsKeys.todo);
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
    localStorage.setItem(lsKeys.todo, a);
  } else {
    App.Notify("This event is already in your to-do list.", null, "My ToDo", "My bad.");
  }
}
function searchToDo(eid) {
  // returns index of event or -1 if not found
  var tempStr = localStorage.getItem(lsKeys.todo);
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
    localStorage.setItem(lsKeys.todo, a);
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
    newobj = Model.events.items[todoArray[i]];
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
  localStorage.setItem(lsKeys.todo, '');
  $('#todo-list').html('');
  $('.todo-empty').show();
  $('.todo-clear').hide();
}


$.fn.bindGuestDetailLinks = function() {
  $(this).click(function(e) {
    // when a list item is selected, render the new guest detail page,
    // remove the old one from DOM, append the new one and switch.
    e.preventDefault();
    Model.guestDetail = $(this).attr('data-guestid');
   
    var d = Model.guests.items[Model.guestDetail];
    if(d.EventList) {
      d.GuestEvents = [];
      var tmp = d.EventList.split(',');
      for(var i in tmp) {
        if(Model.events.items[tmp[i]]) {
          d.GuestEvents.push(Model.events.items[tmp[i]]);
        }
      }
      d.GuestEvents = _.sortBy(d.GuestEvents, function(n) {
        return n.StartDate;
      });
    }
    var html = $('#guest-detail-template').render(d);
    DomCache.$guestDetailContent.empty().html(html);

    // switch to the new page, then JQM-enhance it  
    $.mobile.changePage('#guest-detail');
    DomCache.$guestDetailContent.trigger('create');

    // bind events on the new page
    DomCache.$guestDetailContent.find('.event-detail-link').bindEventDetailLinks();
  });
};

$.fn.bindEventDetailLinks = function() {
  $(this).click(function(e) {
    e.preventDefault();
    Model.eventDetail = $(this).attr('data-eventid');
    
    var d = Model.events.items[Model.eventDetail];
    if(d.GuestList) {
      d.EventGuests = [];
      var tmp = d.GuestList.split(',');
      for(var i in tmp) {
        if(Model.guests.items[tmp[i]]) {
          d.EventGuests.push(Model.guests.items[tmp[i]]);
        }
      }
    }
      
    var html = $('#event-detail-template').render(d);
    DomCache.$eventDetailContent.empty().html(html);

    if(_.include(getToDo(), String(Model.eventDetail))) {
      DomCache.$eventDetailContent.find('.todo-add').addClass('ui-disabled');
    } else {
      DomCache.$eventDetailContent.find('.todo-add').removeClass('ui-disabled');
    }

    // switch to the new page, then JQM-enhance it  
    $.mobile.changePage('#event-detail');	
    DomCache.$eventDetail.trigger('create');

    // bind events on the new page
    DomCache.$eventDetailContent.find('.guest-detail-link').bindGuestDetailLinks();
    DomCache.$eventDetailContent.find('.todo-add').click(function(e){
      e.preventDefault();
      var id = $(this).attr('data-eventid');
      addToDo(id);
      $(this).addClass('ui-disabled');
      renderToDo();
    });
  });
};


/* Page init functions */

function init(useLocalStorage) {

  resizeStyles();

  if(useLocalStorage) {
    Model.events = JSON.parse(localStorage.getItem(lsKeys.events));
    Model.guests = JSON.parse(localStorage.getItem(lsKeys.guests));
  }
  
  // caching ye olde selectors
  DomCache.$eventDetail = $('#event-detail');
  DomCache.$eventDetailContent = $('#event-detail-content');

  DomCache.$feedback = $('#feedback');
  DomCache.$feedbackForm = $('#feedback-form');

  DomCache.$guests = $('#guests');
  DomCache.$guestDetail = $('#guest-detail');
  DomCache.$guestDetailContent = $('#guest-detail-content');
  DomCache.$guestsList = $('#guests-list');

  DomCache.$map = $('#map');
  DomCache.$mapImage = $('#map-image');
  DomCache.$mapZoomIn = $('#map-zoom-in');
  DomCache.$mapZoomOut = $('#map-zoom-out');

  DomCache.$todo = $('#todo');

  DomCache.$twitter = $('#twitter');
  DomCache.$tweetsList = $('#tweets-list');
 
  buildSchedule();
  DomCache.$guestsList.html( renderGuests() ).trigger('updatelayout');

  $('#guests').find('.guest-detail-link').bindGuestDetailLinks();
  $('.schedule').find('.event-detail-link').bindEventDetailLinks();

  DomCache.$mapZoomIn.click(function(e) {
    e.preventDefault();
    var cw = DomCache.$mapImage.outerWidth();
    DomCache.$mapImage.css({
      width: cw * 1.1
    });
  });

  DomCache.$mapZoomOut.click(function(e) {
    e.preventDefault();
    var cw = DomCache.$mapImage.outerWidth();
    DomCache.$mapImage.css({
      width: cw * 0.9
    });
  });

  DomCache.$twitter.bind('pageinit', function(e) {
    DomCache.$tweetsList.tweet({
      avatar_size: 48,
      count: 20,
      query: Convention.Twitter,
      loading_text: "searching twitter...",
      template: "{avatar}{user}{text}{time}"
    });
    DomCache.$tweetsList.on('click','a',function(e) {
      e.preventDefault();
      App.Notify('Clicking on links in tweets has been disabled for now. Sorry!', null, 'Drat!');
    });
  });


  /* Todo page */

  DomCache.$todo.bind('pageinit', function() {
    renderToDo();  
    DomCache.$todo.on('click', '.todo-remove', function(e){
      e.preventDefault();
      var id = $(this).attr('data-eventid');
      removeToDo(id);
    });
    DomCache.$todo.on('click', '.todo-clear', function(e){
      e.preventDefault();
      navigator.notification.confirm("Are you sure you want to delete all items from your to-do list?", function(i){
        if(i == 1) {
          clearToDo();
        }
      }, "Delete All ToDo Items", "Yes,No");
    });
  });


  /* Feedback page */

  DomCache.$feedbackForm.trigger('create');
  $('#dashboard,#event-detail').on('click','.feedback-link',function(e) {
    feedbackSubject = "";
    if($(this).hasClass('dashboard')) {
      feedbackSubject = 'General Feedback for ' + Convention.Name;
    } else {
      feedbackSubject = 'Feedback for "' + DomCache.$eventDetailContent.find('h3').text() + '"';
    }
  });
  DomCache.$feedbackForm.on('click','.submit',function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('ui-disabled');
    var content = DomCache.$feedbackForm.find('.content').val();
    var rating  = DomCache.$feedback.find('input:radio[name=rating]:checked').val();
    var meta    = "["+rating+"] "+feedbackSubject;
    $.ajax({
      url: 'http://'+RootDomain+'/feedback',
      method: 'GET',
      dataType: 'json',
      data: {
        content : content,
        meta    : meta,
        cid     : Convention.ConventionID
      },
      success: function(resp) {
        DomCache.$feedbackForm.find('.submit').removeClass('ui-disabled');
        DomCache.$feedbackForm.find('.content').val('');
        DomCache.$feedbackForm.find('.meta').val('');
        DomCache.$feedback.find('input:radio').prop('checked',false);
        DomCache.$feedback.find('.ui-btn').removeClass('ui-btn-active ui-radio-on');
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
  DomCache.$feedback.bind('pagebeforeshow', function() {
    DomCache.$feedback.find('h3').text(feedbackSubject);
  });

  $('#loading').hide();

} // end init

function dataLoadError() {
  App.Notify('There was a problem loading events and guests data.');
}

function loadAppData(callback) {
  console.log('Loading new data from server');
  var ts = Math.round((new Date()).getTime() / 1000);
  Model.events = null;
  Model.guests = null;
  $.ajax({
    url: 'http://'+RootDomain+'/api/'+Model.cid+'/events',
    type: 'GET',
    dataType: 'json',
    success: function(data) {
      Model.events = data;
      localStorage.setItem(lsKeys.events, JSON.stringify(Model.events));
      if(Model.guests) {
        localStorage.setItem(lsKeys.lastUpdate, ts);
        callback.call();
      }
    }
  });
  $.ajax({
    url: 'http://'+RootDomain+'/api/'+Model.cid+'/guests',
    type: 'GET',
    dataType: 'json',
    success: function(data) {
      Model.guests = data;
      localStorage.setItem(lsKeys.guests, JSON.stringify(Model.guests));
      if(Model.events) {
        localStorage.setItem(lsKeys.lastUpdate, ts);
        callback.call();
      }
    } 
  });
}

$(function() {
  console.log("Waiting for device");
  document.addEventListener("deviceready", function() {

    $('#loading').find('p').text("Device ready!");

    if(localStorage.getItem(lsKeys.todo) == null) {
      localStorage.setItem(lsKeys.todo, '');
    }
    
    if(navigator && navigator.connection) {
      // Phonegap is running, try to get connection
      var nw = navigator.connection.type;

      if(nw == Connection.WIFI || nw == Connection.CELL_3G || nw == Connection.CELL_4G || nw == Connection.CELL_2G || nw == Connection.CELL) {
        // Got a connection - check for updates
        $('#loading').find('p').text("Checking for updates...");
        $.ajax({
          url: 'http://'+RootDomain+'/api/'+Model.cid,
          method: 'GET',
          dataType: 'json',
          success: function(resp) {
            if(!(localStorage.getItem(lsKeys.lastUpdate)) || resp.UpdateUT > localStorage.getItem(lsKeys.lastUpdate)) {
              $('#loading').find('p').text('New updates found! Downloading...');
              loadAppData(init);
            } else {
              $('#loading').find('p').text('New updates found! Downloading...');
              init(true);
            }
          },
          error: function() {
            App.Notify('Sorry, the update server could not be reached. Please restart the app and try again.');
          }
        });
      } else {
        // Phonegap is running, but no network - can't check for updates
        if(localStorage.getItem(lsKeys.events) && localStorage.getItem(lsKeys.guests)) {
          $('#loading').find('p').text("No network available. Loading from cache.");
          init(true);
        } else {
          //loadAppData(init);
          App.Notify('No network connection available! If this is your first time using the app, please make sure your device has an Internet connection and restart.');
        }
      }
    } else {
      // No Phonegap, must be a mobile browser. Download latest updates and go.
      $('#loading').find('p').text('Loading convention data...');
      loadAppData(init);
    }

  }, false); // end deviceready 
});
