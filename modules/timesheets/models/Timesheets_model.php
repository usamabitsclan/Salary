<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * timesheets model
 */
class timesheets_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * get staff
     * @param  string $id    
     * @param  array  $where 
     * @return array        
     */
    public function get_staff($id = '', $where = [])
    {
        $select_str = '*,CONCAT(firstname," ",lastname) as full_name';

        if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
            $select_str .= ',(SELECT COUNT(*) FROM ' . db_prefix() . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
        }

        $this->db->select($select_str);
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('staffid', $id);
            $staff = $this->db->get(db_prefix() . 'staff')->row();

            if ($staff) {
                $staff->permissions = $this->get_staff_permissions($id);
            }

            return $staff;
        }
        $this->db->order_by('firstname', 'desc');

        return $this->db->get(db_prefix() . 'staff')->result_array();
    }
    /**
     * get staff role
     * @param  integer $staff_id 
     * @return object           
     */
    public function get_staff_role($staff_id){

        return $this->db->query('select r.name
            from '.db_prefix().'staff as s 
                left join '.db_prefix().'roles as r on r.roleid = s.role
            where s.staffid ='.$staff_id)->row();
    }
    
    /**
     * get department name
     * @param   $departmentid 
     * @return                
     */
    public function get_department_name($departmentid){
        return $this->db->query('select ' . db_prefix() . 'departments.name from ' . db_prefix() . 'departments where departmentid = '.$departmentid)->result_array();
    }
   
    /**
     * get month
     * @return month 
     */
     public function get_month()
    {
        $date = getdate();
        $date_1 = mktime(0, 0, 0, ($date['mon'] - 5), 1, $date['year']);
        $date_2 = mktime(0, 0, 0, ($date['mon'] - 4), 1, $date['year']);
        $date_3 = mktime(0, 0, 0, ($date['mon'] - 3), 1, $date['year']);
        $date_4 = mktime(0, 0, 0, ($date['mon'] - 2), 1, $date['year']);
        $date_5 = mktime(0, 0, 0, ($date['mon'] - 1), 1, $date['year']);
        $date_6 = mktime(0, 0, 0, $date['mon'], 1, $date['year']);
        $date_7 = mktime(0, 0, 0, ($date['mon'] + 1), 1, $date['year']);
        $date_8 = mktime(0, 0, 0, ($date['mon'] + 2), 1, $date['year']);
        $date_9 = mktime(0, 0, 0, ($date['mon'] + 3), 1, $date['year']);
        $date_10 = mktime(0, 0, 0, ($date['mon'] + 4), 1, $date['year']);
        $date_11 = mktime(0, 0, 0, ($date['mon'] + 5), 1, $date['year']);
        $date_12 = mktime(0, 0, 0, ($date['mon'] + 6), 1, $date['year']);
        $month = [ '1' => ['id' => date('Y-m-d', $date_1), 'name' => date('m/Y', $date_1)],
                    '2' => ['id' => date('Y-m-d', $date_2), 'name' => date('m/Y', $date_2)],
                    '3' => ['id' => date('Y-m-d', $date_3), 'name' => date('m/Y', $date_3)],
                    '4' => ['id' => date('Y-m-d', $date_4), 'name' => date('m/Y', $date_4)],
                    '5' => ['id' => date('Y-m-d', $date_5), 'name' => date('m/Y', $date_5)],
                    '6' => ['id' => date('Y-m-d', $date_6), 'name' => date('m/Y', $date_6)],
                    '7' => ['id' => date('Y-m-d', $date_7), 'name' => date('m/Y', $date_7)],
                    '8' => ['id' => date('Y-m-d', $date_8), 'name' => date('m/Y', $date_8)],
                    '9' => ['id' => date('Y-m-d', $date_9), 'name' => date('m/Y', $date_9)],
                    '10' => ['id' => date('Y-m-d', $date_10), 'name' => date('m/Y', $date_10)],
                    '11' => ['id' => date('Y-m-d', $date_11), 'name' => date('m/Y', $date_11)],
                    '12' => ['id' => date('Y-m-d', $date_12), 'name' => date('m/Y', $date_12)],
            ];
        return $month;
    }
   /**
    * set leave
    * @param object $data 
    */
    public function set_leave($data){
            $affectedRows = 0;
            $date_create = date('Y-m-d H:i:s');
            $creator = get_staff_user_id();
            $leave_of_the_year_data = explode (',', $data['leave_of_the_year_data']);
            unset($data['leave_of_the_year_data']);
            $es_detail = [];
            $row = [];
            $rq_val = [];
            $header = [];
            $header[] = 'staffid';
            $header[] = 'staff';
            $header[] = 'departmentid';
            $header[] = 'roleid';
            $header[] = 'maximum_leave_of_the_year';


            for ($i=0; $i < count($leave_of_the_year_data); $i++) {                
                $row[] = $leave_of_the_year_data[$i];                
                if((($i+1)%5) == 0){
                    $rq_val[] = array_combine($header, $row);
                    $row = [];
                }
            }
            foreach($rq_val as $key => $rq){
                if($rq['staffid'] != ''){
                    $this->db->where('staffid', $rq['staffid']);
                    $data_staff_leave =  $this->db->get(db_prefix().'timesheets_day_off')->row();
                    if($data_staff_leave){

                        $data_update['total'] = $rq['maximum_leave_of_the_year'];
                        $data_update['remain'] = $rq['maximum_leave_of_the_year'];
                        $data_update['year'] = date('Y');    
                        $data_update['staffid'] = $rq['staffid'];    

                        $this->db->where('staffid', $rq['staffid']);
                        $this->db->update(db_prefix().'timesheets_day_off',$data_update);
                        $affectedRows++;
                    }
                    else{
                        $data_update['total'] = $rq['maximum_leave_of_the_year'];
                        $data_update['remain'] = $rq['maximum_leave_of_the_year'];
                        $data_update['year'] = date('Y');
                        $data_update['staffid'] = $rq['staffid'];
                        $this->db->insert(db_prefix().'timesheets_day_off',$data_update);
                        $affectedRows++;
                    }
                }
            }  
         return $affectedRows;
    }
    /**
     * add_day_off
     * @param  array $data 
     */
    public function add_day_off($data){
            $department = '';
            $position = '';
            $repeat_by_year = 0;

            if(isset($data['department'])){
                $department = implode(',',$data['department']);
            }
            if(isset($data['position'])){
                $position = implode(',',$data['position']);
            }
            if(isset($data['repeat_by_year'])){
                $repeat_by_year = $data['repeat_by_year'];
            }

            $this->db->insert(db_prefix().'day_off', [
                'off_reason' => $data['leave_reason'],
                'off_type' => $data['leave_type'],
                'break_date' => $this->format_date($data['break_date']),
                'department' => $department,
                'position' => $position,
                'repeat_by_year' => $repeat_by_year,
                'add_from' => get_staff_user_id()

            ]);
            $insert_id = $this->db->insert_id();
            if($insert_id){
                return $insert_id;
            }
        return 0;
    }
    /**
     * update day off
     * @param   array $data 
     * @param   int $id   
     * @return   bool    
     */
    public function update_day_off($data, $id){
            $department = '';
            $position = '';
            $repeat_by_year = 0;

            if(isset($data['department'])){
                $department = implode(',',$data['department']);
            }
            if(isset($data['position'])){
                $position = implode(',',$data['position']);
            }
            if(isset($data['repeat_by_year'])){
                $repeat_by_year = $data['repeat_by_year'];
            }

        $this->db->where('id',$id);
        $this->db->update(db_prefix().'day_off',[
                'off_reason' => $data['leave_reason'],
                'off_type' => $data['leave_type'],
                'break_date' => $this->format_date($data['break_date']),
                'department' => $department,
                'position' => $position,
                'repeat_by_year' => $repeat_by_year,
                'add_from' => get_staff_user_id()

            ]);
            if($this->db->affected_rows() > 0){
                return true;
            }
            return false;
    }
    /**
     * get break dates
     * @param  string $type 
     * @return array       
     */
    public function get_break_dates($type = ''){
        if($type != ''){
            $this->db->where('off_type', $type);
            return $this->db->get(db_prefix().'day_off')->result_array();
        }else{
            return $this->db->get(db_prefix().'day_off')->result_array();
        }
    }
    /**
     * delete day off
     * @param  $id 
     * @return bool    
     */
    public function delete_day_off($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'day_off');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * get_requisition
     * @param  $type 
     * @return       
     */
    public function get_requisition($type){
        return $this->db->query('SELECT rq.id, rq.name, rq.addedfrom, rq.date_create, rq.approval_deadline, rq.status 
        FROM '.db_prefix().'request rq
        LEFT JOIN '.db_prefix().'request_type rqt ON rqt.id = rq.request_type_id
        where rqt.related_to = '.$type)->result_array();
    }

    /**
     * add work shift
     * @param object $data 
     * @return integer 
    */
    public function add_work_shift($data){
        unset($data['id']);
       
        if(!$this->check_format_date_ymd($data['from_date'])){
            $data_insert['from_date'] = to_sql_date($data['from_date']);
        }
        else{
            $data_insert['from_date'] = $data['from_date'];
        }
        if(!$this->check_format_date_ymd($data['to_date'])){
            $data_insert['to_date'] = to_sql_date($data['to_date']);
        }
        else{
            $data_insert['to_date'] = $data['to_date'];
        }
        if(isset($data['department'])){
            $data_insert['department'] = implode(',', $data['department']);
        }
        if(isset($data['role'])){
            $data_insert['position'] = implode(',', $data['role']);
        }
        if(isset($data['staff'])){
           $data_insert['staff'] = implode(',', $data['staff']);
        }
        if(isset($data['type_shiftwork'])){
           $data_insert['type_shiftwork'] = $data['type_shiftwork'];
        }
        $data_insert['shift_code'] = strtotime(date('Y-m-d'));
        $data_insert['shift_name'] = '';
        $data_insert['shift_type'] = '';
        $data_insert['date_create'] = date('Y-m-d');
        $data_insert['add_from'] = get_staff_user_id();
        $this->db->insert(db_prefix().'work_shift',$data_insert);
        $insert_id = $this->db->insert_id();
        if($insert_id){
                $new_list_id = [];
                $staff_id_list = [];
                $has_staff = false;
                if(isset($data['staff'])){
                    $has_staff = true;
                    foreach ($data['staff'] as $key => $staff_id) {
                        if(!in_array($staff_id, $staff_id_list)){
                            $staff_id_list[] = $staff_id;
                        }
                    }
                }

                $data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'],$has_staff);                     
                $list_date = $this->get_list_date($data_insert['from_date'], $data_insert['to_date']);    
                $list_data_shift = [];
                if($data['type_shiftwork'] == 'repeat_periodically'){

                    $data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'],$has_staff);                        
                    if($has_staff == true){                
                        foreach ($staff_id_list as $key => $staffid) {
                            foreach ($data_shift_hanson as $h => $r_item) {
                                if($staffid == $r_item['staff_id']){
                                    for($i = 1; $i<=7; $i++){
                                        $shift_id = $r_item[$i];
                                        if($shift_id != ''){
                                            $data_detail['staff_id'] = $staffid;
                                            $data_detail['number'] = $i;
                                            $data_detail['shift_id'] = $shift_id;
                                            $data_detail['work_shift_id'] = $insert_id;                                            
                                            $this->db->insert(db_prefix().'work_shift_detail_number_day', $data_detail);                                        
                                        }
                                    }
                                }                        
                            }  
                        } 
                    }
                    else{
                        foreach ($data_shift_hanson as $h => $r_item) {                           
                            for($i = 1; $i<=7; $i++){
                                $shift_id = $r_item[$i];
                                if($shift_id != ''){
                                    $data_detail['staff_id'] = '';
                                    $data_detail['number'] = $i;
                                    $data_detail['shift_id'] = $shift_id;
                                    $data_detail['work_shift_id'] = $insert_id;                                            
                                    $this->db->insert(db_prefix().'work_shift_detail_number_day', $data_detail);                                        
                                }
                            }
                        }  
                    }
                }
                elseif($data['type_shiftwork'] == 'by_absolute_time'){

                    $staff_id_list = [];
                    $has_staff = false;
                    if(isset($data['staff'])){
                        $has_staff = true;
                        foreach ($data['staff'] as $key => $staff_id) {
                            if(!in_array($staff_id, $staff_id_list)){
                                $staff_id_list[] = $staff_id;
                            }
                        }
                    }
                    $data_shift_hanson = $this->get_shift_by_absolute_time($data['shifts_detail'], $list_date, $has_staff);
                    if($has_staff == true){                
                        foreach ($staff_id_list as $key => $staffid) {
                            foreach ($data_shift_hanson as $h => $r_item) {
                                if($staffid == $r_item['staff_id']){
                                    foreach ($list_date as $k => $date) {
                                        $shift_id = $r_item[date('Y-m-d',strtotime($date))];
                                        if($shift_id != '' && (int)$shift_id > 0){
                                            $data_detail['staff_id'] = $staffid;
                                            $data_detail['date'] = $date;
                                            $data_detail['shift_id'] = $shift_id;
                                            $data_detail['work_shift_id'] = $insert_id;
                                            $this->db->insert(db_prefix().'work_shift_detail', $data_detail);                                        
                                        }
                                    }
                                }                        
                            }  
                        }            
                    }
                    else{
                        foreach ($data_shift_hanson as $h => $r_item) {                           
                            foreach ($list_date as $k => $date) {
                                $shift_id = $r_item[date('Y-m-d',strtotime($date))];
                                if($shift_id != '' && (int)$shift_id > 0){
                                    $data_detail['staff_id'] = '';
                                    $data_detail['date'] = $date;
                                    $data_detail['shift_id'] = $shift_id;
                                    $data_detail['work_shift_id'] = $insert_id;
                                    $this->db->insert(db_prefix().'work_shift_detail', $data_detail);                                        
                                }
                            }
                        }  
                    }
                }
            return $insert_id;
          }
    }
    /**
     *  get staff id by department
     * @param  $id 
     * @return     
     */
    public function get_staff_id_by_department($id){
        $this->db->select('staffid');
        $this->db->where('departmentid', $id);
        return $this->db->get(db_prefix().'staff_departments')->result_array();
    }
    /**
     * get staff id by role
     * @param  integer $id 
     * @return  array 
     */
    public function get_staff_id_by_role($id){
        $this->db->select('staffid');
        $this->db->where('role', $id);
        return $this->db->get(db_prefix().'staff')->result_array();
    }
    /**
     * get list date
     * @param   $from_date 
     * @param   $to_date             
     */
    public function get_list_date($from_date, $to_date){
        $list_date = [];
        $i = 0;
        $to_date_s = '';
        $to_date = date('Y-m-d', strtotime($to_date));
        while($to_date_s != $to_date) {
          $next_date = date('Y-m-d', strtotime($from_date .' +'.$i.' day'));
          $list_date[] = $next_date; 
          $to_date_s = $next_date; 
          $i++;
        }
        return $list_date;
    }
    /**
     * get shift repeat periodically
     * @param  string  $shifts_detail 
     * @param  boolean $has_staff     
     * @return array                 
     */
    public function get_shift_repeat_periodically($shifts_detail, $has_staff = true){
            $shifts_detail = explode (',', $shifts_detail);
            $es_detail = [];
            $row = [];
            $rq_val = [];
            $header = [];
            if($has_staff == true){
                $header[] = 'staff_id';
                $header[] = 'staff';
                $header[] = '1';
                $header[] = '2';
                $header[] = '3';
                $header[] = '4';
                $header[] = '5';
                $header[] = '6';
                $header[] = '7';
                for ($i=0; $i < count($shifts_detail); $i++) {
                    $row[] = $shifts_detail[$i];  
                    if((($i+1)%9) == 0){
                        $rq_val[] = array_combine($header, $row);
                        $row = [];
                    }
                }
            }
            else{
                $header[] = '1';
                $header[] = '2';
                $header[] = '3';
                $header[] = '4';
                $header[] = '5';
                $header[] = '6';
                $header[] = '7';
                for ($i=0; $i < count($shifts_detail); $i++) { 
                    $row[] = $shifts_detail[$i];  
                    if((($i+1)%7) == 0){
                        $rq_val[] = array_combine($header, $row);
                        $row = [];
                    }
                }
            }
            return $rq_val;
    }
    /**
     * [get_shift_by_absolute_time
     * @param  integer $shifts_detail 
     * @param  array $list_date     
     * @param  boolean $has_staff     
     * @return integer                
     */
    
     public function get_shift_by_absolute_time($shifts_detail, $list_date, $has_staff = true){
            $shifts_detail = explode (',', $shifts_detail);
            $es_detail = [];
            $row = [];
            $rq_val = [];
            $header = [];
            if($has_staff == true){
                $header[] = 'staff_id';
                $header[] = 'staff';
                $total_date = 0;
                foreach ($list_date as $date) {
                    $header[] = $date;   
                    $total_date++;                 
                }

                for ($i=0; $i < count($shifts_detail); $i++) {   
                    $row[] = $shifts_detail[$i]; 

                    if((($i+1)%($total_date+2)) == 0){
                        $rq_val[] = array_combine($header, $row);
                        $row = [];
                    }
                }
            }
            else{
                $total_date = 0;
                foreach ($list_date as $date) {
                    $header[] = $date;   
                    $total_date++;                 
                }
                for ($i=0; $i < count($shifts_detail); $i++) { 
                    $row[] = $shifts_detail[$i];  
                    if((($i+1) % $total_date) == 0){
                        $rq_val[] = array_combine($header, $row);
                        $row = [];
                    }
                }
            }
            return $rq_val;
    }
    /**
     * update work shift
     * @param   $data 
     * @return  boolean     
     */
    public function update_work_shift($data){

        if(!$this->check_format_date_ymd($data['from_date'])){
            $data_insert['from_date'] = to_sql_date($data['from_date']);
        }
        else{
            $data_insert['from_date'] = $data['from_date'];
        }
        if(!$this->check_format_date_ymd($data['to_date'])){
            $data_insert['to_date'] = to_sql_date($data['to_date']);
        }
        else{
            $data_insert['to_date'] = $data['to_date'];
        }
        if(isset($data['department'])){
            $data_insert['department'] = implode(',', $data['department']);
        }
        else{
            $data_insert['department'] = '';
        }
        if(isset($data['role'])){
            $data_insert['position'] = implode(',', $data['role']);
        }
        else{
            $data_insert['position'] = '';
        }
        if(isset($data['staff'])){
           $data_insert['staff'] = implode(',', $data['staff']);
        }
        else{
            $data_insert['staff'] = '';
        }
        if(isset($data['type_shiftwork'])){
           $data_insert['type_shiftwork'] = $data['type_shiftwork'];
        }
        $data_insert['shift_code'] = strtotime(date('Y-m-d'));
        $data_insert['shift_name'] = '';
        $data_insert['shift_type'] = '';
        $data_insert['date_create'] = date('Y-m-d');
        $data_insert['add_from'] = get_staff_user_id();
        $old_type_shiftwork = '';
        $data_old  = $this->get_work_shift($data['id']);
        if($data_old){
            $old_type_shiftwork = $data_old->type_shiftwork;
        }

        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix().'work_shift',$data_insert);

        $new_list_id = [];
        $staff_id_list = [];
       
        $has_staff = false;
        if(isset($data['staff'])){
            $has_staff = true;
            foreach ($data['staff'] as $key => $staff_id) {
                if(!in_array($staff_id, $staff_id_list)){
                    $staff_id_list[] = $staff_id;
                }
            }
        }

        $list_date = $this->get_list_date($data_insert['from_date'], $data_insert['to_date']);    

        $list_data_shift = [];
        if($data['type_shiftwork'] == 'repeat_periodically'){       
            
            $data_shift_hanson = $this->get_shift_repeat_periodically($data['shifts_detail'],$has_staff);           
            if($has_staff == true){                
                foreach ($staff_id_list as $key => $staffid) {
                    foreach ($data_shift_hanson as $h => $r_item) {
                        if($staffid == $r_item['staff_id']){
                            for($i = 1; $i<=7; $i++){
                                $shift_id = $r_item[$i];
                                if($shift_id != ''){
                                        $data_saved = $this->get_shift_staff_by_day_name($data['id'], $i, $staffid);
                                        if($data_saved){
                                            $data_detail['shift_id'] = $shift_id;
                                            $this->db->where('id', $data_saved->id);                                    
                                            $this->db->update(db_prefix().'work_shift_detail_number_day', $data_detail);                                      
                                        }
                                        else{
                                            $data_detail['number'] = $i;
                                            $data_detail['staff_id'] = $staffid;
                                            $data_detail['shift_id'] = $shift_id;
                                            $data_detail['work_shift_id'] = $data['id'];                                          
                                            $this->db->insert(db_prefix().'work_shift_detail_number_day', $data_detail);
                                        } 
                                }
                                else{
                                    $this->db->where('number', $i);                                    
                                    $this->db->where('staff_id', $staffid);                                    
                                    $this->db->where('work_shift_id', $data['id']);                                    
                                    $this->db->delete(db_prefix().'work_shift_detail_number_day');
                                }
                            }
                        }                        
                    } 
                } 
            }
            else{
                foreach ($data_shift_hanson as $h => $r_item) {                           
                    for($i = 1; $i<=7; $i++){
                        $shift_id = $r_item[$i];
                        if($shift_id != ''){
                            $data_saved = $this->get_shift_staff_by_day_name($data['id'], $i);
                            if($data_saved){
                                $data_detail['shift_id'] = $shift_id;
                                $this->db->where('id', $data_saved->id);                                    
                                $this->db->update(db_prefix().'work_shift_detail_number_day', $data_detail);                                       
                            }
                            else{
                                $data_detail['staff_id'] = '';
                                $data_detail['number'] = $i;
                                $data_detail['shift_id'] = $shift_id;
                                $data_detail['work_shift_id'] = $data['id'];                                            
                                $this->db->insert(db_prefix().'work_shift_detail_number_day', $data_detail);
                            }
                        }
                        else{
                                $this->db->where('number', $i);                                    
                                $this->db->where('work_shift_id', $data['id']);                                    
                                $this->db->delete(db_prefix().'work_shift_detail_number_day');
                        }
                    }
                }  
            }
            if($old_type_shiftwork != 'repeat_periodically'){
                $this->db->where('work_shift_id', $data['id']);                                    
                $this->db->delete(db_prefix().'work_shift_detail');
            }
        }
        elseif($data['type_shiftwork'] == 'by_absolute_time'){
            $data_shift_hanson = $this->get_shift_by_absolute_time($data['shifts_detail'], $list_date, $has_staff);
            if($has_staff == true){                
                foreach ($staff_id_list as $key => $staffid) {
                    foreach ($data_shift_hanson as $h => $r_item) {
                        if($staffid == $r_item['staff_id']){
                            foreach ($list_date as $k => $date) {
                                $shift_id = $r_item[date('Y-m-d',strtotime($date))];
                                if($shift_id != ''){  
                                    $get_s = $this->get_shift_staff_by_date( $data['id'], $date, $staffid);                                       
                                    if($get_s){
                                        $data_detail['shift_id'] = $shift_id;
                                        $this->db->where('id', $get_s->id);
                                        $this->db->update(db_prefix().'work_shift_detail', $data_detail);                                       
                                    }
                                    else{
                                        $data_detail['staff_id'] = $staffid;
                                        $data_detail['date'] = $date;
                                        $data_detail['shift_id'] = $shift_id;
                                        $data_detail['work_shift_id'] = $data['id'];
                                        $this->db->insert(db_prefix().'work_shift_detail', $data_detail);      
                                    }
                                }
                                else{
                                    $this->db->where('staff_id', $staffid);
                                    $this->db->where('date', $date);
                                    $this->db->where('work_shift_id', $data['id']);
                                    $this->db->delete(db_prefix().'work_shift_detail');                                       
                                }
                            }
                        }                        
                    }  
                }            
            }
            else{
                    foreach ($data_shift_hanson as $h => $r_item) {                           
                        foreach ($list_date as $k => $date) {
                            $shift_id = $r_item[date('Y-m-d',strtotime($date))];
                             if($shift_id != ''){  
                                $get_s =  $this->get_shift_staff_by_date($data['id'], $date);                                      
                                if($get_s){
                                    $data_detail['shift_id'] = $shift_id;
                                    $this->db->where('id', $get_s->id);
                                    $this->db->update(db_prefix().'work_shift_detail', $data_detail);                                       
                                }
                                else{
                                    $data_detail['date'] = $date;
                                    $data_detail['shift_id'] = $shift_id;
                                    $data_detail['work_shift_id'] = $data['id'];
                                    $this->db->insert(db_prefix().'work_shift_detail', $data_detail);      
                                }
                            }
                            else{
                                if($get_s){
                                    $this->db->where('date', $date);
                                    $this->db->where('work_shift_id', $data['id']);
                                    $this->db->delete(db_prefix().'work_shift_detail');                                       
                                }
                            }
                        }
                    }  
            }
            if($old_type_shiftwork != 'by_absolute_time'){
                $this->db->where('work_shift_id', $data['id']);                                    
                $this->db->delete(db_prefix().'work_shift_detail_number_day');
            }
        }
        return true;       
    }
    /**
     * get data edit shift
     * @param  integer $work_shift 
     * @return array             
     */
    public function get_data_edit_shift($work_shift){
        $this->db->where('id',$work_shift);
        $shift = $this->db->get(db_prefix().'work_shift')->row();
        $data['shifts_detail'] = $shift->shifts_detail;
        if(isset($data['shifts_detail'])){
             $data['shifts_detail'] = explode ( ',', $data['shifts_detail']);
             $shifts_detail_col = ['detail','monday','tuesday','wednesday','thursday','friday','saturday_even','saturday_odd','sunday'];
             $row = [];
             $shifts_detail = [];
             for ($i=0; $i < count($data['shifts_detail']); $i++) {
                    $row[] = $data['shifts_detail'][$i];
                if((($i+1)%9) == 0){
                    $shifts_detail[] = array_combine($shifts_detail_col, $row);
                    $row = [];
                }
            }
            unset($data['shifts_detail']);
            
        }
        return $shifts_detail;
        
    }

  

/**
 * get shift
 * @param integer $id 
 * @return object or array          
 */
    public function get_shifts($id = ''){
        if($id != ''){
            $this->db->where('id',$id);
            return $this->db->get(db_prefix().'work_shift')->row();
        }else{
            return $this->db->get(db_prefix().'work_shift')->result_array();
        }
    }
    public function delete_shift($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'work_shift');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('work_shift_id', $id);
            $this->db->delete(db_prefix() . 'work_shift_detail');
            $this->db->where('work_shift_id', $id);
            $this->db->delete(db_prefix() . 'work_shift_detail_day_name');
            return true;
        }
        return false;
    
    }
    /**
     * get timesheets ts by month
     * @param integer $month 
     * @param integer $year  
     * @return array   
     */
    public function get_timesheets_ts_by_month($month, $year){
        $check_latch_timesheet = $this->check_latch_timesheet($month.'-'.$year);
        if($check_latch_timesheet){
            $query = 'select * from '.db_prefix().'timesheets_timesheet where month(date_work) = '.$month.' and year(date_work) = '.$year.' and latch = 1';       
        }else{
            $query = 'select * from '.db_prefix().'timesheets_timesheet where month(date_work) = '.$month.' and year(date_work) = '.$year;       
        }
        return $this->db->query($query)->result_array();
    }
    
    /**
     * get ts by date and staff
     * @param  $date  
     * @param  $staff 
     * @return        
     */
    public function get_ts_by_date_and_staff($date,$staff){
        $this->db->where('date_work', $date);
        $this->db->where('staff_id', $staff);
        return $this->db->get(db_prefix().'timesheets_timesheet')->result_array();
    }


    /**
     * staff chart by age
     */
    public function staff_chart_by_age()
    {
        $staffs = $this->staff_model->get();

        $chart = [];
        $status_1 = ['name' => _l('18-24'), 'color' => '#777', 'y' => 0, 'z' => 100];
        $status_2 = ['name' => _l('25-29'), 'color' => '#fc2d42', 'y' => 0, 'z' => 100];
        $status_3 = ['name' => _l('30 - 39'), 'color' => '#03a9f4', 'y' => 0, 'z' => 100];
        $status_4 = ['name' => _l('40-60'), 'color' => '#ff6f00', 'y' => 0, 'z' => 100];
        
        foreach ($staffs as $staff) {

        $diff = date_diff(date_create(), date_create($staff['birthday']));
        $age = $diff->format('%Y');

          if($age >= 18 && $age <= 24)
          {
            $status_1['y'] += 1;
          }elseif ($age >= 25 && $age <= 29) {
            $status_2['y'] += 1;
          }elseif ($age >= 30 && $age <= 39) {
            $status_3['y'] += 1;
          }elseif ($age >= 40 && $age <= 60) {
            $status_4['y'] += 1;
          }
          
        }
        if($status_1['y'] > 0){
            array_push($chart, $status_1);
        }
        if($status_2['y'] > 0){
            array_push($chart, $status_2);
        }
        if($status_3['y'] > 0){
            array_push($chart, $status_3);
        }
        if($status_4['y'] > 0){
            array_push($chart, $status_4);
        }

        return $chart;
    }
    

    /**
     * get_timesheets_ts_by_year
     * @param integre $staff_id 
     * @param integre $year     
     * @return integre           
     */
    public function get_timesheets_ts_by_year($staff_id, $year){

        return $this->db->query('select * from '.db_prefix().'timesheets_timesheet where YEAR(date_work) = '.$year.' AND staff_id = '.$staff_id.' order by date_work ASC ')->result_array();
    }
    /**
     * get request leave
     * @param  integer $id 
     */
    public function get_request_leave($id = ''){
            $this->load->model('staff_model');
            if($id == ''){
                return  $this->db->get(db_prefix().'timesheets_requisition_leave')->result_array();
            }else{
                $this->db->where('id',$id);
                $requisition =  $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
                if($requisition){
                    $staff = $this->staff_model->get($requisition->staff_id);
                    if($staff){
                        $requisition->email =  $staff->email;

                        $requisition->name = $this->getdepartment_name($requisition->staff_id)->name;
                        $requisition->name_role = $this->get_staff_role($requisition->staff_id)->name;
                    }
                    $requisition->attachments = $this->get_requisition_attachments($id);
                }
            return $requisition;
        }
    }
    /**
     * getdepartment name
     * @param  integer $staffid 
     * @return object          
     */
    public function getdepartment_name($staffid){
        return $this->db->query('select s.staffid, d.departmentid ,d.name
            from ' . db_prefix() . 'staff as s 
                left join ' . db_prefix() . 'staff_departments as sd on sd.staffid = s.staffid
                left join ' . db_prefix() . 'departments d on d.departmentid = sd.departmentid 
            where s.staffid in ('.$staffid.')
                order by d.departmentid,s.staffid')->row();
    }
    /**
     * get requisition attachments
     * @param  integer $id            
     * @param  integer $attachment_id 
     * @param  array  $where         
     * @return array                
     */
    public function get_requisition_attachments($id = '', $attachment_id = '', $where = [])
    {
        $this->db->where($where);
        $idIsHash = !is_numeric($attachment_id) && strlen($attachment_id) == 32;
        if (is_numeric($attachment_id) || $idIsHash) {
            $this->db->where($idIsHash ? 'attachment_key' : 'id', $attachment_id);

            return $this->db->get(db_prefix() . 'files')->row();
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'requisition');
        $this->db->order_by('dateadded', 'DESC');

        return $this->db->get(db_prefix() . 'files')->result_array();
    }
    /**
     * get number of days off
     * @param  integer $staffid 
     * @return integer           
     */
    public function get_number_of_days_off($staffid = 0){
        if($staffid == 0){
            $staffid = get_staff_user_id();   
        }
        $staff = $this->staff_model->get($staffid); 

        $leave_position = $this->get_timesheets_option_api('timesheets_leave_position');
        $leave_contract_type = $this->get_timesheets_option_api('timesheets_leave_contract_type');
        $leave_start_date = $this->get_timesheets_option_api('timesheets_leave_start_date');
        $max_leave_in_year = $this->get_timesheets_option_api('timesheets_max_leave_in_year');
        $start_leave_from_month = $this->get_timesheets_option_api('timesheets_start_leave_from_month');
        $start_leave_to_month = $this->get_timesheets_option_api('timesheets_start_leave_to_month');

        $add_new_leave_month_from_date = $this->get_timesheets_option_api('timesheets_add_new_leave_month_from_date');

        $accumulated_leave_to_month = $this->get_timesheets_option_api('timesheets_accumulated_leave_to_month');
        $leave_contract_sign_day = $this->get_timesheets_option_api('timesheets_leave_contract_sign_day');
        $add_leave_after_probationary_period = $this->get_timesheets_option_api('add_leave_after_probationary_period');
        $start_date_seniority = $this->get_timesheets_option_api('timesheets_start_date_seniority');
        $seniority_year = $this->get_timesheets_option_api('timesheets_seniority_year');
        $seniority_year_leave = $this->get_timesheets_option_api('timesheets_seniority_year_leave');
        $next_year = $this->get_timesheets_option_api('timesheets_next_year');
        $next_year_leave = $this->get_timesheets_option_api('timesheets_next_year_leave');

        $alow_borrow_leave = $this->get_timesheets_option_api('alow_borrow_leave');
        if($leave_position != ''){
            $leave_position = explode(', ', $leave_position);
            if(!in_array($staff->role,$leave_position)){
                return 0;
            }
        }

        $count = 0;
        $day_off = $this->get_day_off($staffid);
        if($day_off){
            if($alow_borrow_leave == 1){
                $count = $day_off->total; 
            }
        }
        return $count;
    }
        /**
     * Check whether column exists in a table
     * Custom function because Codeigniter is caching the tables and this is causing issues in migrations
     * @param  string $column column name to check
     * @param  string $table table name to check
     * @return boolean
     */


    public function get_timesheets_option_api($name)
    {
        $options = [];
        $val  = '';
        $name = trim($name);
        
        if (!isset($options[$name])) {
            // is not auto loaded
            $this->db->select('option_val');
            $this->db->where('option_name', $name);
            $row = $this->db->get(db_prefix() . 'timesheets_option')->row();
            if ($row) {
                $val = $row->option_val;
            }
        } else {
            $val = $options[$name];
        }

        return $val;
    }

    /**
     * Gets the day off.
     * @param      integer  $staffid  The staffid
     * @param      integer  $year     The year
     * @return     <type>          The day off.
     */
    public function get_day_off($staffid = 0, $year = 0){
        if($staffid == 0){
            $staffid = get_staff_user_id();   
        }
        if($year == 0){
            $year = date('Y');   
        }
        $day_off = $this->db->query('select * from '.db_prefix().'timesheets_day_off where staffid = '.$staffid.' and year = '.$year)->row();
        return $day_off;
    }
    /**
     * check_approval_details
     * @param   $rel_id   
     * @param   $rel_type          
     * @return   bool          
     */
    public function check_approval_details($rel_id, $rel_type){
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $approve_status = $this->db->get(db_prefix().'timesheets_approval_details')->result_array();
        if(count($approve_status) > 0){
            foreach ($approve_status as $value) {
                if($value['approve'] == 2){
                    return 'reject'; 
                }
                if($value['approve'] == 0){
                    $value['staffid'] = explode(', ',$value['staffid']);
                    return $value; 
                }
            }
            return true; 
        }
        return false;
    }
    /**
     * get list approval details
     * @param  integer $rel_id   
     * @param  string $rel_type
     * @return  array
     */
    public function get_list_approval_details($rel_id, $rel_type){
        $this->db->select('*');
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        return $this->db->get(db_prefix().'timesheets_approval_details')->result_array();
    }

    /**
     * cancel request
     * @param object $data    
     * @param  integer $staffid 
     * @return          
     */
    public function cancel_request($data, $staffid = ''){
        $rel_id = $data['rel_id'];
        $rel_type = $data['rel_type'];
        $data_update = [];

        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->delete(db_prefix().'timesheets_approval_details');


        switch ($rel_type) {
           
            case 'Leave':
                $data_update['status'] = 0;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                
                $this->db->where('id', $rel_id);
                $requisition_leave = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();

                $st = $requisition_leave->start_time;
                $et = $requisition_leave->end_time;

                if($staffid != ''){
                    $staff_id = $staffid;
                }else{
                    $staff_id = get_staff_user_id();
                }

                $type = '';
                switch ($requisition_leave->type_of_leave) {
                    case 1:
                        $type = 'SI';
                        break;
                    case 2:
                        $type = 'M';
                        break;
                    case 3:
                        $type = 'R';
                        break;
                    case 4:
                        $type = 'Ro';
                        break;
                    case 6:
                        $type = 'PO';
                        break;
                    case 7:
                        $type = 'ME';
                        break;
                    case 8:
                        $type = 'AL';

                        $day_off = $this->get_day_off($requisition_leave->staff_id);
                        $dd = $requisition_leave->number_of_leaving_day;
                       
                            $aa = $day_off->remain + $dd;
                            $this->db->where('staffid', $requisition_leave->staff_id);
                            $this->db->where('year', date('Y'));
                            $this->db->update(db_prefix().'timesheets_day_off',[
                                                'remain' => $aa,
                                            ]);                        
                        break;
                    
                }




                if($requisition_leave->end_time != ''){
                    for ($i=0; $i < 5; $i++) {
                        if(strtotime($st) <= strtotime($et)){
                            $date_work = date('Y-m-d', strtotime($st));

                            $this->db->where('staff_id', $requisition_leave->staff_id);
                            $this->db->where('date_work', $date_work);
                            $this->db->where('type', $type);
                            $this->db->delete(db_prefix().'timesheets_timesheet');

                            $st = date('Y-m-d', strtotime($st. ' + 1 days'));
                            $i = 0;
                        }else{
                            $i = 10;
                        }
                    }                   
                }else{

                    $date_work = date('Y-m-d', strtotime($st));

                    $this->db->where('staff_id', $requisition_leave->staff_id);
                            $this->db->where('date_work', $date_work);
                            $this->db->where('type', $type);
                            $this->db->delete(db_prefix().'timesheets_timesheet');                  
                }




                return true;
                break;
            default:
                return false;
                break;
        }
    }
    /**
     * get value by time
     * @param  integer $st       
     * @param  string $et       
     * @param  string $staff_id 
     * @return  decimal        
     */
    public function get_value_by_time($st, $et = '', $staff_id = ''){
       
        if($staff_id == '') {
            $staffid = get_staff_user_id();
        }else{
            $staffid = $staff_id;
        }

        $work_shift = $this->get_data_edit_shift_by_staff($staffid);

        $date_time = $this->get_date_time($work_shift);

        $time = strtotime($st);

        $lunch_break = 0;

        $work_time = 0;

        $t = 0;

        $staff_sc = $this->timesheets_model->get_staff_shift_applicable_object();
        $list_staff_sc = [];
        foreach ($staff_sc as $key => $value) {
            $list_staff_sc[] = $value['staffid'];
        }

        if(in_array($staffid, $list_staff_sc)){
            $shift = $this->timesheets_model->get_shiftwork_sc_date_and_staff(date('Y-m-d', $time), $staffid);
            if(isset($shift)){
                $work_shift = $this->timesheets_model->get_shift_sc($shift);

                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift->time_start_work.' - '.$work_shift->time_end_work.'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift->start_lunch_break_time.' - '.$work_shift->end_lunch_break_time.'</li>';

                $work_time = (strtotime($work_shift->time_end_work) - strtotime($work_shift->time_start_work)) / 60;
                $lunch_break = (strtotime($work_shift->end_lunch_break_time) - strtotime($work_shift->start_lunch_break_time)) / 60;

                if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($work_shift->time_start_work.':00')){
                    $stime = strtotime($work_shift->time_start_work.':00');
                }else{
                    $stime = strtotime(date('H:i:s' , strtotime($st)));
                }

                if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                    $_st = strtotime(date('H:i:s' ,strtotime($st)));
                    $_et = strtotime(date('H:i:s' ,strtotime($et)));
                    if($_st < strtotime($work_shift->time_start_work.':00')){
                        $_st = strtotime($work_shift->time_start_work.':00');
                    }elseif($_st > strtotime($work_shift->start_lunch_break_time.':00') && $_st < strtotime($work_shift->end_lunch_break_time.':00') ){
                        $_st = strtotime($work_shift->end_lunch_break_time.':00');
                    }
                    if($_et > strtotime($work_shift->time_end_work.':00')){
                        $_et = strtotime($work_shift->time_end_work.':00');
                    }
                    if(strtotime($work_shift->start_lunch_break_time.':00') > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                        $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                    }else{
                        $t = ($_et - $_st) / 3600;
                    }
                }else{
                    
                    if(strtotime($work_shift->start_lunch_break_time.':00') > $stime){
                        $t = (strtotime($work_shift->time_end_work.':00') - $stime) / 3600 - ($lunch_break / 60);
                    }else{
                        $t = (strtotime($work_shift->time_end_work.':00') - $stime) / 3600;
                    }
                }
            }
        }else{
            switch (date('N', $time)) {
                case 1:
                    $lunch_break = $date_time['lunch_break']['monday'];
                    $work_time = $date_time['work_time']['monday'];

                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['monday'])){
                        $stime = strtotime($date_time['late_for_work']['monday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }

                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        if($_st < strtotime($date_time['late_for_work']['monday'])){
                            $_st = strtotime($date_time['late_for_work']['monday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['monday']) && $_st < strtotime($date_time['start_afternoon_shift']['monday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['monday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['monday'])){
                            $_et = strtotime($date_time['come_home_early']['monday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['monday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        
                        if(strtotime($date_time['start_lunch_break_time']['monday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['monday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['monday']) - $stime) / 3600;
                        }
                    }

                    break;
                case 2:
                    $lunch_break = $date_time['lunch_break']['tuesday'];
                    $work_time = $date_time['work_time']['tuesday'];

                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['tuesday'])){
                        $stime = strtotime($date_time['late_for_work']['tuesday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }
                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        if($_st < strtotime($date_time['late_for_work']['tuesday'])){
                            $_st = strtotime($date_time['late_for_work']['tuesday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['tuesday']) && $_st < strtotime($date_time['start_afternoon_shift']['tuesday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['tuesday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['tuesday'])){
                            $_et = strtotime($date_time['come_home_early']['tuesday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['tuesday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        if(strtotime($date_time['start_lunch_break_time']['tuesday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['tuesday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['tuesday']) - $stime) / 3600;
                        }
                    }
                    break;
                case 3:
                    $lunch_break = $date_time['lunch_break']['wednesday'];
                    $work_time = $date_time['work_time']['wednesday'];

                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['wednesday'])){
                        $stime = strtotime($date_time['late_for_work']['wednesday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }

                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        if($_st < strtotime($date_time['late_for_work']['wednesday'])){
                            $_st = strtotime($date_time['late_for_work']['wednesday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['wednesday']) && $_st < strtotime($date_time['start_afternoon_shift']['wednesday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['wednesday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['wednesday'])){
                            $_et = strtotime($date_time['come_home_early']['wednesday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['wednesday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        
                        if(strtotime($date_time['start_lunch_break_time']['wednesday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['wednesday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['wednesday']) - $stime) / 3600;
                        }
                    }
                    break;
                case 4:
                    $lunch_break = $date_time['lunch_break']['thursday'];
                    $work_time = $date_time['work_time']['thursday'];

                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['thursday'])){
                        $stime = strtotime($date_time['late_for_work']['thursday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }
                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        if($_st < strtotime($date_time['late_for_work']['thursday'])){
                            $_st = strtotime($date_time['late_for_work']['thursday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['thursday']) && $_st < strtotime($date_time['start_afternoon_shift']['thursday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['thursday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['thursday'])){
                            $_et = strtotime($date_time['come_home_early']['thursday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['thursday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        if(strtotime($date_time['start_lunch_break_time']['thursday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['thursday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['thursday']) - $stime) / 3600;
                        }
                    }
                    break;
                case 5:
                    $lunch_break = $date_time['lunch_break']['friday'];
                    $work_time = $date_time['work_time']['friday'];
                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['friday'])){
                        $stime = strtotime($date_time['late_for_work']['friday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }
                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        
                        if($_st < strtotime($date_time['late_for_work']['friday'])){
                            $_st = strtotime($date_time['late_for_work']['friday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['friday']) && $_st < strtotime($date_time['start_afternoon_shift']['friday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['friday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['friday'])){
                            $_et = strtotime($date_time['come_home_early']['friday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['friday']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        
                        if(strtotime($date_time['start_lunch_break_time']['friday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['friday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['friday']) - $stime) / 3600;
                        }
                    }
                    break;
                case 6:
                    if((date('d', $time)%2) == 1){
                        $lunch_break = $date_time['lunch_break']['saturday_odd'];
                        $work_time = $date_time['work_time']['saturday_odd'];

                        if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['saturday_odd'])){
                            $stime = strtotime($date_time['late_for_work']['saturday_odd']);
                        }else{
                            $stime = strtotime(date('H:i:s' ,strtotime($st)));
                        }

                        if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                            $_st = strtotime(date('H:i:s' ,strtotime($st)));
                            $_et = strtotime(date('H:i:s' ,strtotime($et)));
                            if($_st < strtotime($date_time['late_for_work']['saturday_odd'])){
                                $_st = strtotime($date_time['late_for_work']['saturday_odd']);
                            }elseif($_st > strtotime($date_time['start_lunch_break_time']['saturday_odd']) && $_st < strtotime($date_time['start_afternoon_shift']['saturday_odd']) ){
                                $_st = strtotime($date_time['start_afternoon_shift']['saturday_odd']);
                            }
                            if($_et > strtotime($date_time['come_home_early']['saturday_odd'])){
                                $_et = strtotime($date_time['come_home_early']['saturday_odd']);
                            }
                            if(strtotime($date_time['start_lunch_break_time']['saturday_odd']) > $stime  && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                                $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                            }else{
                                $t = ($_et - $_st) / 3600;
                            }
                        }else{
                            if(strtotime($date_time['start_lunch_break_time']['saturday_odd']) > $stime){
                                $t = (strtotime($date_time['come_home_early']['saturday_odd']) - $stime) / 3600 - ($lunch_break / 60);
                            }else{
                                $t = (strtotime($date_time['come_home_early']['saturday_odd']) - $stime) / 3600;
                            }
                        }

                    }elseif ((date('d', $time)%2) == 0) {
                        $lunch_break = $date_time['lunch_break']['saturday_even'];
                        $work_time = $date_time['work_time']['saturday_even'];

                        if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['saturday_even'])){
                            $stime = strtotime($date_time['late_for_work']['saturday_even']);
                        }else{
                            $stime = strtotime(date('H:i:s' ,strtotime($st)));
                        }

                        if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                            $_st = strtotime(date('H:i:s' ,strtotime($st)));
                            $_et = strtotime(date('H:i:s' ,strtotime($et)));
                            if($_st < strtotime($date_time['late_for_work']['saturday_even'])){
                                $_st = strtotime($date_time['late_for_work']['saturday_even']);
                            }elseif($_st > strtotime($date_time['start_lunch_break_time']['saturday_even']) && $_st < strtotime($date_time['start_afternoon_shift']['saturday_even']) ){
                                $_st = strtotime($date_time['start_afternoon_shift']['saturday_even']);
                            }
                            if($_et > strtotime($date_time['come_home_early']['saturday_even'])){
                                $_et = strtotime($date_time['come_home_early']['saturday_even']);
                            }
                            if(strtotime($date_time['start_lunch_break_time']['saturday_even']) > $stime && strtotime($date_time['start_afternoon_shift']['friday']) < $_et){
                                $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                            }else{
                                $t = ($_et - $_st) / 3600;
                            }
                        }else{
                            if(strtotime($date_time['start_lunch_break_time']['saturday_even']) > $stime){
                                $t = (strtotime($date_time['come_home_early']['saturday_even']) - $stime) / 3600 - ($lunch_break / 60);
                            }else{
                                $t = (strtotime($date_time['come_home_early']['saturday_even']) - $stime) / 3600;
                            }
                        }
                    }
                    break;
                case 7:
                    $lunch_break = $date_time['lunch_break']['sunday'];
                    $work_time = $date_time['work_time']['sunday'];

                    if(strtotime(date('H:i:s' ,strtotime($st))) < strtotime($date_time['late_for_work']['sunday'])){
                        $stime = strtotime($date_time['late_for_work']['sunday']);
                    }else{
                        $stime = strtotime(date('H:i:s' ,strtotime($st)));
                    }
                    if(date('Y-m-d', strtotime($st)) == date('Y-m-d', strtotime($et))){
                        $_st = strtotime(date('H:i:s' ,strtotime($st)));
                        $_et = strtotime(date('H:i:s' ,strtotime($et)));
                        if($_st < strtotime($date_time['late_for_work']['sunday'])){
                            $_st = strtotime($date_time['late_for_work']['sunday']);
                        }elseif($_st > strtotime($date_time['start_lunch_break_time']['sunday']) && $_st < strtotime($date_time['start_afternoon_shift']['sunday']) ){
                            $_st = strtotime($date_time['start_afternoon_shift']['sunday']);
                        }
                        if($_et > strtotime($date_time['come_home_early']['sunday'])){
                            $_et = strtotime($date_time['come_home_early']['sunday']);
                        }
                        if(strtotime($date_time['start_lunch_break_time']['sunday']) > $stime){
                            $t = ($_et - $_st) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = ($_et - $_st) / 3600;
                        }
                    }else{
                        if(strtotime($date_time['start_lunch_break_time']['sunday']) > $stime){
                            $t = (strtotime($date_time['come_home_early']['sunday']) - $stime) / 3600 - ($lunch_break / 60);
                        }else{
                            $t = (strtotime($date_time['come_home_early']['sunday']) - $stime) / 3600;
                        }
                    }
                    break;
            }
        }
        return number_format($t, 2);
    }
    /**
     * check choose when approving
     * @param   $related 
     */
    public function check_choose_when_approving($related){
        $this->db->select('choose_when_approving');
        $this->db->where('related',$related);
        $rs = $this->db->get(db_prefix().'timesheets_approval_setting')->row();
        if($rs){
            return $rs->choose_when_approving;
        }else{
            return 0;
        }
        
    }
    /**
     * send request approve
     * @param  array $data     
     * @param  integer $staff_id 
     * @return bool           
     */
    public function send_request_approve($data, $staff_id = ''){
        if(!isset($data['status'])){
            $data['status'] = '';
        }
        $date_send = date('Y-m-d H:i:s');
        $data_new = $this->get_approve_setting($data['rel_type']);
        $data_setting = $this->get_approve_setting($data['rel_type'], false);
        if(!$data_new){
            return false;
        }
        $this->delete_approval_details($data['rel_id'], $data['rel_type']);
        $list_staff = $this->staff_model->get();
        $list = [];
        $staff_addedfrom = $data['addedfrom'];
        if($staff_id == ''){
            $sender = get_staff_user_id();
        }else{
            $sender = $staff_id;
        }
        foreach ($data_new as $value) {
            $row = [];
            $row['notification_recipient'] = $data_setting->notification_recipient;
            $row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));

            if($value->approver !== 'specific_personnel'){
            $value->staff_addedfrom = $staff_addedfrom;
            $value->rel_type = $data['rel_type'];
            $value->rel_id = $data['rel_id'];
            
                $approve_value = $this->get_staff_id_by_approve_value($value, $value->approver);
                
                if(is_numeric($approve_value) && $approve_value > 0){
                    $approve_value = $this->staff_model->get($approve_value)->email;
                }else{

                    $this->db->where('rel_id', $data['rel_id']);
                    $this->db->where('rel_type', $data['rel_type']);
                    $this->db->delete(db_prefix().'timesheets_approval_details');


                    return $value->approver;
                }
                $row['approve_value'] = $approve_value;
            
            $staffid = $this->get_staff_id_by_approve_value($value, $value->approver);
            
            if(empty($staffid)){
                $this->db->where('rel_id', $data['rel_id']);
                $this->db->where('rel_type', $data['rel_type']);
                $this->db->delete(db_prefix().'timesheets_approval_details');


                return $value->approver;
            }

                $row['staffid'] = $staffid;
                $row['date_send'] = $date_send;
                $row['rel_id'] = $data['rel_id'];
                $row['rel_type'] = $data['rel_type'];
                $row['sender'] = $sender;
                $this->db->insert(db_prefix().'timesheets_approval_details', $row);

            }else if($value->approver == 'specific_personnel'){
                $row['staffid'] = $value->staff;
                $row['date_send'] = $date_send;
                $row['rel_id'] = $data['rel_id'];
                $row['rel_type'] = $data['rel_type'];
                $row['sender'] = $sender;

                $this->db->insert(db_prefix().'timesheets_approval_details', $row);
            }
        }
        return true;
    }
    /**
     * get approve setting
     * @param  integer $type         
     * @param  boolean $only_setting 
     * @param  string  $staff_id     
     * @return bool                
     */
     public function get_approve_setting($type, $only_setting = true, $staff_id = ''){
        if($staff_id == ''){
            $staff_id = get_staff_user_id();
        }
        $this->load->model('departments_model');
        $staff = $this->staff_model->get($staff_id);
        $departments = $this->departments_model->get_staff_departments($staff_id, true);

        


        $where_job_position = '';
        if($staff){
            if($staff->role != '' && $staff->role != 0){
                $where_job_position = 'find_in_set('.$staff->role.',job_positions)';
            }else{
                $where_job_position = '(job_positions is null or job_positions = "")';
            }
        }

        $where_departments = '';

        foreach ($departments as $key => $value) {
            if($where_departments != ''){
                $where_departments .= ' OR find_in_set('.$value.',departments)';
            }else{
                $where_departments = 'find_in_set('.$value.',departments)';
            }
        }

        $where = '';
        if($where_job_position != ''){
            $where = $where_job_position;
        }
        if($where_departments != ''){
            $where_departments = '('.$where_departments.')';
            if($where != ''){
                $where .= ' and '.$where_departments;
            }else{
                $where = '('.$where_departments.')';
            }
        }

        $this->db->select('*');
        $this->db->where('related', $type);
        if($where != ''){
            $this->db->where($where);
        }

        $approval_setting = $this->db->get(db_prefix().'timesheets_approval_setting')->row();
        
        if($approval_setting){
            if($only_setting == false){
                return $approval_setting;
            }else{
                return json_decode($approval_setting->setting);
            }
        }else{
            $this->db->select('*');
            $this->db->where('related', $type);
            if($where_departments != ''){
                $this->db->where('(job_positions is null or job_positions = "") AND '. $where_departments);
            }

            $approval_setting = $this->db->get(db_prefix().'timesheets_approval_setting')->row();
            if($approval_setting){
                if($only_setting == false){
                    return $approval_setting;
                }else{
                    return json_decode($approval_setting->setting);
                }
            }else{
                $this->db->select('*');
                $this->db->where('related', $type);
                $this->db->where('(job_positions is null or job_positions = "")');
                $this->db->where('(departments is null or departments = "")');

                $approval_setting = $this->db->get(db_prefix().'timesheets_approval_setting')->row();
                if($approval_setting){
                    if($only_setting == false){
                        return $approval_setting;
                    }else{
                        return json_decode($approval_setting->setting);
                    }
                }
            }
        }
        return false;
    }
    /**
     * delete_approval_details
     * @param  integer $rel_id   
     * @param  integer $rel_type
     * @return  bool          
     */
    public function delete_approval_details($rel_id, $rel_type)
    {
        $this->db->where('rel_id', $rel_id);
        $this->db->where('rel_type', $rel_type);
        $this->db->delete(db_prefix().'timesheets_approval_details');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * update approval details
     * @param  integer $id   
     * @param  array $data 
     * @return bool       
     */
    public function update_approval_details($id, $data){
        $data['date'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'timesheets_approval_details', $data);
        if($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * update approve request
     * @param  integer $rel_id   
     * @param  integer $rel_type
     * @param  integer $status   
     * @param  string $staffid  
     * @return integer           
     */
    public function update_approve_request($rel_id , $rel_type, $status, $staffid = ''){
        $data_update = [];
        switch ($rel_type) {
            case 'Leave':
                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                if($status == 1){
                $this->db->where('id', $rel_id);
                $requisition_leave = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
                $st = $requisition_leave->start_time;
                $et = $requisition_leave->end_time;

                if($staffid != ''){
                    $staff_id = $staffid;
                }else{
                    $staff_id = get_staff_user_id();
                }
                $type = '';
                switch ($requisition_leave->type_of_leave) {
                    case 1:
                        $type = 'SI';
                        break;
                    case 2:
                        $type = 'M';
                        break;
                    case 3:
                        $type = 'R';
                        break;
                    case 4:
                        $type = 'Ro';
                        break;
                    case 6:
                        $type = 'PO';
                        break;
                    case 7:
                        $type = 'ME';
                        break;
                    case 8:
                        $type = 'AL';

                        $day_off = $this->get_day_off($requisition_leave->staff_id);
                        $dd = $requisition_leave->number_of_leaving_day;
                        $number_days_off = $day_off->days_off;

                            $aa = $day_off->remain - $dd;
                            $bb = $number_days_off + $dd;
                            $this->db->where('staffid', $requisition_leave->staff_id);
                            $this->db->where('year', date('Y'));
                            $this->db->update(db_prefix().'timesheets_day_off',[
                                'remain' => $aa,
                                'days_off' => $bb
                            ]);                  
                        break;
                    
                }
                $staffid = $requisition_leave->staff_id;
                $number_of_day = $requisition_leave->number_of_leaving_day;
                if($requisition_leave->start_time != '' && $requisition_leave->end_time != ''){
                        $list_date = $this->get_list_date($requisition_leave->start_time, $requisition_leave->end_time);
                        $list_af_date = [];
                        foreach ($list_date as $key => $next_start_date) {
                          $data_work_time = $this->timesheets_model->get_hour_shift_staff($staffid, $next_start_date);
                          $data_day_off = $this->timesheets_model->get_day_off_staff_by_date($staffid, $next_start_date);
                          if($data_work_time > 0 && count($data_day_off) == 0){
                            $list_af_date[] = $next_start_date;
                          }
                        }                        

                        if(count($list_af_date) == 1){
                            $date_work = $requisition_leave->start_time;
                            $work_time = $this->timesheets_model->get_hour_shift_staff($staff_id, $date_work);

                                $this->db->where('staff_id', $staffid);
                                $this->db->where('date_work', $date_work);
                                $this->db->where('type', 'W');
                                $tslv = $this->db->get(db_prefix().'timesheets_timesheet')->row();
                                
                                if($tslv){
                                    if($number_of_day < 1 && $tslv->value > ($work_time * $number_of_day)){
                                        $this->db->where('staff_id', $staffid);
                                        $this->db->where('date_work', $date_work);
                                        $this->db->where('type', 'W');
                                        $this->db->update(db_prefix().'timesheets_timesheet', ['value' => ($work_time * $number_of_day)]);
                                    }
                                    else{
                                        $this->db->where('staff_id', $staffid);
                                        $this->db->where('date_work', $date_work);
                                        $this->db->where('type', 'W');
                                        $this->db->delete(db_prefix().'timesheets_timesheet');
                                    } 
                                }
                                if($number_of_day < 1){
                                    $work_time = $work_time * $number_of_day;
                                }
                                
                                $this->db->insert(db_prefix().'timesheets_timesheet', [
                                    'staff_id' => $staffid,
                                    'date_work' => $date_work,
                                    'value' => $work_time,
                                    'add_from' => $staffid,
                                    'type' => $type,
                                ]);
                        }
                        else{
                            $count_array = count($list_af_date);
                            $date_end = '';
                            $count_day = $number_of_day;
                            foreach ($list_af_date as $key => $date_work) {
                                    $work_time = $this->timesheets_model->get_hour_shift_staff($staff_id, $date_work);
                                    $this->db->where('staff_id', $staffid);
                                    $this->db->where('date_work', $date_work);
                                    $this->db->where('type', 'W');
                                    $tslv = $this->db->get(db_prefix().'timesheets_timesheet')->row();
                                    
                                    if($tslv){
                                        if($count_day < 1 && $tslv->value > ($work_time / 2)){
                                            $this->db->where('id', $tslv->id);
                                            $this->db->update(db_prefix().'timesheets_timesheet', ['value' => ($work_time / 2)]);
                                        }
                                        else{
                                            $this->db->where('id', $tslv->id);                       
                                            $this->db->delete(db_prefix().'timesheets_timesheet');
                                        } 
                                    }
                                    if($count_day < 1){
                                        $work_time = $work_time / 2;
                                    }
                                    $this->db->insert(db_prefix().'timesheets_timesheet',[
                                        'staff_id' => $staffid,
                                        'date_work' => $date_work,
                                        'value' => $work_time,
                                        'add_from' => $staffid,
                                        'type' => $type,
                                    ]);                                
                                    $count_day -= 1;          
                            }
                        }
                    }
                }
                return true;
                break;
            case 'Late_early':
                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                return true;
                break;
            case 'Go_out':
                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                return true;
                break;
            case 'Go_on_bussiness':
                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                if($status == 1){
                    $this->db->where('id', $rel_id);
                    $requisition_leave = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();

                    $start_time = strtotime($requisition_leave->start_time);
                    $end_time = strtotime($requisition_leave->end_time);

                    $st = date('Y-m-d', $start_time);
                    $et = date('Y-m-d', $end_time);

                    if($staffid != ''){
                        $staff_id = $staffid;
                    }else{
                        $staff_id = get_staff_user_id();
                    }

                    $type = 'B';
                    
                    if($requisition_leave->end_time != ''){
                        for ($i=0; $i < 5; $i++) {
                            $hour_work = $this->timesheets_model->get_hour_shift_staff($requisition_leave->staff_id, $st);
                            if(strtotime($st) <= strtotime($et)){
                                $this->db->insert(db_prefix().'timesheets_timesheet',[
                                                    'staff_id' => $requisition_leave->staff_id,
                                                    'date_work' => $st,
                                                    'value' => $hour_work,
                                                    'add_from' => $staff_id,
                                                    'type' => $type,
                                                ]);
                                $st = date('Y-m-d', strtotime($st. ' + 1 days'));
                                $i = 0;
                            }else{
                                $i = 10;
                            }
                        }
                    }else{
                        $hour_work = $this->timesheets_model->get_hour_shift_staff($requisition_leave->staff_id, $st);
                        $this->db->insert(db_prefix().'timesheets_timesheet',[
                                                    'staff_id' => $requisition_leave->staff_id,
                                                    'date_work' => $st,
                                                    'value' => $hour_work,
                                                    'add_from' => $staff_id,
                                                    'type' => $type,
                                                ]);
                    }
                }
                return true;
                break;
            case 'additional_timesheets':            
                if($staffid != ''){
                    $staff_id = $staffid;
                }else{
                    $staff_id = get_staff_user_id();
                }

                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_additional_timesheet', $data_update);
                if($status == 1){
                    $this->db->where('id', $rel_id);
                    $additional_timesheet = $this->db->get(db_prefix().'timesheets_additional_timesheet')->row();

                    $check_latch_timesheet = $this->timesheets_model->check_latch_timesheet(date('m-Y',strtotime($additional_timesheet->additional_day)));
                    if(!$check_latch_timesheet){
                        $this->edit_timesheets($additional_timesheet, $staffid);
                    }
                }

                return true;
                break;
            case 'quit_job':
                $data_update['status'] = $status;
                $this->db->where('id', $rel_id);
                $this->db->update(db_prefix().'timesheets_requisition_leave', $data_update);
                return true;
                break;

            default:
                return false;
                break;
        }
    }
    /**
     * add requisition ajax
     * @param array $data 
     */
    public function add_requisition_ajax($data){

        $staff_quit_job =  $data['staff_id'];

        if($data['rel_type'] == 1){
            $type = 'Leave';
        }elseif($data['rel_type'] == 2){
            $type = 'Late_early';
        }elseif($data['rel_type'] == 3){
            $type = 'Go_out';
        }elseif($data['rel_type'] == 4){
            $type = 'Go_on_bussiness';
        }elseif($data['rel_type'] == 5){
            $type = 'quit_job';
            $data['start_time'] = date('Y-m-d H:i:s');
            $data['end_time'] = date('Y-m-d H:i:s');
        }


        $data['datecreated'] = date('Y-m-d H:i:s');
        if(isset($data['used_to'])){
            $used_to = $data['used_to'];
            unset($data['used_to']);
        }

        if(isset($data['amoun_of_money'])){
            $amoun_of_money = $data['amoun_of_money'];
            unset($data['amoun_of_money']);
        }

        if(isset($data['request_date'])){
            $request_date = to_sql_date($data['request_date']);
            unset($data['request_date']);
        }

        if(isset($data['received_date'])){
            $received_date = $data['received_date'];
            unset($data['received_date']);
        }

        if(isset($data['advance_payment_reason'])){
            $advance_payment_reason = $data['advance_payment_reason'];
            unset($data['advance_payment_reason']);
        }

        $check_proccess = $this->get_approve_setting($type);

        if($check_proccess){
            $this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
            $insert_id = $this->db->insert_id();
            if($insert_id){
                handle_requisition_attachments($insert_id);
                if($data['rel_type'] == 4){
                    foreach($used_to as $key => $val){
                        $this->db->insert(db_prefix().'timesheets_go_bussiness_advance_payment', [
                            'requisition_leave' => $insert_id,
                            'used_to' => $val,
                            'amoun_of_money' =>  timesheets_reformat_currency_asset($amoun_of_money[$key]),
                            'request_date' => $request_date,
                            'advance_payment_reason' => $advance_payment_reason,
                        ]);
                    }
                }
                return $insert_id;
            }else{
                return false;
            }
        }else{
            $data['status'] = 1;
            $this->db->insert(db_prefix() . 'timesheets_requisition_leave', $data);
            $insert_id = $this->db->insert_id();
            if($insert_id){
                    handle_requisition_attachments($insert_id);
                   // if(isset($data['used_to'])){
                        if($data['rel_type'] == 4){
                            foreach($used_to as $key => $val){
                                $this->db->insert(db_prefix().'timesheets_go_bussiness_advance_payment', [
                                    'requisition_leave' => $insert_id,
                                    'used_to' => $val,
                                    'amoun_of_money' => timesheets_reformat_currency_asset($amoun_of_money[$key]),
                                    'request_date' => $request_date,
                                    'advance_payment_reason' => $advance_payment_reason,
                                ]);
                            }
                        }
                   // }
                return $insert_id;

            }else{
                return false;
            }
        }
     }
    
    public function delete_requisition($id){
        $this->db->where('id', $id);
        $rel_type = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
        if($rel_type){
            if($rel_type->rel_type == '4'){
                $this->db->where('requisition_leave', $id);
                $this->db->delete(db_prefix() . 'timesheets_go_bussiness_advance_payment');
            }
        }
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'timesheets_requisition_leave');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get option val
     * @return object 
     */
      public function get_option_val()
     {
        $this->db->select('option_val');
        $this->db->from('timesheets_option');
        $where_opt = 'option_name = "leave_according_process"';

        $this->db->where($where_opt);
        $query = $this->db->get()->row();
        return $query;
     }
     /**
      * get staff shift applicable object
      * @return array 
      */
     public function get_staff_shift_applicable_object(){
        $shift_applicable_object = [];
        $this->db->select('option_val');
        $this->db->where('option_name', 'shift_applicable_object');
        $row = $this->db->get(db_prefix() . 'timesheets_option')->row();
        if ($row) {
            if($row->option_val != ''){
                $shift_applicable_object = explode(',', $row->option_val);
            }else{
                return [];
            }
        }
       
        $where = '';
        if($shift_applicable_object)
        foreach ($shift_applicable_object as $key => $value) {
           if($where == ''){
                $where ='(role = '.$value;
           }else{
                $where .= ' OR role = '.$value;
           }
        }
        if($where != ''){
            $where .= ')';
        }

        if($where == ''){
            $where .= '(select count(*) from '.db_prefix().'staff_contract where staff = '.db_prefix().'staff.staffid and DATE_FORMAT(start_valid, "%Y-%m") <="'.date('Y-m').'" and IF(end_valid = null, DATE_FORMAT(end_valid, "%Y-%m") >="'.date('Y-m').'",1=1)) > 0 and status_work="working" and active=1';
        }else{
            $where .= ' and (select count(*) from '.db_prefix().'staff_contract where staff = '.db_prefix().'staff.staffid and DATE_FORMAT(start_valid, "%Y-%m") <="'.date('Y-m').'" and IF(end_valid = null, DATE_FORMAT(end_valid, "%Y-%m") >="'.date('Y-m').'",1=1)) > 0 and status_work="working" and active=1';
        }
        $this->db->where($where);
        
        return $this->db->get(db_prefix().'staff')->result_array();
    }
    /**
     * check latch timesheet
     * @param  integer $month 
     * @return bool        
     */
    public function check_latch_timesheet($month){
        if($month != ''){
            $this->db->where('month_latch', $month);
            $count = $this->db->count_all_results(db_prefix().'timesheets_latch_timesheet');
            
            if($count > 0){
                return true;
            }else{
                return false;
            }
        }

        return false;

    }
    /**
     * get staff timekeeping applicable object
     * @return array 
     */
    public function get_staff_timekeeping_applicable_object(){
        $data_timekeeping_form = get_timesheets_option('timekeeping_form');

        $timekeeping_applicable_object = [];
        if($data_timekeeping_form == 'timekeeping_task'){
            if(get_timesheets_option('timekeeping_task_role') != ''){
                $timekeeping_applicable_object = explode(',', get_timesheets_option('timekeeping_task_role'));
            }
        }elseif($data_timekeeping_form == 'timekeeping_manually'){
            if(get_timesheets_option('timekeeping_manually_role') != ''){
                $timekeeping_applicable_object = explode(',', get_timesheets_option('timekeeping_manually_role'));
            }
        }elseif($data_timekeeping_form == 'csv_clsx'){
            if(get_timesheets_option('csv_clsx_role') != ''){
                $timekeeping_applicable_object = explode(',', get_timesheets_option('csv_clsx_role'));
            }
        }

        $where = '';
        if($timekeeping_applicable_object){
            foreach ($timekeeping_applicable_object as $key => $value) {
               if($where == ''){
                    $where ='role = '.$value;
               }else{
                    $where .= ' OR role = '.$value;
               }
            }
        }

        if($where != ''){
            $where .= '';
        }

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }
        $result = $this->db->get(db_prefix().'staff')->result_array();
        return $result;
    }
    /**
     * unlatch timesheet
     * @param  integer $month 
     * @return bool        
     */
    public function unlatch_timesheet($month){
        if($month != ''){
            $this->db->where('month_latch', $month);
            $this->db->delete(db_prefix().'timesheets_latch_timesheet');

            if ($this->db->affected_rows() > 0) {
                $m = date('m', strtotime('01-'.$month));
                $y = date('Y', strtotime('01-'.$month));
                $this->db->where('month(date_work) = '.$m.' and year(date_work) = '.$y);
                $this->db->update(db_prefix().'timesheets_timesheet', ['latch' => 0]);

                return true;
            }
            return false;
        }

        return false;

    }
    /**
     * get hour check in out staff
     * @param  integer $staff_id 
     * @param  date $datetime 
     * @return integer          
     */
    public function get_hour_check_in_out_staff($staff_id, $datetime){

        $list_check_in_out = $this->db->query('select date from '.db_prefix().'check_in_out where staff_id = '.$staff_id.' and date(date) = \''.$datetime.'\'')->result_array();
        $hour = 0;
        $lunch_time = 0;
        if(isset($list_check_in_out[0]['date'])&&isset( $list_check_in_out[1]['date'])){
            //$hour = $this->get_hour($list_check_in_out[0]['date'], $list_check_in_out[1]['date']);

            $d1 = $this->format_date_time($list_check_in_out[0]['date']);
            $d2 = $this->format_date_time($list_check_in_out[1]['date']); 

            $time_in = strtotime(date('H:i:s' ,strtotime($d1)));
            $time_out = strtotime(date('H:i:s' ,strtotime($d2)));

            $list_shift = $this->get_shift_work_staff_by_date($staff_id, $datetime);  
            foreach ($list_shift as $ss) {
                $data_shift_type = $this->timesheets_model->get_shift_type($ss);

                $time_in_ = $time_in;
                $time_out_ = $time_out;
                if($data_shift_type){                    
                    $d1 = $this->format_date_time($data_shift_type->time_start_work);
                    $d2 = $this->format_date_time($data_shift_type->time_end_work);
                    $d3 = $this->format_date_time($data_shift_type->start_lunch_break_time);
                    $d4 = $this->format_date_time($data_shift_type->end_lunch_break_time);

                    $start_work = strtotime(date('H:i:s', strtotime($d1)));
                    $end_work = strtotime(date('H:i:s', strtotime($d2)));
                    $start_lunch_break = strtotime(date('H:i:s' ,strtotime($d3)));
                    $end_lunch_break = strtotime(date('H:i:s' ,strtotime($d4)));

                    if($time_in < $start_work && $time_out > $start_work){
                        $time_in_ = $start_work;
                    }

                    if($time_out > $end_work && $time_in < $end_work){
                        $time_out_ = $end_work;
                    }
                    if($time_out < $start_work){
                        continue;
                    }
                    if($time_out_ >= $end_lunch_break){
                        $lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
                    }
                    $hour += round(abs($time_out_ - $time_in_)/(60*60),2);
                }  
            }            
        }

        $result = abs($lunch_time - $hour);
        if($result == 0){
            return '';
        }
        return $result;
    }
    /**
     * report by leave statistics
     */
     public function report_by_leave_statistics()
    {
        $months_report = $this->input->post('months_report');
        $custom_date_select = '';
        if ($months_report != '') {
            
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = '(hrl.start_time BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = '(hrl.start_time BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = '(hrl.start_time BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = '(hrl.start_time BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select =  'hrl.start_time ="' . $from_date . '"';
                } else {
                    $custom_date_select = '(hrl.start_time BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
           
        }

        $chart = [];
        $dpm = $this->departments_model->get();
        foreach($dpm as $d){
            $chart['categories'][] = $d['name'];

            $chart['sick_leave'][] = $this->count_type_leave($d['departmentid'],1,$custom_date_select);
            $chart['maternity_leave'][] = $this->count_type_leave($d['departmentid'],2,$custom_date_select);
            $chart['private_work_with_pay'][] = $this->count_type_leave($d['departmentid'],3,$custom_date_select);
            $chart['private_work_without_pay'][] = $this->count_type_leave($d['departmentid'],4,$custom_date_select);
            $chart['child_sick'][] = $this->count_type_leave($d['departmentid'],5,$custom_date_select);
            $chart['power_outage'][] = $this->count_type_leave($d['departmentid'],6,$custom_date_select);
            $chart['meeting_or_studying'][] = $this->count_type_leave($d['departmentid'],7,$custom_date_select);
        }
        
        return $chart;
    }

/**
 * count type leave
 * @param   integer $department         
 * @param   integer $type            
 * @param   date  $custom_date_select 
 * @return array                     
 */
    public function count_type_leave($department, $type,$custom_date_select){

        if($custom_date_select != ''){
            $query = $this->db->query('select hrl.id, hrl.subject from '.db_prefix().'timesheets_requisition_leave hrl left join '.db_prefix().'staff_departments sd on sd.staffid = hrl.staff_id where sd.departmentid = '.$department.' and hrl.type_of_leave = '.$type.' and '.$custom_date_select)->result_array();
        }else{
            $query = $this->db->query('select hrl.id, hrl.subject from '.db_prefix().'timesheets_requisition_leave hrl left join '.db_prefix().'staff_departments sd on sd.staffid = hrl.staff_id where sd.departmentid = '.$department.' and hrl.type_of_leave = '.$type)->result_array();
        }

        return count($query);

    }
    /**
     * count timekeeping by month
     * @param  integer $staffid 
     * @param  integer $month   
     * @return integer          
     */
    public function count_timekeeping_by_month($staffid, $month){
        if($staffid != '' && $staffid != 0){
            $month = date('m-Y',strtotime($month));

            $check_latch_timesheet = $this->check_latch_timesheet($month);

            if(!$check_latch_timesheet){
                return 0;
            }

            $this->db->where('DATE_FORMAT(date_work, "%m-%Y") = "'.$month.'" AND staff_id = '.$staffid);
            $timekeeping = $this->db->get(db_prefix().'timesheets_timesheet')->result_array();
            $count_timekeeping = 0;
            $count_result = 0;
            foreach ($timekeeping as $key => $value) {
                if($value['type'] == 'W' || $value['type'] == 'H' || $value['type'] == 'R' || $value['type'] == 'CT' || $value['type'] == 'CD'){
                    $count_timekeeping += $value['value'];
                }
            }

            $work_shift = $this->get_data_edit_shift_by_staff($staffid);
                    
            $lunch_break = (($work_shift[3]['monday'][0] - $work_shift[2]['monday'][0]) * 60) + ($work_shift[3]['monday'][1] - $work_shift[2]['monday'][1]);
            $work_time = (($work_shift[1]['monday'][0] - $work_shift[0]['monday'][0]) * 60) + ($work_shift[1]['monday'][1] - $work_shift[0]['monday'][1]);

            $count_result += $count_timekeeping / (round($work_time - $lunch_break)/60);
            return $count_result;
        }else{
            return 1;
        }
    }

    /**
     * get timesheets task ts by month
     * @param  date $date 
     * @return array       
     */
    public function get_timesheets_task_ts_by_month($date){
        $query = 'select DATE_FORMAT(FROM_UNIXTIME(`start_time`), \'%Y-%m-%d\') AS date_work, DATE_FORMAT(FROM_UNIXTIME(`end_time`), \'%Y-%m-%d\') AS end_date, staff_id, start_time, end_time FROM '.db_prefix().'taskstimers where DATE_FORMAT(FROM_UNIXTIME(`start_time`), \'%Y-%m-%d\') <= \''.$date.'\' and DATE_FORMAT(FROM_UNIXTIME(`end_time`), \'%Y-%m-%d\') >= \''.$date.'\'';
        $data_task = $this->db->query($query)->result_array();
    }
    /**
     * get ts by task and staff
     * @param  date $date  
     * @param  imteger $staff 
     * @return array        
     */
    public function get_ts_by_task_and_staff($date,$staff){
        $query = 'select DATE_FORMAT(FROM_UNIXTIME(`start_time`), \'%Y-%m-%d\') AS date_work, DATE_FORMAT(FROM_UNIXTIME(`end_time`), \'%Y-%m-%d\') AS end_date, staff_id, start_time, end_time FROM '.db_prefix().'taskstimers where DATE_FORMAT(FROM_UNIXTIME(`start_time`), \'%Y-%m-%d\') <= \''.$date.'\' and DATE_FORMAT(FROM_UNIXTIME(`end_time`), \'%Y-%m-%d\') >= \''.$date.'\' and staff_id = '.$staff;
        $data_timesheets = $this->db->query($query)->result_array();
    }
    /**
     * [check_format_date_ymd
     * @param  date $date 
     * @return boolean       
     */
    public function check_format_date_ymd($date) {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * check format date
     * @param  date $date 
     * @return boolean 
     */
    public function check_format_date($date){
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s(0|[0-1][0-9]|2[0-4]):?((0|[0-5][0-9]):?(0|[0-5][0-9])|6000|60:00)$/",$date)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * get_role
     * @param  integer $roleid 
     * @return object or array         
     */
    public function get_role($roleid){
        $this->db->where('roleid',$roleid);
        return $this->db->get(db_prefix().'roles')->row();
    }

    /**
     * leave of the year
     * @param  integer $staff_id 
     * @return object or array           
     */
    public function leave_of_the_year($staff_id = ''){
        if($staff_id!=''){
            $this->db->where('staff_id',$staff_id);
            return $this->db->get(db_prefix().'leave_of_the_year')->row();
        }
        else{
            return $this->db->get(db_prefix().'leave_of_the_year')->result_array();
        }
    }
    /**
     * get staff query
     * @param  string $query 
     * @return array        
     */
    public function get_staff_query($query = '')
    {
        if($query != ''){
            $query = ' where '.$query;
        }
        return $this->db->query('select * from '.db_prefix() . 'staff'.$query)->result_array();
    }
    /**
     * add shift type
     * @param integer 
     */
    public function add_shift_type($data){      
        if(isset($data['time_start'])){
            if(!$this->check_format_date_ymd($data['time_start'])){
                $data['time_start'] = to_sql_date($data['time_start']);
            }
        }
        if(isset($data['time_end'])){        
            if(!$this->check_format_date_ymd($data['time_end'])){
                $data['time_end'] = to_sql_date($data['time_end']);
            }              
        }              
        $this->db->insert(db_prefix() . 'shift_type', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return $insert_id;
        }
        return 0;
    }
     /**
     * update shift type
     * @param integer 
     */
    public function update_shift_type($data){      
        if(isset($data['time_start'])){
            if(!$this->check_format_date_ymd($data['time_start'])){
                $data['time_start'] = to_sql_date($data['time_start']);
            }
        }
        if(isset($data['time_end'])){        
            if(!$this->check_format_date_ymd($data['time_end'])){
                $data['time_end'] = to_sql_date($data['time_end']);
            }              
        } 
        $this->db->where('id',$data['id']);             
        $this->db->update(db_prefix() . 'shift_type', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * delete shift type
     * @param  integer  
     * @return bool   
     */
    public function delete_shift_type($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'shift_type');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * get shift type
     * @param  integer  
     * @return bool   
     */
    public function get_shift_type($id=''){
        if($id != ''){
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'shift_type')->row();
        }
        else{
            return $this->db->get(db_prefix() . 'shift_type')->result_array();
        }
    }
    /**
     * get staff shift list
     * @return db 
     */
    public function get_staff_shift_list(){
        return $this->db->query('select distinct(staff_id) from '.db_prefix().'work_shift_detail')->result_array();
    }

    /**
     * get data staff shift list
     * @return db 
     */
    public function get_data_staff_shift_list($staff_id){
        return $this->db->query('select * from '.db_prefix().'work_shift_detail where staff_id = '.$staff_id)->result_array();
    }
    /**
     * check in
     * @param  array $data 
     * @return integer       
     */
    public function check_in($data){
        $id_admin = 0;
        $date = '';
        $affectedRows = 0;
        if(!isset($data['date'])){
            $data['date'] = date('Y-m-d');
            $date = $data['date'];
        }
        if(isset($data['admin'])){

            if($data['admin'] != ''){
                if(!$this->check_format_date($data['date'])){
                    $data['date'] = to_sql_date($data['date'], true);
                }
                else{
                    $data['date'] = $data['date'];
                }            
            }
            $get_date = explode(" ", $data['date']);
            $date = $get_date[0];
            $id_admin = $data['admin'];
            unset($data['admin']);
        }
        else{
            if(!$this->check_format_date_ymd($data['date'])){
                $data['date'] = to_sql_date($data['date']);
            }
            else{
                $data['date'] = $data['date'];
            }   
            $date = $data['date'];
            $data['date'] = $data['date'].' '.$data['hours'];
        }
        unset($data['hours']);

        $test = $this->check_ts($data['staff_id'],$date)->check_in;
        if($test != 0){
            $this->db->where('id', $test);
            $this->db->update(db_prefix().'check_in_out', ["date" => $data["date"]]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }
        else{
            $this->db->insert(db_prefix().'check_in_out',$data);
            $insert_id = $this->db->insert_id();
            if ($insert_id) {
                $affectedRows++;
            }  
        }
        $list_check_in_out = $this->db->query('select date from '.db_prefix().'check_in_out where staff_id = '.$data['staff_id'].' and date(date) = \''.$date.'\'')->result_array();
        if(isset($list_check_in_out[0]['date'])&&isset( $list_check_in_out[1]['date'])){
            $this->automatic_insert_timesheets($data['staff_id'] , $list_check_in_out[0]['date'],$list_check_in_out[1]['date']);
        }
        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
     * check out
     * @param  array $data 
     * @return integer       
     */
    public function check_out($data){
        $id_admin = 0;
        $date = '';
        $affectedRows = 0;
        if(!isset($data['date'])){
            $data['date'] = date('Y-m-d');
            $date = $data['date'];
        }
        if($data['staff_id'] == ''){
            $data['staff_id'] = get_staff_user_id();
        }

        if(isset($data['admin'])){
            if($data['admin'] != ''){
                if(!$this->check_format_date($data['date'])){
                    $data['date'] = to_sql_date($data['date'], true);
                }
                else{
                    $data['date'] = $data['date'];
                }            
            }
            $id_admin = $data['admin'];
            unset($data['admin']);
        }
        else{
            if(!$this->check_format_date_ymd($data['date'])){
                $data['date'] = to_sql_date($data['date']);
            }else{
                $data['date'] = $data['date'];
            }            
            $data['date'] = $data['date'].' '.$data['hours'];
        }
        
        if($data['date']){
            $date_split = explode(' ', $data['date']);
            $date = $date_split[0];
        }
        unset($data['hours']);
        $insert_id = '';
        $test = $this->check_ts($data['staff_id'],$date)->check_out;
        if($test != 0){
            $this->db->where('id', $test);
            $this->db->update(db_prefix().'check_in_out',["date" => $data["date"]]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }else{
            $this->db->insert(db_prefix().'check_in_out',$data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                $affectedRows++;
            }
        }
        $list_check_in_out = $this->db->query('select date from '.db_prefix().'check_in_out where staff_id = '.$data['staff_id'].' and date(date) = \''.$date.'\'')->result_array();
        if(isset($list_check_in_out[0]['date'])&&isset( $list_check_in_out[1]['date'])){
            $this->automatic_insert_timesheets($data['staff_id'] , $list_check_in_out[0]['date'],$list_check_in_out[1]['date']);
        }
        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }
    /**
     * check_ts check checked in and checked out
     * @param  integer $staff_id 
     * @param  date $date     
     * @return stdClass           
     */
    public function check_ts($staff_id, $date){
        $check_in = 0;
        $check_out = 0;
        $data_check_in = $this->db->query('select id from '.db_prefix().'check_in_out where staff_id = '.$staff_id.' and date(date) = \''.$date.'\' and type_check = 1')->row();
        if($data_check_in){
            $check_in = $data_check_in->id;
        }
        $data_check_out = $this->db->query('select id from '.db_prefix().'check_in_out where staff_id = '.$staff_id.' and date(date) = \''.$date.'\' and type_check = 2')->row();
        if($data_check_out){
            $check_out = $data_check_out->id;
        }
        $data_check_result = new stdClass();
        $data_check_result->check_in = $check_in;
        $data_check_result->check_out = $check_out;
        return $data_check_result;
    }
    /**
     * get shift data
     * @param  integer $id 
     * @return stdClass     
     */
    public function get_shift_data($id){
        $data_shift_res = new stdClass();
        $data_shift_res->name = '';
        $data_shift_res->color = '';
        $data_shift_res->description = '';
        $data_shift_res->datecreated = '';

        $data_shift_res->time_start_work = '';
        $data_shift_res->time_end_work = '';

        $data_shift_res->start_lunch_break_time = '';
        $data_shift_res->end_lunch_break_time = '';

        $data_shift_res->start_work_hour = '';
        $data_shift_res->end_work_hour = '';

        $data_shift_res->start_lunch_hour = '';
        $data_shift_res->end_lunch_hour = '';

        $this->db->where('id', $id);
        $data_shift = $this->db->get(db_prefix().'shift_type')->row();
        if($data_shift){
                $data_shift_res->name = $data_shift->shift_type_name;
                $data_shift_res->color = $data_shift->color;
                $data_shift_res->description = $data_shift->description;
                $data_shift_res->datecreated = $data_shift->datecreated;
 
                $time_start_sp = explode(' ', $data_shift->time_start_work);
                $time_end_sp = explode(' ', $data_shift->time_end_work);
                $start_lunch_sp = explode(' ', $data_shift->start_lunch_break_time);
                $end_lunch_sp = explode(' ', $data_shift->end_lunch_break_time);

                $data_shift_res->time_start_work = $data_shift->time_start_work;
                $data_shift_res->time_end_work = $data_shift->time_end_work;

                $data_shift_res->start_lunch_break_time = $data_shift->start_lunch_break_time;
                $data_shift_res->end_lunch_break_time = $data_shift->end_lunch_break_time;


                $data_shift_res->start_work_hour = isset($time_start_sp[1]) ? $time_start_sp[1] : '';
                $data_shift_res->end_work_hour = isset($time_end_sp[1]) ? $time_end_sp[1] : '';

                $data_shift_res->start_lunch_hour = isset($start_lunch_sp[1]) ? $start_lunch_sp[1] : '';
                $data_shift_res->end_lunch_hour = isset($end_lunch_sp[1]) ? $end_lunch_sp[1] : '';
        }
        return $data_shift_res;
    }
    /**
     * get hour shift staff
     * @param  integer $staff_id 
     * @param  integer $date     
     * @return integer           
     */
    public function get_hour_shift_staff($staff_id, $date){
        $result = 0;
        $data_shift_list = $this->get_shift_work_staff_by_date($staff_id, $date);
        foreach ($data_shift_list as $ss) {
            $data_shift_type = $this->get_shift_type($ss);            
            if($data_shift_type){
                $hour = $this->get_hour($data_shift_type->time_start_work, $data_shift_type->time_end_work);
                $lunch_hour = $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
                $result += abs($hour - $lunch_hour);
            }  
        }        
        return $result;        
    }
    /**
     * get hour
     * @param date $date1 
     * @param date $date2
     * @return decimal        
     */
    public function get_hour($date1, $date2){
        $result = 0; 
        if($date1 != '' && $date2 != ''){
            $timestamp1 = strtotime($date1);
            $timestamp2 = strtotime($date2);
            $result = number_format(abs($timestamp2 - $timestamp1)/(60*60),2);
        }
        return $result;
    }
    /**
     * format date
     * @param  date $date     
     * @return date           
     */
    public function format_date($date){
        if(!$this->check_format_date_ymd($date)){
            $date = to_sql_date($date);
        }
        return $date;
     }            

/**
 * format date time
 * @param  date $date     
 * @return date           
 */
    public function format_date_time($date){
        if(!$this->check_format_date($date)){
            $date = to_sql_date($date, true);
        }
        return $date;
    }
    /**
     * get workshiftms
     * @param integer $id 
     * @return integer     
     */
    public function get_workshiftms($id){
        $this->db->where('id',$id);
        return $this->db->get(db_prefix().'work_shift')->row();
    }
    /**
     * get id shift type by date and master id
     * @param  integer $staff_id      
     * @param  integer $date          
     * @param  integer $work_shift_id 
     * @return integer                
     */
    public function get_id_shift_type_by_date_and_master_id($staff_id, $date, $work_shift_id){
        if($staff_id != ''&&$date != ''&&$work_shift_id != ''){
            $this->db->where('staff_id', $staff_id);
            $this->db->where('date', $date);
            $this->db->where('work_shift_id', $work_shift_id);
            $this->db->select('shift_id');
            $data = $this->db->get(db_prefix().'work_shift_detail')->row();
            if($data){
                return $data->shift_id;
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }

    /**
     * Gets the total standard workload.
     *
     * @param      int  $staffid  The staffid
     * @param      Date  $f_date   The f date
     * @param      Date  $t_date   The t date
     * 
     * @return     array   The staff select.
     */
    public function get_total_work_time($staffid, $f_date, $t_date){
        $total = 0;
        while (strtotime($f_date) <= strtotime($t_date)) {
            $standard_workload = $this->get_hour_shift_staff($staffid, $f_date);
            $total += $standard_workload;

            $f_date = date('Y-m-d', strtotime('+1 day', strtotime($f_date)));
        }

        return $total;
    }

    /**
     * get_shift_type_id_by_number_day
     * @param  int $work_shift_id 
     * @param  int $number        
     * @param  int $staff_id      
     * @return int object               
     */
    public function get_shift_type_id_by_number_day($work_shift_id, $number, $staff_id = ''){
        if($work_shift_id != ''){   
            if($staff_id != ''){  
                $query = 'select shift_id from '.db_prefix().'work_shift_detail_number_day where work_shift_id = '.$work_shift_id.' and number = \''.$number.'\' and staff_id = '.$staff_id;
                return $this->db->query($query)->row();
            }
            else{
                $query = 'select shift_id from '.db_prefix().'work_shift_detail_number_day where work_shift_id = '.$work_shift_id.' and number = \''.$number.'\'';
                return $this->db->query($query)->row();
            }
        }
    }
    
    public function get_first_staff_work_shift($work_shift_id){
        if($work_shift_id){
            if($work_shift_id != ''){
                $this->db->where('work_shift_id',$work_shift_id);
                return $this->db->get(db_prefix().'work_shift_detail')->row();
            }
        }
    }
    /**
     * Gets the number day.
     *
     * @param      string   $from_date  The from date
     * @param      string   $to_date    To date
     *
     * @return     integer  The number day.
     */
    public function get_number_day($from_date, $to_date, $staffid){
        $count = 0;
        if($to_date == ''){
            $to_date = date('Y-m-d');
        }
        for ($i=0; $i < 5; $i++) {
            if(strtotime($from_date) <= strtotime($to_date)){
                if($this->get_hour_shift_staff($staffid, $from_date)){
                    $count++;
                }
                
                $from_date = date('Y-m-d', strtotime($from_date. ' + 1 days'));
                $i = 0;
            }else{
                $i = 10;
            }
        }
        return $count;
    }


    /**
     * delete shift staff by day name
     * @param   int $work_shift_id 
     * @param   string $day_name      
     * @param   int $staff_id      
     */
    public function get_shift_staff_by_day_name($work_shift_id , $day_name, $staff_id = '')
    {
       if($work_shift_id!='' && $day_name!=''){
           if($staff_id ==''){
                $this->db->where('number', $day_name);                                    
                $this->db->where('work_shift_id', $work_shift_id);                                    
                return $this->db->get(db_prefix().'work_shift_detail_number_day')->row();
           }
           else{
                $this->db->where('staff_id', $staff_id);                                    
                $this->db->where('number', $day_name);                                    
                $this->db->where('work_shift_id', $work_shift_id);                                    
                return $this->db->get(db_prefix().'work_shift_detail_number_day')->row();
           }
       }
    }
    /**
     * convert_day_to_number
     * @param  int $day 
     * @return  int    
     */
    public function convert_day_to_number($day){
         switch ($day) {
            case 'mon':
            return '1';
            case 'tue':
            return '2';
            case 'wed':
            return '3';
            case 'thu':
            return '4';
            case 'fri':
            return '5';
            case 'sat':
            return '6';
            case 'sun':
            return '7';
        }
    }

     /**
     * get shift staff by date
     * @param   int $work_shift_id 
     * @param   string $day_name      
     * @param   int $staff_id      
     */
    public function get_shift_staff_by_date($work_shift_id , $date, $staff_id = '')
    {
       if($work_shift_id!='' && $date!=''){
           if($staff_id ==''){
                $this->db->where('date', $date);                                    
                $this->db->where('work_shift_id', $work_shift_id);                                    
                return $this->db->get(db_prefix().'work_shift_detail')->row();
           }
           else{
                $this->db->where('staff_id', $staff_id);                                    
                $this->db->where('date', $date);                                    
                $this->db->where('work_shift_id', $work_shift_id);                                    
                return $this->db->get(db_prefix().'work_shift_detail')->row();
           }
       }
    }
    /**
     * get_work_shift
     * @param  int $id 
     * @return object or array object     
     */
    public function get_work_shift($id = ""){
       if($id !=''){
            $this->db->where('id', $id);                                    
            return $this->db->get(db_prefix().'work_shift')->row();
       }
       else{                                               
            return $this->db->get(db_prefix().'work_shift')->result_array();
       }
    }

    /**
     * get day off staff by date
     * @param  integer $staff 
     * @param  date $date  
     * @return array        
     */
    public function get_day_off_staff_by_date($staff,$date){      
         if($staff!='' && $date != ''){
            $this->db->where('staffid', $staff);
            $this->db->select('role');
            $role_data = $this->db->get(db_prefix().'staff')->row();
            $role_id = 0;
            if($role_data){
                $role_id = $role_data->role;
            }

            $data_department = $this->departments_model->get_staff_departments($staff,true);
            $list_department = '';
            if($data_department){
                foreach ($data_department as $key => $value) {
                    if($list_department == ''){
                        $list_department .= 'find_in_set('.$value.', department)';
                    }else{
                        $list_department .= ' or find_in_set('.$value.', department)';
                    }
                }
                if($list_department != ''){
                    $list_department = '('.$list_department.') and ';
                }
            }

            $query = 'select * from '.db_prefix().'day_off where '.$list_department.' find_in_set('.$role_id.',position) and break_date = \''.$date.'\'';
            $query2 = 'select * from '.db_prefix().'day_off where '.$list_department.'find_in_set('.$role_id.',position) and day(break_date) = day(\''.$date.'\') and month(break_date) = month(\''.$date.'\') and repeat_by_year = 1';
            $result = $this->db->query($query)->result_array();
            $result2 = $this->db->query($query2)->result_array();
            $list_shift_id = [];
            foreach ($result as $key => $value) {
               $list_shift_id[] = $value;
            }
            foreach ($result2 as $key => $value) {
               $list_shift_id[] = $value;
            }
            return $list_shift_id;
        }
    }
    /**
     * get_staffid_ts_by_year
     * @param  string $month_array 
     * @return array              
     */
    public function get_staffid_ts_by_year($month_array){
        $string='select * from '.db_prefix().'timesheets_timesheet where year(date_work)="'.$month_array.'"';
        return $this->db->query($string)->result_array();
    }
    /**
     * get staffid ts by month
     * @param  integer $month 
     * @return array        
     */
    public function get_staffid_ts_by_month($month){
        $string='select * from '.db_prefix().'timesheets_timesheet where month(date_work)="'.$month.'"';
        return $this->db->query($string)->result_array();
    }
    /**
     * getStaff
     * @param  string $id    
     * @param  array  $where 
     * @return array        
     */
    public function getStaff($id = '', $where = [])
    {
        $select_str = '*,CONCAT(firstname," ",lastname) as full_name';

        if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
            $select_str .= ',(SELECT COUNT(*) FROM ' . db_prefix() . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
        }
        $this->db->select($select_str);
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('staffid', $id);
            $staff = $this->db->get(db_prefix() . 'staff')->row();

            if ($staff) {
                $staff->permissions = $this->get_staff_permissions($id);
            }

            return $staff;
        }
        $this->db->join(db_prefix() . 'timesheets_timesheet', db_prefix() . 'timesheets_timesheet.id = (select '.db_prefix() . 'timesheets_timesheet.id from '.db_prefix() . 'timesheets_timesheet where '.db_prefix() . 'timesheets_timesheet.staff_id = ' . db_prefix() . 'staff.staffid limit 1)', 'left');
        
        $this->db->order_by('firstname', 'desc');

        return $this->db->get(db_prefix() . 'staff')->result_array();
    }
    /**
     * fetch all timesheet
     */
     function fetch_all_timesheet(){
      $this->db->select('*');
      $this->db->order_by('id');
      $this->db->from('timesheets_timesheet');
      $this->db->join('staff', 'timesheets_timesheet.staff_id = staff.staffid');
      return $this->db->get();
     }
     /**
      * get timesheets option
      * @param  string $option 
      * @return object or array         
      */
    public function get_timesheets_option($option){
        if($option != ''){
            $this->db->where('option_name', $option);
            return $this->db->get(db_prefix().'timesheets_option')->row();
        }else{
            return $this->db->get(db_prefix().'timesheets_option')->result_array();
        }
    }
    /**
     * get data edit shift by staff
     * @param  integer $staff 
     * @param  date $date  
     * @return array        
     */
    public function get_shift_work_staff_by_date($staff, $date = ''){
        $nv = $this->staff_model->get($staff);
        $dpm = $this->departments_model->get_staff_departments($staff,true);
        $sql_dpm = '';
        if($dpm){
            foreach ($dpm as $key => $value) {
                if($sql_dpm == ''){
                    $sql_dpm .= 'find_in_set('.$value.', department)';
                }else{
                    $sql_dpm .= ' or find_in_set('.$value.', department)';
                }
            }
        }
        
        if($date == ''){
            $date = date('Y-m-d');
        } 
        $sql_where = 'find_in_set('.$staff.', staff)';
        $this->db->where($sql_where);
        $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
        $shift = $this->db->get(db_prefix().'work_shift')->result_array();
       
        if(!$shift){
            if($sql_dpm != '' && ($nv) && ($nv->role != 0 && $nv->role != '')){
                $this->db->where('find_in_set('.$nv->role.', position)');
                $this->db->where('('.$sql_dpm.')');
                $this->db->where('(staff = "" or staff IS NULL)');
                $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                
                if(!$shift){
                    $this->db->where('(position = "" or position IS NULL)');
                    $this->db->where('('.$sql_dpm.')');
                    $this->db->where('(staff = "" or staff IS NULL)');
                    $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                    $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                    
                    if(!$shift){
                        $this->db->where('find_in_set('.$nv->role.', position)');
                        $this->db->where('(department = "" or department IS NULL)');
                        $this->db->where('(staff = "" or staff IS NULL)');
                        $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                        $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                        
                        if(!$shift){
                            $this->db->where('(position = "" or position IS NULL)');
                            $this->db->where('(department = "" or department IS NULL)');
                            $this->db->where('(staff = "" or staff IS NULL)');
                            $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                            $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                        }
                    }
                }
            }elseif($sql_dpm == '' && ($nv) && ($nv->role != 0 && $nv->role != '')){
                $this->db->where('find_in_set('.$nv->role.', position)');
                $this->db->where('(department = "" or department IS NULL)');
                $this->db->where('(staff = "" or staff IS NULL)');
                $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                if(!$shift){
                    $this->db->where('(position = "" or position IS NULL)');
                    $this->db->where('(department = "" or department IS NULL)');
                    $this->db->where('(staff = "" or staff IS NULL)');
                    $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                    $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                }
            }elseif($sql_dpm != '' && ($nv) && ($nv->role == 0 || $nv->role == '')){
                $this->db->where('(position = "" or position IS NULL)');
                $this->db->where('('.$sql_dpm.')');
                $this->db->where('(staff = "" or staff IS NULL)');
                $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                if(!$shift){
                    $this->db->where('(position = "" or position IS NULL)');
                    $this->db->where('(department = "" or department IS NULL)');
                    $this->db->where('(staff = "" or staff IS NULL)');
                    $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                    $shift = $this->db->get(db_prefix().'work_shift')->result_array();
                }
            }elseif($sql_dpm == '' && ($nv) && ($nv->role == 0 || $nv->role == '')){
                $this->db->where('(position = "" or position IS NULL)');
                $this->db->where('(department = "" or department IS NULL)');
                $this->db->where('(staff = "" or staff IS NULL)');
                $this->db->where('("'.$date.'" >=  from_date AND "'.$date.'" <= to_date)');
                $shift = $this->db->get(db_prefix().'work_shift')->result_array();
            }
            
            if(!$shift){
                $shift = $this->db->get(db_prefix().'work_shift')->result_array();
            }
        }

        $list_shift_id = [];
        
        if($shift){
            foreach ($shift as $key => $value) {
                $day_number = date('N',strtotime($date));
                $this->db->where('(staff_id = '.$staff.' or staff_id = 0)');
                $this->db->where('date',$date);
                $this->db->where('work_shift_id',$value['id']);
                $shift_detail = $this->db->get(db_prefix().'work_shift_detail')->row();
                if($shift_detail){
                    if(!in_array($shift_detail->shift_id, $list_shift_id)){
                        $list_shift_id[] = $shift_detail->shift_id;   
                    }
                }
                $this->db->where('(staff_id = '.$staff.' or staff_id = 0)');
                $this->db->where('number',$day_number);
                $this->db->where('work_shift_id',$value['id']);
                $shift_detail = $this->db->get(db_prefix().'work_shift_detail_number_day')->row();

                if($shift_detail){
                    if(!in_array($shift_detail->shift_id, $list_shift_id)){
                        $list_shift_id[] = $shift_detail->shift_id;   
                    }
                }
            }
        }
        return $list_shift_id;
        
    }

    /**
     * get shift work staff by date
     * @param   int $staff 
     * @param   date $date  
     * @return  array      
     */
    public function get_shift_work_staff_by_date_2($staff, $date){
        if($staff!='' && $date != ''){
            $this->db->where('staffid', $staff);
            $this->db->select('role');
            $role_data = $this->db->get(db_prefix().'staff')->row();
            $role_id = 0;
            if($role_data){
                $role_id = $role_data->role;
            }
            $data_department = $this->db->query('select * from '.db_prefix().'staff_departments where staffid = '.$staff)->result_array();
            $list_department = '';
            foreach ($data_department as $key => $group) {
                $list_department .= ' find_in_set('.$group['departmentid'].',department) or'; 
            }
            $day_name = date('D',strtotime($date));
            $day_number = $this->convert_day_to_number(strtolower($day_name));
            $query = 'select shift_id from '.db_prefix().'work_shift_detail_number_day where (work_shift_id in (SELECT id FROM '.db_prefix().'work_shift where (('.$list_department.' position = '.$role_id.')  or (department = "" and position = "" and position = "")) and type_shiftwork = \'repeat_periodically\' ) or staff_id = '.$staff.') and number = '.$day_number;
            $query2 = 'select shift_id from '.db_prefix().'work_shift_detail where (work_shift_id in (SELECT id FROM '.db_prefix().'work_shift where (('.$list_department.' position = '.$role_id.') or (department = "" and position = "" and position = "")) and type_shiftwork = \'by_absolute_time\' ) or staff_id = '.$staff.') and date = \''.$date.'\'';
            $result = $this->db->query($query)->result_array();
            $result2 = $this->db->query($query2)->result_array();
            $list_shift_id = [];
            foreach ($result as $key => $value) {
                if(!in_array($value['shift_id'], $list_shift_id)){
                    $list_shift_id[] = $value['shift_id'];   
                }
            }
            foreach ($result2 as $key => $value) {
                if(!in_array($value['shift_id'], $list_shift_id)){
                    $list_shift_id[] = $value['shift_id'];
                }
            }
            return $list_shift_id;
        }
    }

    /**
     * send mail
     * @param  array $data    
     * @param  integer $staffid 
     */
        public function send_mail($data, $staffid = ''){
        if($staffid == ''){
            $staff_id = $staffid;
        }else{
            $staff_id = get_staff_user_id();
        }
        $this->load->model('emails_model');
        if(!isset($data['status'])){
            $data['status'] = '';
        }
        $get_staff_enter_charge_code = '';
        $mes = 'notify_send_request_approve_project';
        $staff_addedfrom = 0;
        $additional_data = $data['rel_type'];
        $object_type = $data['rel_type'];
        $type = '';
        switch ($data['rel_type']) {
            case 'hr_planning':
                $hrplanning = $this->get_proposal_hrplanning($data['rel_id']);
                $staff_addedfrom = '';
                $additional_data = '';
                if($hrplanning){
                    $staff_addedfrom = $hrplanning->requester;
                    $additional_data = $hrplanning->proposal_name;
                }
                $type = _l('hr_planning');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve_hr_planning_proposal';
                $mes_approve = 'notify_send_approve_hr_planning_proposal';
                $mes_reject = 'notify_send_rejected_hr_planning_proposal';
                $link = 'timesheets/hr_planning?tab=hr_planning_proposal#' . $data['rel_id'];
                break;

            case 'candidate_evaluation':
                $this->load->model('recruitment/recruitment_model');

                $candidate = $this->recruitment_model->get_candidates($data['candidate']);
                $additional_data = '';
                if($recruitment){
                    $staff_addedfrom = $candidate->cp_add_from;
                    $additional_data = $candidate->candidate_name;
                }

                $type = _l('interview_result');

                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'recruitment/candidate/' . $data['candidate'].'?evaluation='.$data['rel_id'];
                break;

            case 'recruitment_campaign':
                $this->load->model('recruitment/recruitment_model');
                $staff_addedfrom = $data['addedfrom'];
                $recruitment = $this->recruitment_model->get_campaign_by_id($data['rel_id']);
                $additional_data = '';
                if($recruitment){
                    $additional_data = $recruitment->campaign_name;
                }

                $type = _l('recruitment_campaign');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'recruitment/recruitment_campaign/' . $data['rel_id'];

                break;
            case 'Leave':
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = _l('Leave');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_detail/' . $data['rel_id'];
                break;
            case 'Late_early':
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = _l('Late_early');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_detail/' . $data['rel_id'];
                break;
            case 'Go_out':
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = _l('Go_out');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_detail/' . $data['rel_id'];
                break;
            case 'Go_on_bussiness':
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = _l('Go_on_bussiness');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_detail/' . $data['rel_id'];
                break;
            case 'additional_timesheets':
                $additional_timesheets =  $this->get_additional_timesheets($data['rel_id']);
                $data['addedfrom'] = $additional_timesheets->creator;
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = '';
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve_additional_timesheets';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id='.$data['rel_id'];
                break;
             case 'recruitment_proposal':
                $this->load->model('recruitment/recruitment_model');
                $additional_data = '';
                $staff_addedfrom = $data['addedfrom'];
                $proposal = $this->recruitment_model->get_rec_proposal($data['rel_id']);
                if($proposal){
                    $additional_data = $proposal->proposal_name;
                }
                $type = _l('recruitment_proposal');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'recruitment/recruitment_proposal/' . $data['rel_id'];
                break;
            case 'quit_job':
                $staff_addedfrom = $data['addedfrom'];
                $additional_data = _l('quit_job');
                $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
                $mes = 'notify_send_request_approve';
                $mes_approve = 'notify_send_approve';
                $mes_reject = 'notify_send_rejected';
                $link = 'timesheets/requisition_detail/' . $data['rel_id'];
                break;

            default:
                
                break;
        }


        $check_approve_status = $this->check_approval_details($data['rel_id'], $data['rel_type'], $data['status']);
        if(isset($check_approve_status['staffid'])){

            if(!in_array($staff_id,$check_approve_status['staffid'])){
                foreach ($check_approve_status['staffid'] as $value) {
                    $staff = $this->staff_model->get($value);
                    $notified = add_notification([
                    'description'     => $mes,
                    'touserid'        => $staff->staffid,
                    'link'            => $link,
                    'additional_data' => serialize([
                        $additional_data,
                    ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$staff->staffid]);
                    }
                    if($data['rel_type'] != 'additional_timesheets' && $data['rel_type'] != 'Go_on_bussiness' && $data['rel_type'] != 'Leave' && $data['rel_type'] != 'Late_early' && $data['rel_type'] != 'Go_out'){
                        $this->emails_model->send_simple_email($staff->email, _l('request_approval'), _l('email_send_request_approve', $type) .' <a href="'.admin_url($link).'">'.$additional_data.'</a> '._l('from_staff', get_staff_full_name($staff_addedfrom)));
                    }
                }
            }
        }

        if(isset($data['approve'])){
            if($data['approve'] == 1){
                $mes = $mes_approve;
                $mes_email = 'email_send_approve';
            }else{
                $mes = $mes_reject;
                $mes_email = 'email_send_rejected';
            }
            
            $staff = $this->staff_model->get($staff_addedfrom);
            $notified = add_notification([
            'description'     => $mes,
            'touserid'        => $staff->staffid,
            'link'            => $link,
            'additional_data' => serialize([
                $additional_data,
            ]),
            ]);
            if ($notified) {
                pusher_trigger_notification([$staff->staffid]);
            }
            if($data['rel_type'] != 'additional_timesheets' && $data['rel_type'] != 'Go_on_bussiness' && $data['rel_type'] != 'Leave' && $data['rel_type'] != 'Late_early' && $data['rel_type'] != 'Go_out'){
                $this->emails_model->send_simple_email($staff->email, _l('approval_notification'), _l($mes_email, $type.' <a href="'.admin_url($link).'">'.$additional_data.'</a> ').' '._l('by_staff', get_staff_full_name($staff_id)));
            }
            
            foreach($list_approve_status as $key => $value){
            $value['staffid'] = explode(', ',$value['staffid']);
                if($value['approve'] == 1 && !in_array(get_staff_user_id(),$value['staffid'])){
                    foreach ($value['staffid'] as $staffid) {
                        $staff = $this->staff_model->get($staffid);
                        $notified = add_notification([
                        'description'     => $mes,
                        'touserid'        => $staff->staffid,
                        'link'            => $link,
                        'additional_data' => serialize([
                            $additional_data,
                        ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$staff->staffid]);
                        }

                        if($data['rel_type'] != 'additional_timesheets' && $data['rel_type'] != 'Go_on_bussiness' && $data['rel_type'] != 'Leave' && $data['rel_type'] != 'Late_early' && $data['rel_type'] != 'Go_out'){
                            $this->emails_model->send_simple_email($staff->email, _l('approval_notification'), _l($mes_email, $type. ' <a href="'.admin_url($link).'">'.$additional_data.'</a>').' '._l('by_staff', get_staff_full_name($staff_id)));
                        }
                    }
                }
            }

            $mes_approve_n = 'notify_send_approve_n';
            $mes_reject_n = 'notify_send_rejected_n';

            if($data['approve'] == 1){
                $mes_ar_n = $mes_approve_n;
            }else{
                $mes_ar_n = $mes_reject_n;
            }

            $this->db->select('*');
            $this->db->where('related', $data['rel_type']);
            $approval_setting = $this->db->get(db_prefix().'timesheets_approval_setting')->row();

            if($approval_setting){
                $notification_recipient = $approval_setting->notification_recipient;
                $arr_notification_recipient =  explode(",", $notification_recipient);
            }else{
                $arr_notification_recipient=[];
            }   

            if(count($arr_notification_recipient) > 0){

                $mail_template = 'send-request-approve';

                if(!in_array($staff_id,$arr_notification_recipient)){
                    foreach ($arr_notification_recipient as $value1) {
                        $notified = add_notification([
                        'description'     => $mes_ar_n,
                        'touserid'        => $value1,
                        'link'            => $link,
                        'additional_data' => serialize([
                            $additional_data,
                        ]),
                        ]);

                        if ($notified) {
                            pusher_trigger_notification([$value1]);
                        }
                    }

                    if($data['rel_type'] != 'additional_timesheets' && $data['rel_type'] != 'Go_on_bussiness' && $data['rel_type'] != 'Leave' && $data['rel_type'] != 'Late_early' && $data['rel_type'] != 'Go_out'){
                        $this->emails_model->send_simple_email($staff->email, _l('approval_notification'), _l($mes_email, $type.' <a href="'.admin_url($link).'">'.$additional_data.'</a>').' '._l('by_staff', get_staff_full_name($staff_id)));
                    }
                }
            }
        }
    }
    /**
     * send notifi handover recipients
     * @param  array $data 
     * @return array       
     */
    public function send_notifi_handover_recipients($data){
        $this->load->model('emails_model');
        if(!isset($data['status'])){
            $data['status'] = '';
        }

        $get_staff_enter_charge_code = '';
        $mes = 'notify_send_request_approve_project';
        $staff_addedfrom = 0;
        $additional_data = $data['rel_type'];
        $object_type = $data['rel_type'];

        $staff_addedfrom = $data['addedfrom'];
        $additional_data = _l('Leave');
        $list_approve_status = $this->get_list_approval_details($data['rel_id'],$data['rel_type']);
        $mes = 'chosen_you_to_handover_recipients_requisition';

        $mes_approve = 'notify_send_approve';
        $mes_reject = 'notify_send_rejected';
        $link = 'timesheets/requisition_detail/' . $data['rel_id'];
    

        $this->db->select('*');
        $this->db->where('id', $data['rel_id']);
        $requisition_leave = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
        if($requisition_leave){
            $staff_handover_recipients =  $requisition_leave->handover_recipients;
            $additional_data = $requisition_leave->subject;

        }else{
            $staff_handover_recipients =  'false';

        }
        
        if(is_numeric($staff_handover_recipients)){

            $mail_template = 'send-request-approve';

            if(get_staff_user_id() != $staff_handover_recipients){
                    $notified = add_notification([
                    'description'     => $mes,
                    'touserid'        => $staff_handover_recipients,
                    'link'            => $link,
                    'additional_data' => serialize([
                        $additional_data,
                    ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$staff_handover_recipients]);
                    }
                      
            
            }
        }
    }
    /**
     * send notification recipient
     * @param  array $data 
     */
    public function send_notification_recipient($data){
        $this->load->model('emails_model');
        if(!isset($data['status'])){
            $data['status'] = '';
        }


        $mes = 'send_request';
        $link = 'timesheets/requisition_detail/' . $data['rel_id'];

        $this->db->select('*');
        $this->db->where('id', $data['rel_id']);
        $requisition_leave = $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
        if($requisition_leave){
            $additional_data = $requisition_leave->subject;

        }else{
             $additional_data = '';

        }


        $this->db->select('*');
        $this->db->where('related', "Leave");
        $approval_setting = $this->db->get(db_prefix().'timesheets_approval_setting')->row();

        if($approval_setting){
            $notification_recipient = $approval_setting->notification_recipient;
            $arr_notification_recipient =  explode(",", $notification_recipient);
        }else{
            $arr_notification_recipient=[];
        }   

        
        if(count($arr_notification_recipient) > 0){

            $mail_template = 'send-request-approve';

            if(!in_array(get_staff_user_id(),$arr_notification_recipient)){
                foreach ($arr_notification_recipient as $value) {

                    $notified = add_notification([
                    'description'     => $mes,
                    'touserid'        => $value,
                    'link'            => $link,
                    'additional_data' => serialize([
                        $additional_data,
                    ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$value]);
                    }            
                }
            }
        }

    }
    /**
     * get date time
     * @param  integer $work_shift 
     * @return array             
     */
    public function get_date_time($work_shift){
        $day = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'sunday', 'saturday_odd', 'saturday_even'];
        $date_return = [];
        foreach ($day as $value) {
            $date_return['lunch_break'][$value] = (($work_shift[3][$value][0] - $work_shift[2][$value][0]) * 60) + ($work_shift[3][$value][1] - $work_shift[2][$value][1]);
            $date_return['work_time'][$value] = (($work_shift[1][$value][0] - $work_shift[0][$value][0]) * 60) + ($work_shift[1][$value][1] - $work_shift[0][$value][1]);
            $date_return['late_for_work'][$value] = $work_shift[0][$value][0].':'.$work_shift[0][$value][1].':00';
            $date_return['start_lunch_break_time'][$value] = $work_shift[2][$value][0].':'.$work_shift[2][$value][1].':00';
            $date_return['come_home_early'][$value] = $work_shift[1][$value][0].':'.$work_shift[1][$value][1].':00';
            $date_return['late_latency_allowed'][$value] = $work_shift[4][$value][0].':'.$work_shift[4][$value][1].':00';
            $date_return['start_afternoon_shift'][$value] = $work_shift[3][$value][0].':'.$work_shift[3][$value][1].':00';
        }
        return $date_return;
    }
    /**
     * Gets the overtime setting.
     *
     * @param      string  $id     The identifier
     *
     * @return     <type>  The overtime setting.
     */
    public function get_overtime_setting($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'timesheets_overtime_setting')->row();
        }

        return  $this->db->get(db_prefix() . 'timesheets_overtime_setting')->result_array();
    }    public function add_overtime_setting($data){
        $this->db->insert(db_prefix() . 'timesheets_overtime_setting', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    /**
     * update overtime setting
     * @param  array $data 
     * @param  integer $id   
     * @return bool       
     */
    public function update_overtime_setting($data, $id)
    {   
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'timesheets_overtime_setting', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * delete overtime setting
     * @param  integer $id 
     * @return boolean     
     */
    public function delete_overtime_setting($id){
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'timesheets_overtime_setting');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    } 

    /**
     * get additional timesheets
     * @param  integer $id 
     * @return array     
     */
    public function get_additional_timesheets($id = ''){
        if (is_numeric($id)) {
            $this->db->select('*');
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'timesheets_additional_timesheet')->row();
        }

        if(!is_admin() && !has_permission('timesheets_additional_timesheets','','view')){
            $this->db->where(get_hierarchy_sql('additional_timesheets') . ' or '.get_staff_user_id() .' in (select staffid from '.db_prefix().'timesheets_approval_details where rel_type = "additional_timesheets" and rel_id = '.db_prefix().'timesheets_additional_timesheet.id)');
        }
        $this->db->order_by('id', 'desc');
        return $this->db->get(db_prefix() . 'timesheets_additional_timesheet')->result_array();
    }
      /**
     * get vacation days of the year 
     * @param  [int] $staff_id 
     * @return [int] $year            
     */
    public function get_requisition_number_of_day_off($staff_id, $year = false)
    {

        if($year == false){
            $year = date('Y');
        }

        $result_total_day_off= $this->get_day_off_by_year($staff_id, $year);

        


        if($result_total_day_off){
            $total_day_off_in_year  = (float)$result_total_day_off->total;
            $total_day_off_allowed_in_year = (float)$result_total_day_off->remain;
        }else{
            $total_day_off_in_year  = (float)0;
            $total_day_off_allowed_in_year = (float)0;
        }
        $data = [];
        $data['total_day_off_in_year']          = $total_day_off_in_year;
        $status_leave = $this->timesheets_model->get_number_of_days_off($staff_id);

        $data['total_day_off_allowed_in_year'] = 0;
        $data['total_day_off'] =0;
        if($result_total_day_off != null){
            $data['total_day_off_allowed_in_year'] = $status_leave - ($result_total_day_off->total - $result_total_day_off->remain);
            if($data['total_day_off_allowed_in_year'] < 0){
                $data['total_day_off_allowed_in_year'] = 0;
            }
            $data['total_day_off']  = $result_total_day_off->total - $result_total_day_off->remain;
        }
        return $data;
    }

    /**
     * [get_date_leave_in_month description]
     * @param  [int] $staff_id [description]
     * @return [Y-mm] $month             [description]
     */
    public function get_date_leave_in_month($staff_id, $month)
    {
        if($staff_id != '' && $staff_id != 0){
            $this->db->where('DATE_FORMAT(date_work, "%Y-%m") = "'.$month.'" AND staff_id = '.$staff_id);
            $timekeeping = $this->db->get(db_prefix().'timesheets_timesheet')->result_array();
            $count_timekeeping = 0;
            $count_result = 0;
            foreach ($timekeeping as $key => $value) {
                if($value['type'] == 'AL' ){
                    $count_result += $value['value'] / $this->get_hour_shift_staff($staff_id, $value['date_work']);
                }
            }
            return $count_result;
        }else{
            return 1;
        }


    }

        /**
     * Gets the day off by year.
     *
     * @param      <type>  $staffid  The staffid
     * @param      <type>  $year     The year
     *
     * @return     <type>  The day off by year.
     */
    public function get_day_off_by_year($staffid, $year)
    {
       $this->db->where('staffid',$staffid);
       $this->db->where('year',$year);
       return $this->db->get(db_prefix().'timesheets_day_off')->row();
    }
    /**
     * get workplace
     * @return array 
     */
    public function get_workplace()
    {
        return $this->db->query('select * from ' . db_prefix() . 'workplace')->result_array();
    }
    /**
     * get allowance type
     * @param  integer $id 
     * @return object or array      
     */
    public function get_allowance_type($id = false){
        if (is_numeric($id)) {
            $this->db->where('type_id', $id);

            return $this->db->get(db_prefix() . 'allowance_type')->row();
        }

        if ($id == false) {
           return  $this->db->get(db_prefix() . 'allowance_type')->result_array();
        }
    }
    /**
     * update allowance type
     * @param  $data 
     * @param  $id   
     * @return  boolean     
     */
    public function update_allowance_type($data, $id)
    {
        $data['allowance_val'] = reformat_currency($data['allowance_val']);
        $this->db->where('type_id', $id);
        $this->db->update(db_prefix() . 'allowance_type', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * delete allowance type
     * @param  $id 
     * @return  boolean   
     */
    public function delete_allowance_type($id){
        $this->db->where('type_id', $id);
        $this->db->delete(db_prefix() . 'allowance_type');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
    /**
     * get salary form
     * @param  boolean $id 
     * @return object or array      
     */
    public function get_salary_form($id = false){
        if (is_numeric($id)) {
            $this->db->where('form_id', $id);

            return $this->db->get(db_prefix() . 'salary_form')->row();
        }

        if ($id == false) {
        return $this->db->query('select * from ' . db_prefix() . 'salary_form')->result_array();
        }
    }
    /**
     * add salary form
     * @param array $data 
     * @return object or array  
     */
    public function add_salary_form($data){
        $data['salary_val'] = reformat_currency($data['salary_val']);
        $this->db->insert(db_prefix() . 'salary_form', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    /**
     * update salary form
     * @param  array $data 
     * @param  integer $id   
     * @return boolean       
     */
    public function update_salary_form($data, $id)
    {   
        $data['salary_val'] = reformat_currency($data['salary_val']);
        $this->db->where('form_id', $id);
        $this->db->update(db_prefix() . 'salary_form', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * delete salary form
     * @param $id 
     * @return boolean      
     */
    public function delete_salary_form($id){
        $this->db->where('form_id', $id);
        $this->db->delete(db_prefix() . 'salary_form');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
   
    /**
     * get province
     * @return [type] 
     */
    public function get_province(){
        return $this->db->get(db_prefix().'province_city')->result_array();
    }
    public function get_procedure_retire($id = ''){
        if($id == ''){
            return $this->db->get(db_prefix().'procedure_retire')->result_array();
        }else{
            $this->db->where('procedure_retire_id', $id);
            return $this->db->get(db_prefix().'procedure_retire')->result_array();
        }
    }
/**
 * get staff info
 * @param  integer $staffid 
 * @return integer          
 */
    public function get_staff_info($staffid){
        $this->db->where('staffid', $staffid);
        $results = $this->db->get(db_prefix().'staff')->row();
        return $results;        
    }
  
    /**
     * timesheets setting get allowance key
     * @return array 
     */
    public function timesheets_setting_get_allowance_key(){
        $allowance_no_taxable = json_decode(get_timesheets_option('allowance_no_taxable'), true);
        $arr_allowance_key =[];
        if($allowance_no_taxable){
            foreach ($allowance_no_taxable as $allowance_key) {
                array_push($arr_allowance_key, $allowance_key['allowance_name']);
            }
        }
        return $arr_allowance_key;
    }

    /**
     * get approval process
     * @param  integer $id 
     * @return object array     
     */
    public function get_approval_process($id = '')
    {
        if(is_numeric($id)){
            $this->db->where('id', $id);
            return $this->db->get(db_prefix().'timesheets_approval_setting')->row();
        }
        return $this->db->get(db_prefix().'timesheets_approval_setting')->result_array();
    }
    
/**
 * get_timesheet
 * @param  integer $staffid   
 * @param  date $from_date 
 * @param  date $to_date   
 * @return array            
 */
    public function get_timesheet($staffid='', $from_date, $to_date){
        return $this->db->query('select * from '.db_prefix().'timesheets_timesheet where staff_id = '.$staffid.' and date_work between \''.$from_date.'\' and \''.$to_date.'\'')->result_array();
    }
    /**
     * report by working hours
     */
    public function report_by_working_hours()
    {
        $months_report = $this->input->post('months_report');
        $custom_date_select = '';
        $custom_date_select1 = '';
        if ($months_report != '') {
            
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = '(ht.date_work BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
                $custom_date_select1 = '(ht.additional_day BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = '(ht.date_work BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
                 $custom_date_select1 = '(ht.additional_day BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = '(ht.date_work BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';

                $custom_date_select1 = '(ht.additional_day BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = '(ht.date_work BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';

                $custom_date_select1 = '(ht.additional_day BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select =  'ht.date_work ="' . $from_date . '"';
                    $custom_date_select1 =  'ht.additional_day ="' . $from_date . '"';
                } else {
                    $custom_date_select = '(ht.date_work BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                    $custom_date_select1 = '(ht.additional_day BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
           
        }

        $chart = [];
        $dpm = $this->departments_model->get();
        foreach($dpm as $d){
            $chart['categories'][] = $d['name'];

            $chart['total_work_hours'][] = $this->count_work_hours($d['departmentid'],$custom_date_select,$custom_date_select1);
            $chart['total_work_hours_approved'][] = $this->count_work_hours_approve($d['departmentid'],$custom_date_select1);
            
        }
        
        return $chart;
    }
    /**
     * count work hours                     
     */
     public function count_work_hours($department,$custom_date_select,$custom_date_select1){
        if($custom_date_select != ''){
           $list = $this->db->query('select ht.staff_id, ht.date_work, ht.value from '.db_prefix().'timesheets_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.staff_id where sd.departmentid = '.$department.' and ht.type = "W" and '.$custom_date_select)->result_array();
        }else{
            $list = $this->db->query('select ht.staff_id, ht.date_work, ht.value from '.db_prefix().'timesheets_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.staff_id where sd.departmentid = '.$department.' and ht.type = "W"')->result_array();
        }
        
        if($custom_date_select1 != ''){
            $list_app = $this->db->query('select ht.creator, ht.additional_day, ht.timekeeping_value from '.db_prefix().'timesheets_additional_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.creator where sd.departmentid = '.$department.' and ht.status = 1 and '.$custom_date_select1)->result_array();
        }else{
            $list_app = $this->db->query('select ht.creator, ht.additional_day, ht.timekeeping_value from '.db_prefix().'timesheets_additional_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.creator where sd.departmentid = '.$department.' and ht.status = 1')->result_array();
        }


        
        $sum = 0;

        if(count($list) > 0){
            foreach($list as $li){
                if(is_numeric($li['value'])){
                    $sum += $li['value'];
                }
                
            }
        }

        if(count($list_app) > 0){
            foreach($list_app as $lis){
                if(is_numeric($lis['timekeeping_value'])){
                    $sum += $lis['timekeeping_value'];
                }
            }
        }
        

        return $sum;
    }
  
    /**
         * count work hours approve
         * @param  integer $department          
         * @param  date $custom_date_select1 
         * @return integer                      
         */
    public function count_work_hours_approve($department,$custom_date_select1){
        

        if($custom_date_select1 != ''){
            $list_app = $this->db->query('select ht.creator, ht.additional_day, ht.timekeeping_value from '.db_prefix().'timesheets_additional_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.creator where sd.departmentid = '.$department.' and ht.status = 1 and '.$custom_date_select1)->result_array();
        }else{
            $list_app = $this->db->query('select ht.creator, ht.additional_day, ht.timekeeping_value from '.db_prefix().'timesheets_additional_timesheet ht left join '.db_prefix().'staff_departments sd on sd.staffid = ht.creator where sd.departmentid = '.$department.' and ht.status = 1')->result_array();
        }
        $sum = 0;

        

        if(count($list_app) > 0){
            foreach($list_app as $lis){
                if(is_numeric($lis['timekeeping_value'])){
                    $sum += $lis['timekeeping_value'];
                }
                
            }
        }
        

        return $sum;
    }
    /**
     * add approval process
     * @param array $data 
     * @return boolean 
     */
    public function add_approval_process($data)
    {
        unset($data['approval_setting_id']);


        if(isset($data['staff'])){
            $setting = [];
            foreach ($data['staff'] as $key => $value) {
                $node = [];
                $node['approver'] = 'specific_personnel';
                $node['staff'] = $data['staff'][$key];

                $setting[] = $node;
            }
            unset($data['approver']);
            unset($data['staff']);
        }



        if(!isset($data['choose_when_approving'])){
            $data['choose_when_approving'] = 0;
        }

        if(isset($data['departments'])){
            $data['departments'] = implode(',', $data['departments']);
        }

        if(isset($data['job_positions'])){
            $data['job_positions'] = implode(',', $data['job_positions']);
        }

        $data['setting'] = json_encode($setting);

        if(isset($data['notification_recipient'])){
            $data['notification_recipient'] = implode(",", $data['notification_recipient']);
        }

        $this->db->insert(db_prefix() .'timesheets_approval_setting', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return true;
        }
        return false;
    }
    /**
     * update approval process
     * @param  integer $id   
     * @param  array $data 
     * @return boolean       
     */
    public function update_approval_process($id, $data)
    {
         if(isset($data['staff'])){
            $setting = [];
            foreach ($data['staff'] as $key => $value) {
                $node = [];
                $node['approver'] = 'specific_personnel';
                $node['staff'] = $data['staff'][$key];

                $setting[] = $node;
            }
            unset($data['approver']);
            unset($data['staff']);
        }
        
        if(!isset($data['choose_when_approving'])){
            $data['choose_when_approving'] = 0;
        }

        $data['setting'] = json_encode($setting);

        if(isset($data['departments'])){
            $data['departments'] = implode(',', $data['departments']);
        }else{
            $data['departments'] = '';
        }

        if(isset($data['job_positions'])){
            $data['job_positions'] = implode(',', $data['job_positions']);
        }else{
            $data['job_positions'] = '';
        }

        if(isset($data['notification_recipient'])){
            $data['notification_recipient'] = implode(",", $data['notification_recipient']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() .'timesheets_approval_setting', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    /**
     * delete approval setting
     * @param  integer $id 
     * @return boolean     
     */
    public function delete_approval_setting($id)
    {
        if(is_numeric($id)){
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() .'timesheets_approval_setting');

            if ($this->db->affected_rows() > 0) {
                return true;
            }
        }
        return false;
    }
    public function setting_timekeeper($data){
        $affectedRows = 0;
        if(isset($data['timekeeping_task_role'])){

            $timekeeping_task_role = implode(',', $data['timekeeping_task_role']);

            $this->db->where('option_name', 'timekeeping_task_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => $timekeeping_task_role,
                    ]);
        }else{
            $this->db->where('option_name', 'timekeeping_task_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => '',
                    ]);
        }
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if(isset($data['timekeeping_manually_role'])){
            $timekeeping_manually_role = implode(',', $data['timekeeping_manually_role']);
            $this->db->where('option_name', 'timekeeping_manually_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => $timekeeping_manually_role,
                    ]);
        }else{
            $this->db->where('option_name', 'timekeeping_manually_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => '',
                    ]);
        }
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if(isset($data['csv_clsx_role'])){

            $csv_clsx_role = implode(',', $data['csv_clsx_role']);

            $this->db->where('option_name', 'csv_clsx_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => $csv_clsx_role,
                    ]);
        }else{
            $this->db->where('option_name', 'csv_clsx_role');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => '',
                    ]);
        }
        if ($this->db->affected_rows() > 0) {
        $affectedRows++;
        }

        if(isset($data['timekeeping_form'])){

            $timekeeping_form = $data['timekeeping_form'];

            $this->db->where('option_name', 'timekeeping_form');
            $this->db->update(db_prefix() . 'timesheets_option', [
                        'option_val' => $timekeeping_form,
                    ]);
            if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    public function edit_timesheets($data, $staffid = ''){
        if($staffid != ''){
            $staff_id = $staffid;
        }else{
            $staff_id = get_staff_user_id();
        }        
        $additional_day = $data->additional_day;
        $data_ts =  $data->time_in.':00';
        $data_te = $data->time_out.':00';

        $time_in = $additional_day.' '.$data_ts;
        $time_out = $additional_day.' '.$data_te;
        $staff = $this->staff_model->get($data->creator);
        $data_new = [];

        if($data->time_in != '' && $data->time_out != ''){            
                $data_work_time = $this->get_hour_shift_staff($staff_id, $data->additional_day);
                if($data_work_time != 0){
                    $this->db->where('staff_id', $staff_id);
                    $this->db->where('date_work', $data->additional_day);
                    $this->db->where('type', 'W');
                    $tslv = $this->db->get(db_prefix().'timesheets_timesheet')->row();                                
                    if($tslv){
                            $new_value = $tslv->value + $data->timekeeping_value;
                            if($new_value > $data_work_time){
                                $new_value = $data_work_time;
                            }
                            $this->db->where('id', $tslv->id);
                            $this->db->update(db_prefix().'timesheets_timesheet', ['value' => $new_value]);
                    }
                    else{
                        $this->automatic_insert_timesheets($staff_id, $time_in, $time_out);
                    }
                }        
        }         
        return true;
    }
    /**
     * add additional timesheets
     * @param array $data    
     * @param integer $staffid 
     */
      public function add_additional_timesheets($data, $staffid = ''){
        if($staffid == ''){
            $staff_id = get_staff_user_id();
        }else{
            $staff_id = $staffid; 
        }
        if($data['time_in'] == 'null' || $data['time_in'] == ''){
            $data['time_in'] = '';
        }

        if($data['time_out'] == 'null' || $data['time_out'] == ''){
            $data['time_out'] = '';
        }
        if(($data['timekeeping_value'] == '0' || $data['timekeeping_value'] == '') &&  $data['time_in'] != '' &&  $data['time_out'] != '') {
            if($data['timekeeping_type'] == 'W'){
                $rest_time = number_format($this->get_rest_time($data['additional_day'], $staff_id)/60, 2);
              $data['timekeeping_value'] = ((strtotime($data['time_out'].':00') - strtotime($data['time_in'].':00')) / 3600) - $rest_time;
            }else{
              $data['timekeeping_value'] = (strtotime($data['time_out'].':00') - strtotime($data['time_in'].':00')) / 3600;
            }
        }
        
        if($data['timekeeping_value'] < 0){
            $data['timekeeping_value'] = 0;
        }

        if($data['timekeeping_value'] != '0' && $data['timekeeping_value'] != ''){
            $data['timekeeping_value'] = number_format($data['timekeeping_value'], 1);
        }
        

        $data['creator'] = $staff_id;
        $data['additional_day'] = to_sql_date($data['additional_day']);
        $data['status'] = '0';
        $this->db->insert(db_prefix().'timesheets_additional_timesheet',$data);

        $insert_id = $this->db->insert_id();
        
        if($insert_id){
            $data_new = [];
            $data_new['rel_id'] = $insert_id;
            $data_new['rel_type'] = 'additional_timesheets';
            $data_new['addedfrom'] = $data['creator'];
            $success = $this->send_request_approve($data_new, $staffid);
            if($success){
                if($staffid == ''){
                    $this->send_mail($data_new);
                }
            }
            return $insert_id;
        }
        return false;
    }
        /**
     * get hour shift staff
     * @param  integer $staff_id 
     * @param  integer $date     
     * @return integer           
     */
    public function get_info_hour_shift_staff($staff_id, $date){
        $result = new stdClass();
        $result->woking_hour = 0;
        $result->lunch_break_hour = 0;

        $result->start_working = '';
        $result->end_working = '';
        $result->start_lunch_break = '';
        $result->end_lunch_break = '';

        $woking_hour = 0;
        $lunch_break_hour = 0;


        $data_shift_list = $this->get_shift_work_staff_by_date($staff_id, $date);
        foreach ($data_shift_list as $ss) {
            $data_shift_type = $this->get_shift_type($ss);
            if($data_shift_type){ 
                $woking_hour += $this->get_hour($data_shift_type->time_start_work, $data_shift_type->time_end_work);
                $lunch_break_hour += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
            }  
        }

        $result->woking_hour = abs($woking_hour - $lunch_break_hour);
        $result->lunch_break_hour = $lunch_break_hour;

        return $result;        
    }

    /**
     * Gets the file requisition.
     *
     * @param      int   $id      The identifier
     * @param      boolean  $rel_id  The relative identifier
     *
     * @return     object   The file requisition.
     */
    public function get_file_requisition($id, $rel_id = false)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get(db_prefix().'files')->row();
        
        if ($file && $rel_id) {
            if ($file->rel_id != $rel_id) {
                return $file;
            }
        }

        return $file;
    }

    /**
     * automatic insert timesheets
     *
     * @param      int   $staffid   The staffid
     * @param      integer  $time_in   The time in
     * @param      integer  $time_out  The time out
     *
     * @return     boolean
     */
    public function automatic_insert_timesheets($staffid, $time_in, $time_out){        
        $date_work = date('Y-m-d', strtotime($time_in));
        $work_time = $this->get_hour_shift_staff($staffid , $date_work);
        $affectedRows = 0;
        if($work_time > 0 && $work_time != ''){
            $list_shift = $this->get_shift_work_staff_by_date($staffid, $date_work);
            $d1 = $this->format_date_time($time_in);
            $d2 = $this->format_date_time($time_out); 

            $time_in = strtotime(date('H:i:s' ,strtotime($d1)));
            $time_out = strtotime(date('H:i:s' ,strtotime($d2)));

            $hour = 0;
            $late = 0;
            $early = 0;
            $lunch_time = 0;
            foreach ($list_shift as $shift) {
                $data_shift_type = $this->timesheets_model->get_shift_type($shift);

                $time_in_ = $time_in;
                $time_out_ = $time_out;
                if($data_shift_type){
                    $d1 = $this->format_date_time($data_shift_type->time_start_work);
                    $d2 = $this->format_date_time($data_shift_type->time_end_work);
                    $d3 = $this->format_date_time($data_shift_type->start_lunch_break_time);
                    $d4 = $this->format_date_time($data_shift_type->end_lunch_break_time);

                    $start_work = strtotime($data_shift_type->time_start_work);
                    $end_work = strtotime($data_shift_type->time_end_work);
                    $start_lunch_break = strtotime($data_shift_type->start_lunch_break_time);
                    $end_lunch_break = strtotime($data_shift_type->end_lunch_break_time); 

                    if($time_out < $start_work){
                        continue;
                    }

                    if($time_in < $start_work && $time_out > $start_work){
                        $time_in_ = $start_work;
                    }elseif($time_in > $start_work && $time_out > $start_work){
                        $late += round(abs($time_in - $start_work)/(60*60),2);
                    }

                    if($time_out > $end_work && $time_in < $end_work){
                        $time_out_ = $end_work;
                    }elseif($time_out < $end_work && $time_in < $end_work){
                        $early += round(abs($time_out - $end_work)/(60*60),2);
                    }

                    if($time_out_ >= $end_lunch_break){
                        $lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
                    }

                    $hour += round(abs($time_out_ - $time_in_)/(60*60),2);
                }  
            }
            $value = abs($hour - $lunch_time);

            $this->db->where('date_work', $date_work);
            $this->db->where('staff_id', $staffid);
            $this->db->where('type', 'L');
            $this->db->delete(db_prefix().'timesheets_timesheet');

            $this->db->where('date_work', $date_work);
            $this->db->where('staff_id', $staffid);
            $this->db->where('type', 'E');
            $this->db->delete(db_prefix().'timesheets_timesheet');

            $this->db->where('date_work', $date_work);
            $this->db->where('staff_id', $staffid);
            $this->db->where('type', 'W');
            $this->db->delete(db_prefix().'timesheets_timesheet');

            if($value > 0){
                $this->db->insert(db_prefix().'timesheets_timesheet',
                    [
                        'value' => $value, 
                        'date_work' => $date_work, 
                        'staff_id' => $staffid, 
                        'type' => 'W',
                        'add_from' => get_staff_user_id()
                    ]);

                $insert_id = $this->db->insert_id();

                if ($insert_id) {
                    $affectedRows++;
                }
            }

            if($late > 0){
                $this->db->insert(db_prefix().'timesheets_timesheet',
                    [
                        'value' => $late, 
                        'date_work' => $date_work, 
                        'staff_id' => $staffid, 
                        'type' => 'L',
                        'add_from' => get_staff_user_id()
                    ]);

                $insert_id = $this->db->insert_id();

                if ($insert_id) {
                    $affectedRows++;
                }
            }

            if($early > 0){
                $this->db->insert(db_prefix().'timesheets_timesheet',
                    [
                        'value' => $early, 
                        'date_work' => $date_work, 
                        'staff_id' => $staffid, 
                        'type' => 'E',
                        'add_from' => get_staff_user_id()
                    ]);

                $insert_id = $this->db->insert_id();

                if ($insert_id) {
                    $affectedRows++;
                }
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
     * Adds an update timesheet.
     *
     * @param      object   $data           The data
     * @param      boolean  $is_timesheets  Indicates if timesheets
     *
     * @return     integer
     */
    public function add_update_timesheet($data, $is_timesheets = false){
        $results = 0;

        foreach ($data as $row) {
            foreach($row as $key => $val){
                if($key != 'staff_id' && $key != 'staff_name'){
                    $ts = explode("; ", $val);
                    if($is_timesheets === true){
                        $this->db->where('staff_id', $row['staff_id']);
                        $this->db->where('date_work', $key);
                        $this->db->delete(db_prefix().'timesheets_timesheet');
                    }
                    foreach($ts as $ex){
                        $value = explode(':', $ex);
                        $this->db->where('staff_id', $row['staff_id']);
                        $this->db->where('date_work', $key);
                        $this->db->where('type', strtoupper($value[0]));
                        $isset = $this->db->get(db_prefix().'timesheets_timesheet')->row();

                        if(isset($isset)){
                            $this->db->where('staff_id', $row['staff_id']);
                            $this->db->where('date_work', $key);
                            $this->db->where('type', strtoupper($value[0]));
                            $this->db->update(db_prefix().'timesheets_timesheet',[
                                'value' => $value[1],
                                'add_from' => get_staff_user_id(),
                                'type' => strtoupper($value[0]),
                            ]);
                            if($this->db->affected_rows() > 0){
                               $results++;
                            }                           
                        }else{
                            if($val != ''){
                                $this->db->insert(db_prefix().'timesheets_timesheet',[
                                    'staff_id' => $row['staff_id'],
                                    'date_work' => $key,
                                    'value' => $value[1],
                                    'add_from' => get_staff_user_id(),
                                    'type' => strtoupper($value[0]),
                                ]);
                                $insert_id = $this->db->insert_id();
                                if($insert_id){
                                    $results++;
                                }
                            }
                            
                        }

                        if($val == ''){
                            $this->db->where('staff_id', $row['staff_id']);
                            $this->db->where('date_work', $key);
                            $this->db->delete(db_prefix().'timesheets_timesheet');
                            if($this->db->affected_rows() > 0){
                                   $results++;
                            }  
                        }

                    }
                }
            }
        }

        return $results;
    }

    /**
     * latch timesheet
     *
     * @param      string   $month  The month
     *
     * @return     boolean
     */
    public function latch_timesheet($month){
        if($month != ''){
            $this->db->insert(db_prefix().'timesheets_latch_timesheet', [
                    'month_latch' => $month,
                ]);
            $insert_id = $this->db->insert_id();    

            if($insert_id){
                $m = date('m', strtotime('01-'.$month));
                $y = date('Y', strtotime('01-'.$month));
                $this->db->where('month(date_work) = '.$m.' and year(date_work) = '.$y);
                $this->db->update(db_prefix().'timesheets_timesheet', ['latch' => 1]);

                return true;
            }else{
                return false;
            }
        }

        return false;

    }

    /**
     * Gets the taskstimers.
     *
     * @param      int  $task_id   The task identifier
     * @param      int  $staff_id  The staff identifier
     *
     * @return     array  The taskstimers.
     */
    public function get_taskstimers($task_id, $staff_id){
        $this->db->where('staff_id', $staff_id);
        $this->db->where('task_id', $task_id);
        $this->db->order_by('id', 'desc');
        return $this->db->get(db_prefix() . 'taskstimers')->result_array();
    }


    public function get_data_insert_timesheets($staffid, $time_in, $time_out){
        
        $date_work = date('Y-m-d', strtotime($time_in));
        $work_time = $this->get_hour_shift_staff($staffid , $date_work);
        $affectedRows = 0;

        $hour = 0;
        $late = 0;
        $early = 0;
        $lunch_time = 0;
        if($work_time > 0 && $work_time != ''){
            $list_shift = $this->get_shift_work_staff_by_date($staffid, $date_work);
            $d1 = $this->format_date_time($time_in);
            $d2 = $this->format_date_time($time_out); 
            $time_in = strtotime(date('H:i:s' ,strtotime($d1)));
            $time_out = strtotime(date('H:i:s' ,strtotime($d2)));

            foreach ($list_shift as $shift) {
                $data_shift_type = $this->timesheets_model->get_shift_type($shift);
                $time_in_ = $time_in;
                $time_out_ = $time_out;
                if($data_shift_type){

                    $start_work = strtotime($data_shift_type->time_start_work);
                    $end_work = strtotime($data_shift_type->time_end_work);
                    $start_lunch_break = strtotime($data_shift_type->start_lunch_break_time);
                    $end_lunch_break = strtotime($data_shift_type->end_lunch_break_time);

                    
                    if($time_out < $start_work){
                        continue;
                    }

                    if($time_in < $start_work && $time_out > $start_work){
                        $time_in_ = $start_work;
                    }elseif($time_in > $start_work && $time_out > $start_work){
                        $late += round(abs($time_in - $start_work)/(60*60),2);
                    }

                    if($time_out > $end_work && $time_in < $end_work){
                        $time_out_ = $end_work;
                    }elseif($time_out < $end_work && $time_in < $end_work){
                        $early += round(abs($time_out - $end_work)/(60*60),2);
                    }

                    if($time_out_ >= $end_lunch_break){
                        $lunch_time += $this->get_hour($data_shift_type->start_lunch_break_time, $data_shift_type->end_lunch_break_time);
                    }
                    $hour += round(abs($time_out_ - $time_in_)/(60*60),2);
                }  
            }

        }
                      

        $value = abs($hour - $lunch_time);
        $data = []; 
        $data['work'] = $value;
        $data['early'] = $early;
        $data['late'] = $late;
        return $data;
    }

    public function import_timesheets($data){
        foreach ($data as $key => $value) {
            $test = $this->check_ts($value['staffid'],date('Y-m-d', strtotime($value['time_in'])));

            if($test->check_in != 0){
                $this->db->where('id', $test->check_in);
                $this->db->update(db_prefix().'check_in_out',['date' => $value['time_in']]);
            }else{
                $this->db->insert(db_prefix().'check_in_out',
                    ['staff_id' => $value['staffid'],
                    'date' => $value['time_in'], 
                    'type_check' => 1]);
            }

            if($test->check_out != 0){
                $this->db->where('id', $test->check_out);
                $this->db->update(db_prefix().'check_in_out',['date' => $value['time_out']]);
            }else{
                $this->db->insert(db_prefix().'check_in_out',
                    ['staff_id' => $value['staffid'],
                    'date' => $value['time_out'], 
                    'type_check' => 2]);
            }

            $this->automatic_insert_timesheets($value['staffid'], $value['time_in'], $value['time_out']);
        }

        return true;
    }

/**
     * delete additional timesheets
     * @param  integer $id 
     * @return boolean     
     */
    public function delete_additional_timesheets($id){
       
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'timesheets_additional_timesheet');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

     
    /**
     * delete timesheets attchement file for any
     *
     * @param      <type>   $attachment_id  The attachment identifier
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function delete_timesheets_attachment_file($attachment_id)
    {
        $deleted    = false;
        $attachment = $this->get_timesheets_attachments_delete($attachment_id);
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(TIMESHEETS_MODULE_UPLOAD_FOLDER .'/requisition_leave/' .$attachment->rel_id.'/'.$attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Attachment Deleted [Requisition Leave ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(TIMESHEETS_MODULE_UPLOAD_FOLDER .'/requisition_leave/' .$attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(TIMESHEETS_MODULE_UPLOAD_FOLDER .'/requisition_leave/' .$attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(TIMESHEETS_MODULE_UPLOAD_FOLDER .'/requisition_leave/' .$attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Gets the timesheets attachments delete.
     *
     * @param      int  $id     The identifier
     *
     * @return     object  The timesheets attachments delete.
     */
    public function get_timesheets_attachments_delete($id){

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'files')->row();
        }else{
            return [];
        }
    }

    /**
     * get list check in/out
     * @param  $date
     * @param  string $staffid 
     * @return 
     */
    public function get_list_check_in_out($date, $staffid = ''){
        if ($staffid !='') {
            $this->db->where('staff_id', $staffid);
        }
        $this->db->where('DATE_FORMAT(date, "%Y-%m-%d") = "'.$date.'"');

        return $this->db->get(db_prefix() . 'check_in_out')->result_array();
    }
    public function get_ts_staff_by_date($staff_id, $date_work){
        return $this->db->query('select * from '.db_prefix().'timesheets_timesheet where staff_id = '.$staff_id.' and date_work = \''.$date_work.'\'')->result_array();
    }
    public function merge_ts($string, $max_hour){
        if($string != ''){
            $array = explode('; ', $string);
            $list_type = [];
            foreach ($array as $key => $value) {
                $split = explode(':', $value);
                if(isset($split[0]) && isset($split[1])){
                    if(count($list_type) == 0){
                        array_push($list_type, $split[0]);
                    }
                    elseif(!in_array($split[0], $list_type)){
                        array_push($list_type, $split[0]);
                    }
                }
            }
            $array_result = [];
            foreach ($list_type as $key => $type) {
                $total = 0;
                foreach ($array as $key => $value) {
                    $split = explode(':', $value);
                    if(isset($split[0]) && isset($split[1])){
                        if(str_replace(' ','',$split[0]) == str_replace(' ','',$type)){
                            $total += $split[1];
                        }                        
                    }
                }
                if($total > $max_hour){
                    $total = $max_hour;
                }
                $array_result[] = $type.':'.$total;
            }
            
            if(count($array_result) > 0){
                return implode('; ',$array_result);
            }
            else{
                return '';
            }
        }
        else{
            return '';
        }
    }
        /**
     * Get staff member/s
     * @param  mixed $id Optional - staff id
     * @param  mixed $where where in query
     * @return mixed if id is passed return object else array
     */
    public function get_staff_list($where = '')
    {
        return $this->db->query('select * from '.db_prefix() . 'staff '.$where)->result_array();
    }
        /**
     * Gets the go bussiness advance payment.
     *
     * @param      <type>  $request_leave  The request leave
     */
    public function get_go_bussiness_advance_payment($request_leave){
        $this->db->where('requisition_leave', $request_leave);
        return $this->db->get(db_prefix().'timesheets_go_bussiness_advance_payment')->result_array();
    }

    /**
     * advance payment update description
     * @param  $id   integer
     * @param  $data array
     * @return boolean
     */
    public function advance_payment_update($id, $data){

        $data['amount_received'] = timesheets_reformat_currency_asset($data['amount_received']);
        $data['received_date'] = to_sql_date($data['received_date']);

        $this->db->where('id',$id);
        $this->db->update(db_prefix().'timesheets_requisition_leave',$data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
}

