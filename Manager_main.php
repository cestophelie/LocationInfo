<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>
  <form method ="POST" ID="sear_name">
      <input id = "input" type="text" name="search1" placeholder="검색어를 입력하세요">
  </form>
  <button onclick="search()">검색하기</button>
  <form id="search_form" name="search_form" action="/manager/Manager_popup/1" method="post">
    <input id="mode" type="hidden" name="mode" value="search">
    <input id="search" name="search" type="hidden"  value="">
    <input id="result" name="result" type="hidden"  value="">
    <input id="device" name="device" type="hidden" value="">
    <input id="arrTest" name="arrTest" type="hidden" value="">
    <input id="popup" name = "popup" type="hidden" value ="">
  </form>
<script>
var string_obj_array;
function search(){//검색어 onclick시 실행되는 함수
  $("#mode").val("search");//mode form의 value가 popup으로 설정되어있을 때만 popup으로 이동하도록 하는 설정
  var str = document.getElementById('input').value;
  if(str==""){
    alert("값을 입력해주세요");//검색어를 입력하지 않고 검색을 시도한 경우
    return;
  }
  check_mobile_device();//접속한 사람의 디바이스 정보. form tag로 post
  searchKey(str,1);

  $("#search").val(str);

}
var arr=new Array();
function searchKey(q, paging){
  $.ajax({
    url : 'https://dapi.kakao.com/v2/local/search/keyword.json',
    headers : { 'Authorization' : 'KakaoAK f462fbf3c358c26006905776a3d9162f'	},
    type: 'GET',
    data : { 'query' : q,
              'page' : paging},
    success : function(data){
            if(data.documents.length != 0 ){ //검색결과가 있으면 카테고리를 arr 배열에 push
                var i=0;
                while(i<data.documents.length){
                  arr.push(data.documents[i].category_name);
                  i++;
                }
            }
            else{
              alert("검색 결과가 없습니다.");
            }
            if(paging==1){
              searchKey(q,2);
            }
            if(paging==2){
              searchKey(q,3)
            }
            if(paging == 3){//카카오에서 최대 로드할 수 있는 페이지가 3개
              popArray(arr);//arr에 넣은 카테고리 중 중복되는 카테고리를 제거
            }
    },

    error:function(request,status,error){
        alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
    }
  })
}

var send ="";
var obj_array = new Array();
var obj = {};//json값으로 묶어서 post할 객체들

function popArray(arr){
  var i=0;
  var j=0;
  if(arr.length>1){
      for(i=0;i<(arr.length-1);i++){
        var num=1;
        for(j=i+1;j<arr.length;j++){
          if(arr[i]==arr[j]){
            arr.splice(j,1);//중복되는 카테고리들은 빼고 중복의 횟수를 num에 저장한다.
            num++;
            j--;
          }
        }
        if((i<=(arr.length-2))||(arr.length==1)){
          send = arr[i];
        }
        else{
          send = arr[i+1];
        }
        if(i!=(arr.length-1)){
          obj_array.push({"send" : send, "count" : num});
        }
      }
  }

    if(arr.length!=1){
        send = arr[arr.length-1];
        obj_array.push({"send" : send, "count" : num});
    }
    string_obj_array = JSON.stringify(obj_array);
    $("#arrTest").val(string_obj_array);
    send_Arr();
}

function send_Arr(){//중복된 카테고리가 제거된 배열을 반복횟수(count)가 큰 순서로 내림차순
    var params = $("#search_form").serialize();
    $.ajax({
      url : "/Manager/sendArr",
      type : "POST",
      data : params,
      dataType : 'html',
      success : function(data){

        popup(data); //내림차순한 하고 다시 json포맷으로 바꾼 data
      },
        error : function(request, status, error){
          console.log("failed in sendArr");
        }
      })
}
var popupArr = new Array();

function popup(data){
    var obj = JSON.parse(data);
    //어떤 데이터들이 여기서 parse가 안된다.
    for(var i = 0; i<obj.length; i++){
        popupArr.push({"send" : obj[i].send});//count값은 더이상 필요 없어 send값만으로 배열
    }
    var string_popupArr = JSON.stringify(popupArr);
    $("#popup").val(string_popupArr);//Controller의 sendArr에서 받은 sorted된 배열 값 중 send column만 받는다.
    $("#mode").val("popup");//value가 search에서 popup으로 바뀔 시 submit 및 팝업창
    $("#search_form").on('submit', function(){
      if ($("#mode").val() == "popup") {
        window.open('/manager/Manager_popup', 'search_form', 'width=500,height=500,resizeable,scrollbars');
        this.target = 'search_form';
      }
    })
    $("#search_form").submit();
}

//device를 조사하는 부분
function isTablet(){
    if((navigator.userAgent.match(/iPad/i) != null)){
      return "iPad";
    }
    else{
      return "default";
    }
}
function check_mobile_device(){//모바일인지 먼저 확인하고 아닌 경우 pc, tablet으로 나누어 탐지
  var mobileKeyWords = new Array('iPhone', 'iPod', 'BlackBerry', 'Android', 'Windows CE', 'LG', 'MOT', 'SAMSUNG', 'SonyEricsson');
  var device_name = 'not a mobile device';
  for (var word in mobileKeyWords){
      if (navigator.userAgent.match(mobileKeyWords[word]) != null){
        device_name = mobileKeyWords[word];
        break;
      }
  }
  if (device_name=="not a mobile device"){
    if(isTablet()!="default"){
      device_name =isTablet();
    }
    else{
      device_name = "PC";
    }
  }
  document.getElementById("device").value = device_name;
  return device_name;

}
</script>
<?php
if (isset($results)) { ?> <!--DB에 저장된 테이블의 값을 테이블로 보여주는 부분-->
                <table id="myTable" border="1" cellpadding="0" cellspacing="0">
                    <tr>
                        <th>idx</th>
                        <th>Search</th>
                        <th>Send</th>
                        <th>Browser</th>
                        <th>IP</th>
                        <th>Device</th>
                        <th>OS</th>
                        <th>Date</th>
                    </tr>
          <?php
        }
          ?>
<?php
  foreach($results as $entry){
?>
<tr>
  <td><?php echo $entry->idx?></td>
  <td><?php echo $entry->search?></td>
  <td><?php echo $entry->send?></td>
  <td><?php echo $entry->browser?></td>
  <td><?php echo $entry->ip?></td>
  <td><?php echo $entry->device?></td>
  <td><?php echo $entry->os?></td>
  <td><?php echo $entry->date?></td>
</tr>

<?php } ?>
</table>
<?php
if(isset($links)){?><!--Paging 링크-->
<?php echo $links
?>
<?php
}
?>
</body>
</html>
