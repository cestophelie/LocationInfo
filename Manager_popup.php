<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>
  <h2>카테고리</h2>
  <p>해당 카테고리를 고르세요</p>
  <table id="tbl" border="1" cellpadding="0" cellspacing="0">
    <tr>
      <th>Check</th>
      <th>Category</th>
    </tr>
<?php
    $params = json_decode($_POST["popup"],true);
    for($i = 0; $i < sizeof($params); $i++){
?>
    <tr>
      <td><input name="check" type="checkbox" value =""></td>
      <td><?php echo $params[$i]['send']?></td>
    </tr>
<?php
    }
?>
    </table>
    <br>
    <form id="search_form" action="" method="post"><!--메인 뷰의 form 값. 팝업에서 insert를 실행하기 위해 가져옴-->
      <input id="search" name="search" type="hidden"  value="<?php echo $_POST["search"]; ?>">
      <input id="result" name="result" type="hidden"  value="<?php echo $_POST["result"]; ?>">
      <input id="device" name="device" type="hidden" value="<?php echo $_POST["device"]; ?>">
    </form>
    <button onclick="categorySubmit();">선택</button>
    <form id="categoryForm" action="" method="post">
        <input id="categorySubmit" name="categorySubmit" type="hidden" value="ugotthis">
    </form>

  <script>
  function categorySubmit(){
  //checked 된 row의 카테고리 값을 #result에 넣는다.
    var table, rows, x, length;

    table = document.getElementById("tbl");
    length = table.getElementsByTagName("tr").length - 1;
    rows = tbl.rows;
    var i=1;
    var selectedCategory;
    $('#tbl tr td input:checkbox').each(function() {
       if (this.checked) {
           selectedCategory = rows[i].getElementsByTagName("td")[1].innerHTML;
        }
        i++;
    });
    $("#result").val(selectedCategory);

    var gotThis = $("#search_form").serialize();
    $.ajax({
      url : "/Manager/Manager_popup_submit",//DB에 insert하는 url
      type : "POST",
      data : gotThis,
      dataType : 'html',
      success : function(data){
          opener.parent.location.reload();
          window.close();
      },
      error : function(request, status, error){
        console.log("failed in sendArr");
        alert("failed in sendArr");
        opener.parent.location.reload();
        window.close();
      }
    })


  }
  </script>
</body>
</html>
