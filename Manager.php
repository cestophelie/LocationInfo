<?php
  if(! defined('BASEPATH')) exit('No direct script access allowed');

  class Manager extends CI_Controller{

   public function __construct(){
      parent:: __construct();

      $this->load->helper('url');
      $this->load->model('Manager_model');
      $this->load->library("pagination");
    }

    function os(){//운영체제를 detect하는 함수. return 값을 배열에 넣어 DB에 insert한다.
      $str = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
      $os = 'Unidentified OS';

      if(preg_match('/android/i', $str)) {
        $os = 'Android';
      } elseif (preg_match('/linux/i', $str)) {
        $os = 'Linux';
      } elseif (preg_match('/iphone/i', $str)) {
        $os = 'iOS';
      } elseif (preg_match('/macintosh|mac os x/i', $str)) {
        $os = 'Mac';
      } elseif (preg_match('/windows|win32/i', $str)) {
        $os = 'Windows';
      }
      return $os;
    }

    public function sendArr(){//카테고리 중 중복 횟수가 많은 순서대로 정렬
      //Manager_main.php view의 send_Arr함수에서 post값을 받고 sort해서 다시 넘긴다.
        if($_POST["arrTest"]!=""){
            $receive = json_decode($_POST["arrTest"],true);
            $num = sizeof($receive);

            foreach ($receive as $key =>$row){//undefined인데 어떻게 $jsn값은 받는걸까
              $send[$key] = $row['send'];
              $count[$key] = $row['count'];
            }
          array_multisort($count, SORT_DESC, $count, SORT_ASC, $receive);//multisort를 안 써도 됨
          $jsn = json_encode($receive,JSON_UNESCAPED_UNICODE);
          print_r($jsn);
        }
    }

    public function Manager_popup(){//Manager_main에서 팝업을 띄우기 위해 사용
      $this->load->view('Manager_popup');
    }

    public function Manager_popup_submit(){//팝업 안에서 가공한 내용들을 post로 받고 DB에 insert
      $this->load->database();
      $this->load->model('Manager_model');

      $browser = '';//브라우저를 detect하는 부분. 이후 DB에 들어가는 배열의 요소
      if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
        $browser =  'Internet explorer';
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE)
        $browser =  'Internet explorer';
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE)
        $browser =  'Mozilla Firefox';
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== FALSE)
        $browser =  'Google Chrome';
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
        $browser =  "Opera Mini";
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== FALSE)
        $browser =  "Opera";
      elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== FALSE)
        $browser =  "Safari";
      else
        $browser = 'Something else';

      date_default_timezone_set('Asia/Seoul');//시간 한국 기준으로 세팅
      if(isset($_POST['search'])){//view에서 form 태그를 이용해 받아온 값들과 위의 브라우저 탐지 결과값을 DB에 넣는 과정
          $info = array(
          "search" => $_POST["search"],
          "send" => $_POST["result"],
          "browser" => $browser,
          "ip" => getHostByName(getHostName()),
          "device" => $_POST["device"],
          "os" => $this->os(),
          "date" =>date("Y년 n월 d일 h시 i분 s초 A")
          );
          $this->Manager_model->insert($info);
      }
    }

    public function index(){//메인화면
      $this->load->database();
      $this->load->model('Manager_model');
      $params = array();

      //paging 하는 부분
        $limit_per_page = 5;

        $start_index=($this->uri->segment(3))?$this->uri->segment(3) : 0;
        $total_records = $this->Manager_model->get_total();

        if($total_records > 0){
          $params["results"]=$this->Manager_model->get_current_page_records($limit_per_page, $start_index);
          $config['base_url']=base_url().'Manager/index';
          $config['total_rows']=$total_records;
          $config['per_page'] = $limit_per_page;
          $config["uri_segment"] = 3;
          $config['num_links'] = 5;
          $this->pagination->initialize($config);

          $params["links"] = $this->pagination->create_links();
        }
         $this->load->view('Manager_main',$params);
     }

    function custom(){
      $this->load->database();
      $this->load->model('Manager_model');
      //insert start
      $info = array( //각 key값은 테이블의 column
          "search" => $_POST["search"],
          "send" => $_POST["result"],
          "browser" => $browser,
          "ip" => getHostByName(getHostName()),
          //$_SERVER['REMOTE_ADDR'],
          "device" => $_POST["device"],
          "os" => php_uname().PHP_OS,
          "date" =>date("l jS \of F Y h:i:s A")
      );
      $this->Manager_model->insert($info);
      $params = array();
      $limit_per_page =5;
      $page=($this->uri->segment(3))?$this->uri->segment(3)-1 : 0;
      $total_records = $this->Manager_model->get_total();

      if($total_records > 0){
          $params["results"]=$this->Manager_model->get_current_page_records($limit_per_page, $page*$limit_per_page);

          $config['base_url']=base_url().'Manager/custom';
          $config['total_rows']=$total_records;
          $config['per_page'] = $limit_per_page;
          $config["uri_segment"] = 3;
          //custom paging config
          $config['num_links'] = 5;
          $config['use_page_numbers'] = TRUE;
          $config['reuse_query_string'] = TRUE;

          $config['full_tag_open'] = '<p>';
          $config['full_tag_close'] = '</p>';

          $config['first_link'] = ' ';
          $config['first_tag_open'] = '<div>';
          $config['first_tag_close'] = '</div>';

          $config['last_link'] = 'Last>';
          $config['last_tag_open'] = '<div>';
          $config['last_tag_close'] = '</div>';

          $config['next_link'] = '&gt';
          $config['next_tag_open'] = '<div>';
          $config['next_tag_close'] = '</div>';

          $config['prev_link'] = '&lt';
          $config['prev_tag_open'] = '<div>';
          $config['prev_tag_close'] = '</div>';

          $config['cur_tag_open'] = '<b>';
          $config['cur_tag_close'] = '</b>';

          $config['num_tag_open'] = '<div>';
          $config['num_tag_close'] = ' </div>';

        $this->pagination->initialize($config);
        $params["links"] = $this->pagination->create_links();
      }
    }
}
?>
