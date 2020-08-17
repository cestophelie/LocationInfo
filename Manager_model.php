<?php
  class Manager_model extends CI_Model{
      public $table ='manager';
  function __construct(){
      parent::__construct();

  }
  //DB의 manager 테이블을 사용
  public function get_total(){
      return $this->db->count_all("manager");
  }


  public function insert($info){//manager table에 컨트롤러(Manager_popup_submit)의 info 배열 값을 넣음.
      $this->db->insert('manager', $info);
  }

  public function get_current_page_records($limit,$start){//한 페이지 당 로딩하는 페이지 제한
      $this->db->limit($limit,$start);
      $this->db->order_by('idx','DESC');
      $query = $this->db->get("manager");

      if($query->num_rows()>0){
        foreach($query->result() as $row){
          $data[] = $row;
        }
        return $data;
      }
      return false;
    }
  }

?>
