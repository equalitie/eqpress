$(function(){
  var count = 10;
  countdown = setInterval(function(){
    $('#countdown').html(count);
    if (count == 0) {
      window.location = 'http://google.com';
    }
    count--;
  }, 1000);
});