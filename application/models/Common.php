<?php


class Common extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }
    public function one_cond_between($table, $col, $val, $con, $minvalue, $maxvalue)
    {
        $this->db->where($col, $val);
        $this->db->where("$con BETWEEN '$minvalue' AND '$maxvalue'");
        $query = $this->db->get($table);
        return $query->result();
    }

    // common functions loop

    public function no_cond($table)
    {
        $query = $this->db->get($table);
        return $query->result();
    }

    public function one_cond($table, $col, $val)
    {
        $this->db->where($col, $val);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function two_cond($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }
    public function three_cond($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function four_cond($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function four_cond_or($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $orcol, $orval, $orcol2, $orval2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->or_where($orcol, $orval);
        $this->db->or_where($orcol2, $orval2);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function one_cond_or($table, $col, $val, $orcol, $orval)
    {
        $this->db->where($col, $val);
        $this->db->or_where($orcol, $orval);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function one_cond_two_or($table, $col, $val, $orcol, $orval, $orcol2, $orval2)
    {
        $this->db->where($col, $val);
        $this->db->or_where($orcol, $orval);
        $this->db->or_where($orcol2, $orval2);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function five_cond($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $col5, $val5)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function six_cond($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $col5, $val5, $col6, $val6)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        $this->db->where($col6, $val6);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function three_cond_not_equal($table, $col, $val, $col2, $val2, $col3, $val3, $colob, $colobv)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->order_by($colob, $colobv);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function four_cond_not_equal($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $colob, $colobv)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4 . ' != ', $val4);
        $this->db->order_by($colob, $colobv);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function three_cond_not_equal_gb($table, $col, $val, $col2, $val2, $col3, $val3, $colob, $colobv, $gb)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->order_by($colob, $colobv);
        $this->db->group_by($gb);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }



    // one condation order by
    public function no_cond_order_by($table, $orderby, $orderbyvalue)
    {
        $this->db->order_by($orderby, $orderbyvalue);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }
    public function one_cond_loop_order_by($table, $col, $val, $orderby, $orderbyvalue)
    {
        $this->db->where($col, $val);
        $this->db->order_by($orderby, $orderbyvalue);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function two_cond_order_by($table, $col, $val, $col2, $val2, $orderby, $orderbyvalue)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->order_by($orderby, $orderbyvalue);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function three_cond_order_by($table, $col, $val, $col2, $val2, $col3, $val3, $orderby, $orderbyvalue)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->order_by($orderby, $orderbyvalue);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function three_cond_order_by_or($table, $col, $val, $col2, $val2, $col3, $val3, $orderby, $orderbyvalue)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->order_by($orderby, $orderbyvalue);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    // one condation group by

    public function no_cond_group($table, $valcol)
    {
        $this->db->group_by($valcol);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function no_cond_group_ob($table, $valcol,$ob, $obval)
    {
        $this->db->group_by($valcol);
        $this->db->order_by($ob, $obval);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function one_cond_group_ob($table,$col, $val, $valcol,$ob, $obval)
    {
        $this->db->where($col, $val);
        $this->db->group_by($valcol);
        $this->db->order_by($ob, $obval);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function one_cond_group($table, $col, $val, $valcol)
    {
        $this->db->where($col, $val);
        $this->db->group_by($valcol);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function two_cond_group($table, $col, $val, $col2, $val2, $valcol)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->group_by($valcol);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function three_cond_group($table, $col, $val, $col2, $val2, $col3, $val3, $gb)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->group_by($gb);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function get_last_row($table, $ob, $obv)
    {
        $query = $this->db->order_by($ob, $obv)->limit(1)->get($table);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }




    // common function single row

    public function one_cond_row($table, $col, $val)
    {
        $this->db->where($col, $val);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function two_cond_row($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function three_cond_row($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function two_cond_not_equal_row($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $result = $this->db->get($table)->row();
        return $result;
    }



    public function three_cond_not_equal_row($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function one_cond_row_desc($table, $col, $val, $ob, $obv)
    {
        $this->db->where($col, $val);
        $this->db->order_by($ob, $obv);
        $result = $this->db->get($table)->row();
        return $result;
    }


    // common function count

    public function no_cond_count_row($table)
    {
        $result = $this->db->get($table);
        return $result;
    }

    public function one_cond_count_row($table, $col, $val)
    {
        $this->db->where($col, $val);
        $result = $this->db->get($table);
        return $result;
    }


    public function two_cond_count_row($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $result = $this->db->get($table);
        return $result;
    }

    public function three_cond_not_equal_count_row($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $result = $this->db->get($table);
        return $result;
    }

    public function one_cond_ne_one_cond_count_row($table, $col, $val, $col2, $val2)
    {
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $result = $this->db->get($table);
        return $result;
    }

    public function three_cond_count_row($table, $col, $val, $col2, $val2, $col3, $val3)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $result = $this->db->get($table);
        return $result;
    }

    public function four_cond_count_row($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $result = $this->db->get($table);
        return $result;
    }
    public function five_cond_count_row($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $col5, $val5)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        $result = $this->db->get($table);
        return $result;
    }

    public function four_cond_count_row_one_not_equal($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4 . ' != ', $val4);
        $result = $this->db->get($table);
        return $result;
    }

    public function two_cond_count_row_gb($table, $col, $val, $col2, $val2,$gb)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->group_by($gb);
        $result = $this->db->get($table);
        return $result;
    }

    public function three_cond_count_row_gb($table, $col, $val, $col2, $val2, $col3, $val3, $gb)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->group_by($gb);
        $result = $this->db->get($table);
        return $result;
    }

    //join table

   public function two_join_ob($t1, $t2, $select, $joinby, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_one_cond($t1, $t2, $select, $joinby, $col, $val, $gb, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->group_by($gb);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_one_cond_not_gb($t1, $t2, $select, $joinby, $col, $val, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_three_cond($t1, $t2, $select, $joinby, $col, $val, $col2, $val2, $col3, $val3, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->order_by($ob, $obval);
        $sql = $this->db->get_compiled_select();
        error_log("two_join_three_cond SQL: " . $sql);
        $query = $this->db->query($sql);
        $result = $query->result();
        error_log("two_join_three_cond - t1: $t1, t2: $t2, col: $col, val: $val, col2: $col2, val2: $val2, col3: $col3, val3: $val3, count: " . count($result));
        return $result;
    }

    public function two_join_four_cond($t1, $t2, $select, $joinby, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }
    

    public function two_join_five_cond($t1, $t2, $select, $joinby, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $col5, $val5, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function three_join_one_cond($t1, $t2, $t3, $select, $joinby, $joinby2, $col, $val, $gb, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->join($t3 . ' as c', $joinby2, 'left');
        $this->db->where($col, $val);
        $this->db->group_by($gb);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_one_ne_cond($t1, $t2, $select, $joinby, $col, $val, $gb, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col . ' != ', $val);
        $this->db->group_by($gb);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_two_cond($t1, $t2, $select, $joinby, $col, $val, $col2, $val2, $ob, $obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_two_cond_gb($t1, $t2, $select, $joinby, $col, $val, $col2, $val2, $ob, $obval,$gb)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->group_by($gb);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_seven_cond($t1, $t2, $select, $joinby, $col, $val, $col2, $val2,$col3, $val3,$col4, $val4,$col5, $val5,$col6, $val6,$col7, $val7,$ob,$obval)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        $this->db->where($col6, $val6);
        $this->db->where($col7, $val7);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get();
        return $query->result();
    }

    public function two_join_two_cond_count($t1, $t2, $select, $joinby, $col, $val, $col2, $val2)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $query = $this->db->get();
        return $query;
    }

    public function two_join_seven_cond_count($t1, $t2, $select, $joinby, $col, $val, $col2, $val2,$col3, $val3,$col4, $val4,$col5, $val5,$col6, $val6,$col7, $val7)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $this->db->where($col5, $val5);
        $this->db->where($col6, $val6);
        $this->db->where($col7, $val7);
        $query = $this->db->get();
        return $query;
    }

    

    public function two_join_two_cond_two_ne_cond_count($t1, $t2, $select, $joinby, $col, $val, $col2, $val2,$col3, $val3,$col4, $val4)
    {
        $this->db->select($select);
        $this->db->from($t1 . ' as a');
        $this->db->join($t2 . ' as b', $joinby, 'left');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->where($col4 . ' != ', $val4);
        $query = $this->db->get();
        return $query;
    }


    public function getStudeProfiles()
    {
        $this->db->where('schoolID', $this->session->username);
        $this->db->order_by('LastName', 'ASC');
        $query = $this->db->get('studeprofile');
        return $query->result();
    }




    //common function

    public function delete($table, $col_id, $segment)
    {
        $id = $this->uri->segment($segment);
        $this->db->where($col_id, $id);
        $this->db->delete($table);
        return true;
    }

    function delete_with_attach($table, $segment, $attach)
    {
        $this->db->where('id', $segment);
        $file = FCPATH . 'uploads/' . basename((string) $attach);
        if ($attach !== '' && is_file($file)) {
            unlink($file);
        }
        $this->db->delete($table);
    }

    function delete_with_attachv2($table, $segment, $folder, $attach)
    {
        $this->db->where('id', $segment);
        $file = rtrim((string) $folder, '/\\') . DIRECTORY_SEPARATOR . basename((string) $attach);
        if ($attach !== '' && is_file($file)) {
            unlink($file);
        }
        $this->db->delete($table);
    }


    public function tcd($table, $col, $val, $col2, $val2)
    { // two cond delete
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->delete($table);
        return true;
    }

    public function del($table, $col, $val)
    { // one cond delete
        $this->db->where($col, $val);
        $this->db->delete($table);
        return true;
    }

    public function rqa($table, $jobID)
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $table) || !$this->db->table_exists($table)) {
            throw new InvalidArgumentException('Invalid rating table.');
        }
        $query = $this->db->query(
            "SELECT * FROM hris_applications a JOIN `{$table}` r ON a.appID = r.appID WHERE jobID = ? AND total_points >= 50 ORDER BY total_points DESC",
            array($jobID)
        );
        return $query->result();
    }

    public function rqa_non($jobID)
    {

        $query = $this->db->query(
            'SELECT * FROM hris_applications a JOIN hris_rating_none r ON a.appID = r.appID WHERE jobID = ? AND dq = 1 ORDER BY total_points DESC',
            array($jobID)
        );
        return $query->result();
    }

    public function check_application($col2, $val2)
    {
        $this->db->select("YEAR(dateSubmitted) AS year, COUNT(*) AS count");
        $this->db->from("hris_applications");
        $this->db->where($col2, $val2);
        $this->db->group_by("year");
        $this->db->order_by("year", "DESC");
        $result = $this->db->get();
        return $result;
    }

    public function one_cond_and_two_cond_ne_ob($table, $col, $val, $col2, $val2, $col3, $val3,$ob,$obval)
    {
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->order_by($ob, $obval);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
        
    }

    public function one_cond_and_two_cond_ne_ob_select($table,$select, $col, $val, $col2, $val2, $col3, $val3,$ob,$obval)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->order_by($ob, $obval);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
        
    }

    public function two_cond_and_two_cond_ne_ob_select($table,$select, $col, $val, $col2, $val2, $col3, $val3,$col4, $val4,$ob,$obval)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3 . ' != ', $val3);
        $this->db->where($col4 . ' != ', $val4);
        $this->db->order_by($ob, $obval);

        $query = $this->db->get($table);

        return $query->result(); 
    }

    public function one_cond_and_one_cond_ne_ob_select($table,$select, $col, $val, $col2, $val2,$ob,$obval)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $this->db->order_by($ob, $obval);

        $query = $this->db->get($table);

        return $query->result(); 
        
    }

    public function one_cond_and_one_cond_ne_ob_select_gb($table,$select, $col, $val, $col2, $val2,$ob,$obval,$gb)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2 . ' != ', $val2);
        $this->db->group_by($gb);
        $this->db->order_by($ob, $obval);

        $query = $this->db->get($table);

        return $query->result(); 
        
    }

    public function one_cond_row_select($table,$select, $col, $val)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $result = $this->db->get($table)->row();
        return $result;
    }
    public function two_cond_row_select($table,$select, $col, $val,$col2, $val2)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $result = $this->db->get($table)->row();
        return $result;
    }

    public function no_cond_select($table,$select)
    {
        $this->db->select($select);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function no_cond_select_ob($table,$select,$ob,$obval)
    {
        $this->db->select($select);
        $this->db->order_by($ob, $obval);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function one_cond_select($table,$select, $col, $val)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function two_cond_select($table,$select, $col, $val,$col2, $val2)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function one_cond_select_gb($table,$select, $col, $val,$gb)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->group_by($gb);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function two_cond_select_gb($table,$select, $col, $val,$col2, $val2,$gb)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->group_by($gb);
        $query = $this->db->get($table);
        return $query->result();
    }
    

    public function three_cond_select($table,$select, $col, $val,$col2,$val2,$col3,$val3)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function three_cond_two_not_equal($table, $col, $val, $col2, $val2, $col3, $val3, $col4, $val4, $col5, $val5)
    {
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4 . ' != ', $val4);
        $this->db->where($col5 . ' != ', $val5);
        
        $query = $this->db->get($table);
        
        return $query->result(); 
    }

    public function four_cond_select($table,$select, $col, $val,$col2,$val2,$col3,$val3,$col4,$val4)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->where($col4, $val4);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function two_cond_select_limit($table,$select, $col, $val,$col2, $val2,$limit)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->limit($limit);
        $query = $this->db->get($table);
        return $query->result();
    }

    function getLevel()
	{
		$this->db->select('Major');
		$this->db->distinct();
		$this->db->order_by('Major', 'ASC');
		$query = $this->db->get('course_table');
		return $query->result();
	}

    public function column_count_three_cond($table,$col,$val,$col2,$val2,$col3,$val3){
        return $this->db->where($col, $val)
                    ->where($col2, $val2)
                    ->where($col3, $val3)
                    ->count_all_results($table);
    }

    public function column_count_four_cond($table,$col,$val,$col2,$val2,$col3,$val3,$col4,$val4){
        return $this->db->where($col, $val)
                    ->where($col2, $val2)
                    ->where($col3, $val3)
                    ->where($col4, $val4)
                    ->count_all_results($table);
    }

    

    // need to erase
    public function two_cond_groupby($table, $group_by_field, $field1, $value1, $field2, $value2)
    {
        $this->db->select($group_by_field);
        $this->db->where($field1, $value1);
        $this->db->where($field2, $value2);
        $this->db->group_by($group_by_field);
        $query = $this->db->get($table);
        return $query->result();
    }

    
    public function two_cond_result($table, $field1, $value1, $field2, $value2)
    {
        $this->db->where($field1, $value1);
        $this->db->where($field2, $value2);
        $query = $this->db->get($table);
        return $query->result();
    }

    public function count_students_by_year_and_sy($col,$val,$col2,$val2)
    {
        $this->db->select('SY, YearLevel, COUNT(*) as total_students');
        $this->db->from('semesterstude');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->group_by(['YearLevel']);
        $this->db->order_by('YearLevel', 'ASC');
        $query = $this->db->get();

        return $query->result();
    }

    public function count_students_by_age($col,$val,$col2,$val2,$col3,$val3)
    {
        $this->db->select('age,YearLevel, COUNT(*) as total_students');
        $this->db->from('semesterstude');
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $this->db->where($col3, $val3);
        $this->db->group_by(['age']);
        $this->db->order_by('age', 'ASC');
        $query = $this->db->get();

        return $query->result();
    }

    public function get_applicant_staff($jobID)
    {
        $this->db->select("
        a.*,
        staff.stat AS ren,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.perProvince, staff.perProvince) AS province,
        COALESCE(app.jhss, staff.jhss) AS jhss,
        COALESCE(app.shss, staff.shss) AS shss,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    //$this->db->join('hris_applications_rating r', 'a.appID = r.appID', 'left');
    $this->db->where('a.jobID', $jobID);
    $this->db->where('a.dq', 0);

    $query = $this->db->get();
    return $query->result();
    }

    public function get_applicant_staff_validated($jvStatus,$appStatus)
    {
        $this->db->select("
        a.*,
        j.jvStatus,j.jobID,j.job_type,j.jobTitle,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.perProvince, staff.perProvince) AS province,
        COALESCE(app.jhss, staff.jhss) AS jhss,
        COALESCE(app.shss, staff.shss) AS shss,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    $this->db->join('hris_jobvacancy j', 'a.jobID = j.jobID', 'left');
    $this->db->where('j.jvStatus', $jvStatus);
    $this->db->where('a.appStatus', $appStatus);

    $query = $this->db->get();
    return $query->result();
    }

    public function get_applicant_staff_dq($jvStatus,$dq)
    {
        $this->db->select("
        a.*,
        j.jvStatus,j.jobID,j.job_type,j.jobTitle,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.perProvince, staff.perProvince) AS province,
        COALESCE(app.jhss, staff.jhss) AS jhss,
        COALESCE(app.shss, staff.shss) AS shss,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    $this->db->join('hris_jobvacancy j', 'a.jobID = j.jobID', 'left');
    $this->db->where('j.jvStatus', $jvStatus);
    $this->db->where('a.dq', $dq);

    $query = $this->db->get();
    return $query->result();
    }

    public function get_submitted_applicant($jobID)
    {
        $this->db->select("
        a.*,
        j.jvStatus,j.jobID,j.job_type,j.jobTitle,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.resProvince, staff.resProvince) AS province,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        COALESCE(app.age, staff.age) AS age,
        COALESCE(app.Sex, staff.Sex) AS sex,
        COALESCE(app.MaritalStatus, staff.MaritalStatus) AS ms,
        COALESCE(app.empEmail, staff.empEmail) AS email,
        COALESCE(app.contactNo, staff.contactNo) AS cn,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    $this->db->join('hris_jobvacancy j', 'a.jobID = j.jobID', 'left');
    $this->db->where('a.jobID', $jobID);

    $query = $this->db->get();
    return $query->result();
    }

    public function get_endorsed_applicant($jvStatus,$dq,$district,$jobID)
    {
        $this->db->select("
        a.*,
        j.jvStatus,j.jobID,j.job_type,j.jobTitle,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.perProvince, staff.perProvince) AS province,
        COALESCE(app.jhss, staff.jhss) AS jhss,
        COALESCE(app.shss, staff.shss) AS shss,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    $this->db->join('hris_jobvacancy j', 'a.jobID = j.jobID', 'left');
    $this->db->where('j.jvStatus', $jvStatus);
    $this->db->where('a.dq', $dq);
    $this->db->where('a.district', $district);
    $this->db->where('a.jobID', $jobID);

    $query = $this->db->get();
    return $query->result();
    }

    public function travel_for_action()
    {
        $this->db->select('tr.*, ts.position');
        $this->db->from('travel_requests tr');
        $this->db->join(
            '(SELECT t1.*
            FROM travel_sign_settings t1
            INNER JOIN (
                SELECT user_id, MAX(id) AS max_id
                FROM travel_sign_settings
                GROUP BY user_id
            ) t2 ON t1.user_id = t2.user_id AND t1.id = t2.max_id
            ) ts',
            'tr.IDNumber = ts.user_id',
            'left'
        );
        //$this->db->where('tr.status', 'Endorsed');
        
        $query = $this->db->get();

        return $query->result();
    }

    public function get_applicant_by_appstatus($jobID,$appStatus,$dq)
    {
        $this->db->select("
        a.*,
        j.jvStatus,j.jobID,j.job_type,j.jobTitle,
        COALESCE(app.record_no, staff.IDNumber) AS code,
        COALESCE(app.id, staff.IDNumber) AS id,
        COALESCE(app.empEmail, staff.IDNumber) AS renren,
        COALESCE(app.contactNo, staff.contactNo) AS contactNo,
        COALESCE(app.FirstName, staff.FirstName) AS FirstName,
        COALESCE(app.MiddleName, staff.MiddleName) AS MiddleName,
        COALESCE(app.NameExtn, staff.NameExtn) AS NameExtn,
        COALESCE(app.LastName, staff.LastName) AS LastName,
        COALESCE(app.resProvince, staff.resProvince) AS province,
        COALESCE(app.resCity, staff.resCity) AS resCity,
        COALESCE(app.resBarangay, staff.resBarangay) AS brgy,
        COALESCE(app.age, staff.age) AS age,
        COALESCE(app.Sex, staff.Sex) AS sex,
        COALESCE(app.MaritalStatus, staff.MaritalStatus) AS ms,
        COALESCE(app.empEmail, staff.empEmail) AS email,
        COALESCE(app.contactNo, staff.contactNo) AS cn,
        CASE
            WHEN app.record_no IS NOT NULL THEN 'ma'
            WHEN staff.IDNumber IS NOT NULL THEN 'ma_staff'
            ELSE 'unknown'
        END AS st
    ");
    $this->db->from('hris_applications a');
    $this->db->join('hris_applicant app', 'a.empEmail = app.empEmail', 'left');
    $this->db->join('hris_staff staff', 'app.record_no IS NULL AND a.empEmail = staff.IDNumber', 'left');
    $this->db->join('hris_jobvacancy j', 'a.jobID = j.jobID', 'left');
    $this->db->where('a.jobID', $jobID);
    $this->db->where('a.appStatus', $appStatus);
    $this->db->where('a.dq', $dq);

    $query = $this->db->get();
    return $query->result();
    }

    public function get_staff_by_plantilla_group()
{
    $this->db->select('hris_plantilla.pGroup, hris_staff.*');
    $this->db->from('hris_plantilla');
    $this->db->join('hris_staff', 'hris_staff.itemNo = hris_plantilla.itemNo', 'left'); 
    $this->db->order_by('hris_plantilla.pGroup');
    
    $query = $this->db->get();
    return $query->result();
}

public function get_grouped_staff() {
        $this->db->select('hris_plantilla.*, hris_staff.*, hris_plantilla.itemNo as ren');
        $this->db->from('hris_plantilla');
        $this->db->join('hris_staff', 'hris_plantilla.itemNo = hris_staff.itemNo', 'left');
        $this->db->order_by('hris_plantilla.pGroup', 'ASC');
        return $this->db->get()->result();
}

public function get_grouped_staff_limit($pgroup) {
        $this->db->select('hris_plantilla.*, hris_staff.*, hris_plantilla.itemNo as ren');
        $this->db->from('hris_plantilla');
        $this->db->join('hris_staff', 'hris_plantilla.itemNo = hris_staff.itemNo', 'left'); 
        $this->db->where('hris_plantilla.pGroup', $pgroup);
        $this->db->order_by('hris_plantilla.pGroup', 'ASC');
        return $this->db->get()->result();
}

public function smea_count_by_pillar($con1, $con2, $con3, $con4, $q) {
    $this->db->select('a.*, b.*');
    $this->db->from('sgod_aip as a');
    $this->db->join('sgod_sop as b', 'a.id = b.aip_id');
    $this->db->where('a.school_id', $con1);
    $this->db->where('a.fy', $con2);
    $this->db->where('a.b_code', $con3);
    $this->db->where('a.pillar', $con4);

    $this->db->where('b.'.$q.' !=', 0);
    $this->db->where('b.'.$q.' !=', '');


    return $this->db->get(); 
}




    



}
