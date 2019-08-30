$(document).ready(function(){
  $('a.color').on('click',function() {
      var a = $(this).attr('data-value');
      document.getElementById('wil_css').href = 'css/w3-theme-' + a + '.css';
      $.post('inc/change.color.php', {
          'color': a
      })
  });
  $('input#btn_update').on('click',function(e){
    e.preventDefault();
    var n_kode=$('#n_kode').val();
    var n_lat=$('#n_lat').val();
    var n_lng=$('#n_lng').val();
    var n_kodepos=$('#n_kodepos').val();
    var poly=$('#poly').text();
    var d={kode:n_kode,lat:n_lat,lng:n_lng,kodepos:n_kodepos,path:poly}
    $.post(
      'inc/geo_ajax.php',d,
      function(data){
        var result=JSON.parse(data);
        pesan(result.msg);
      }
    );
  });
});