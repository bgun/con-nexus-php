var App = {};
var tkey = 'todo5';
var feedbackSubject = null;
var scheduleRendered = false;
var daysArray = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

var Data = {
	Model: {}
};

$(function() {
	buildSchedule();
	renderToDo();
});

function buildSchedule() {
	var d = []; // sorted events array
	var eventdays = [];
	var output = [];
	var day, dow, o;
	var dat;
	console.time("test");
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
				dow = new Date(day);
				dow = dow.getDay();
				pi = eventdays.length - 1;
				ni = eventdays.length + 1;
				output.push({
					index: eventdays.length, 
					previndex: pi,
					nextindex: ni,
					date: day,
					dayofweek: daysArray[dow],
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
	newdate = new Date(ds[0]);
	newdate = newdate.getDay();
	return daysArray[newdate] + " at " + getShortTime(ds[1]);
}

App.Notify = function(msg, callback, title, buttons) {
	if(navigator.notification) {
		navigator.notification.alert(msg, callback, title, buttons);
	} else {
		alert(msg);
	}
}
App.Confirm = function(msg, callback, title, buttons) {
	if(navigator.confirm) {
		return navigator.confirm.alert(msg, callback, title, buttons);
	} else {
		return confirm(msg);
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
		App.Notify("Event removed.", null, "My ToDo");
	} else {
		App.Notify("Invalid Event ID.", null, "My ToDo");
	}
}
function renderToDo(todoObjects) {
	var todoArray = getToDo();
	var todoObjects = [];
	var newobj;
	for(var i = 0; i < todoArray.length; i++) {
		newobj = Data.events.items[todoArray[i]];
		todoObjects.push(newobj);
	}
	$('#todo').remove();
	var html;
	if(todoObjects.length) {
		html = $('#todo-template').render({items: todoObjects});
	} else {
		html = $('#todo-template').render({});
	}
	$('body').append(html);
}
function clearToDo() {
	localStorage[tkey] = '';
	$('.todo-content').html('<p>Your ToDo list is empty.</p>');
}

/* Page init functions */

$('#guests').live('pagecreate', function() {
	var t = $('#guests');
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
	$('#guests .guests-list').html(html);
});

$('.guest-detail-link').live('click', function(e) {
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
	$('#guest-detail').remove();
	$('body').append(html);
	
	$.mobile.changePage('#guest-detail');
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
	$('#event-detail').remove();
	$('body').append(html);
	
	$.mobile.changePage('#event-detail');	
});

$('#map').live('pageinit', function() {	
	$('#map-zoom-in').live('click', function(e) {
		var cw = $('#map-image').outerWidth();
		$('#map-image').css({
			width: cw * 1.2
		});
	});

	$('#map-zoom-out').live('click', function(e) {
		var cw = $('#map-image').outerWidth();
		$('#map-image').css({
			width: cw * 0.8
		});
	});
});

$('#twitter').live('pageinit', function(e) {
	$("#list-tweets").tweet({
	avatar_size: 48,
	count: 20,
	query: "jordancon",
	loading_text: "searching twitter...",
	template: "{avatar}{user}{text}{time}"
	});
});


/* Todo page */

$('a.todo-add').live('click', function(e){
	e.preventDefault();
	var id = $(this).attr('data-eventid');
	addToDo(id);
});
$('.todo-link').live('click', function(e) {
	e.preventDefault();
	
	renderToDo();
	
	$('#todo').on('click', 'a.todo-remove', function(e){
		e.preventDefault();
		var id = $(this).attr('data-eventid');
		removeToDo(id);
		$('.todo-item-'+id).remove();
		if($('.todo-list li').length == 0) {
			clearToDo();
		}
	});
	$('#todo').on('click', 'a.todo-clear', function(e){
		e.preventDefault();
		if(App.Confirm("Are you sure you want to delete all items from your to-do list?", null, "Delete All ToDo Items", "Yes,No")) {
			clearToDo();
		}
	});
	
	$.mobile.changePage('#todo');	
	console.time("todo render");
});


/* Feedback page */

$('.feedback-link').live('click', function(e) {
	feedbackSubject = "";
	if($(this).hasClass('dashboard')) {
		feedbackSubject = 'General Feedback for ' + Convention.Name;
	} else {
		feedbackSubject = 'Feedback for "' + $('#event-detail h3').text() + '"';
	}
});
$('#feedback-form a').live('click', function(e) {
	e.preventDefault();
	$(this).addClass('ui-disabled');
	var content = $('#feedback-form .content').val();
	var meta    = feedbackSubject + " [cid "+Convention.ConventionID+"]";
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
			$('#feedback-form a').removeClass('ui-disabled');
			$('#feedback-form .content').val('');
			$('#feedback-form .meta').val('');
			if(resp.status == 'OK') {
				App.Notify("Your feedback has been submitted.");
			} else {
				App.Notify(resp.error, null, "Thank you!", "Great!");
			}
		},
		error: function() {
			App.Notify("There was an error submitting feedback.");
		}
	});
});
$('#feedback').live('pagebeforeshow', function() {
	$('#feedback h3').text(feedbackSubject);
});
