$(function() {
  console.log("test");
  $('#events-grid').find('tr').hover(function() {
    $(this).addClass('hover');
  },function() {
    $(this).removeClass('hover');
  });
});
