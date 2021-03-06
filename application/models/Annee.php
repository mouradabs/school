<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Annee extends CI_Model
{

  public $table = 'annee_scolaires';
  public $pk = 'id';
  public $search_columns = [ 'label' ];


  function __construct()
  {
      parent::__construct();
  }

  // get all
  function get_all($limit = 15, $start = 0, $q = null)
  {
      return $this->get_limit_data($limit, $start, $q);
  }

  // get data by id
  function get_by_id($id)
  {
      return $this->db->get_where($this->table, [$this->pk => $id])->row();
  }
  
  // get total rows
  function total_rows($q = NULL) {
    if ( $q ) {
      foreach ( $this->search_columns as $col ) {
        $this->db->or_like($col, $q);
      }
    }
     
    return $this->db->count_all_results($this->table);
  }

  // get data with limit and search
  function get_limit_data($limit, $start = 0, $q = NULL) {
    if ( $q ) {
      foreach ( $this->search_columns as $col ) {
        $this->db->or_like($col, $q);
      }
    }
    
    $this->db->order_by('label', 'DESC');
    return $this->db->limit($limit, $start)->get($this->table)->result();
  }

  // insert data
  function insert($data)
  {
    if ( $data['active'] ) $this->deactivateAll();
    
    $result = $this->db->insert($this->table, $data);
    
    if ( $result ) {
      $id = $this->db->insert_id();
      
      $this->load->model('Semestre');
      
      $this->db->insert_batch($this->Semestre->table, [
        ['label' => 'Semestre 1', 'active' => TRUE, 'id_annee' => $id],
        ['label' => 'Semestre 2', 'active' => FALSE, 'id_annee' => $id]
      ]);
    }
    
    return $result;
  }

  // update data
  function update($id, $data)
  {
    if ( $data['active'] ) $this->deactivateAll();
    
    return $this->db->where($this->pk, $id)->update($this->table, $data);
  }

  // delete data
  function delete($id)
  {
      return $this->db->where($this->pk, $id)->delete($this->table);
  }
  
  function get_list()
  {
    $results = $this->db->select(['id', 'label'])->get($this->table)->result();
    
    return array_reduce($results, function ($memo, $item) {
      $memo[$item->id] = $item->label;
      
      return $memo;
    }, []);
  }
  
  // only a list of active academic years
  function get_active_list() {
    $results = $this->db->select(['id', 'label'])->where('active', TRUE)->get($this->table)->result();
    
    return array_reduce($results, function ($memo, $item) {
      $memo[$item->id] = $item->label;
      
      return $memo;
    }, []);
  }
  
  function get_active() {
    return $this->db->limit(1)->select(['id', 'label'])->where('active', TRUE)->get($this->table)->first_row();
  }
  
  // activate the given year id
  function activate($id) {
    return $this->update($id, ['active' => TRUE]);
  }
  
  // deactivate all academic years
  function deactivateAll() {
    return $this->db->set('active', FALSE)->update($this->table);
  }
  
  function get_semestres($id) {
    return $this->db->where('id_annee', $id)->get('semestres')->result();
  }
  
  function get_semestres_ids($id_annee) {
    return array_map(function ($item) { return $item->id; }, $this->get_semestres($id_annee));
  }

}
