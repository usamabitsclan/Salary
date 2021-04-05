<?php 
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * timesheets
 */
class timesheets extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('timesheets_model');
        $this->load->model('departments_model');
        require_once(module_dir_path(TIMESHEETS_MODULE_NAME).'/third_party/excel/PHPExcel.php');
    }

    /* List all announcements */
    public function index()
    {
        if (!has_permission('timesheets_dashboard', '', 'view')) {
            access_denied('timesheets');
        }

        $data['google_ids_calendars']  = $this->misc_model->get_google_calendar_ids();

        $data['title']                 = _l('timesheets');
        $this->load->view('timesheets_dashboard', $data);


    }

    /**
     * setting
     * @return 
     */
    public function setting()
    {
        $this->load->model('staff_model');
        $this->load->model('roles_model');        
        $this->load->model('contracts_model');        

        $data['group'] = $this->input->get('group');

        $data['title']                 = _l('setting');
        $data['tab'][] = 'manage_leave';
        $data['tab'][] = 'manage_dayoff';
        $data['tab'][] = 'approval_process';
        $data['tab'][] = 'timekeeping_settings';
        if($data['group'] == ''){
            $data['group'] = 'contract_type';
        }elseif ($data['group'] == 'manage_dayoff') {
            $data['holiday'] = $this->timesheets_model->get_break_dates();
        }elseif ($data['group'] == 'overtime_setting') {
            $data['overtime_setting'] = $this->timesheets_model->get_overtime_setting();
        }elseif ($data['group'] == 'shift') {
            $data['shift'] = $this->timesheets_model->get_shift_sc();
        }
        $data['tabs']['view'] = 'includes/'.$data['group'];
        $data['month'] = $this->timesheets_model->get_month();

        $data['staff'] = $this->staff_model->get();
        $data['department'] = $this->departments_model->get();

        $data['role'] = $this->roles_model->get();

        if($data['group'] == 'approval_process'){
                 if ($this->input->post()) {
                $data                = $this->input->post();
                $id = $data['approval_setting_id'];
                unset($data['approval_setting_id']);
                if ($id == '') {
                    if (!has_permission('staffmanage_approval', '', 'create')) {
                        access_denied('approval_process');
                    }
                    $id = $this->timesheets_model->add_approval_process($data);
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('approval_process')));
                    }
                } else {
                    if (!has_permission('staffmanage_approval', '', 'edit')) {
                        access_denied('approval_process');
                    }
                    $success = $this->timesheets_model->update_approval_process($id, $data);
                    if ($success) {
                        set_alert('success', _l('updated_successfully', _l('approval_process')));
                    }
                }
            }
            $data['approval_setting'] = $this->timesheets_model->get_approval_process();      
            $data['title']                 = _l('approval_process');
            $data['staffs'] = $this->staff_model->get(); 
        }
        if($data['group'] == 'manage_leave'){
            $new_array_obj = [];
            foreach ($data['staff'] as $key => $value) {
                $department_name = '';
                $data_department = $this->departments_model->get_staff_departments($value['staffid']);
                if($data_department){
                    $department_name = $data_department[0]['name'];
                }

                $role_name = '';
                if($value['role']!=''){
                    $data_role = $this->timesheets_model->get_role($value['role']);
                    if(isset($data_role)){
                        if($data_role){
                            if(isset($data_role->name)){
                                $role_name = $data_role->name;
                            }
                        }
                    }
                }
                $day = 0;
                $data_leave = $this->timesheets_model->get_day_off($value['staffid']);
                if($data_leave){
                    if($data_leave->total != ''){
                        $day = $data_leave->remain;
                    }
                }
                array_push($new_array_obj, array('staffid' => $value['staffid'], 'staff'=>  $value['firstname'].' '.$value['lastname'], 'department' => $department_name, 'role' => $role_name,'maximum_leave_of_the_year' => $day));
            }
            $data['leave_of_the_year'] = json_encode($new_array_obj);
        }
        $this->load->view('manage_setting', $data);
    }
   
   /**
    * leave 
    * @return view
    */
    public function leave()
    {
        $data['title']         = _l('manage_leave');

        $data['positions'] = $this->timesheets_model->get_job_position();
        $data['workplace'] = $this->timesheets_model->get_workplace();
        $data['contract_type'] = $this->timesheets_model->get_contracttype();
        $data['staff'] = $this->staff_model->get();
        $data['allowance_type'] = $this->timesheets_model->get_allowance_type();
        $data['salary_form'] = $this->timesheets_model->get_salary_form();

        $this->load->view('timesheets/manage_leave',$data);
    }

    /**
     * table leave
     * @return view
     */
    public function table_leave(){

        $this->app->get_table_data(module_views_path('timesheets', 'table_leave'));
    }

    /**
     * timesheets file 
     * @param  int $id
     * @param  int $rel_id
     * @return view
     */
    public function timesheets_file($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->timesheets_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('timesheets/includes/_file', $data);
    }

    /**
     * get staff role
     * @return type [description]
     */
    public function get_staff_role(){
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {

            $id = $this->input->post('id');
            $name_object = $this->db->query('select r.name from '.db_prefix().'staff as s join '.db_prefix().'roles as r on s.role = r.roleid where s.staffid = ' .$id)->row();
            }
        }
        if($name_object){
            echo json_encode([
                'name'  => $name_object->name,
            ]);
        }
    
    }

    /**
     * timekeeping
     * @return view
     */
    public function timekeeping(){
        $this->load->model('staff_model');
        $data['title']                 = _l('timesheets');
        
        $days_in_month = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));

        $month      = date('m');
        $month_year = date('Y');

        $data['check_latch_timesheet'] = $this->timesheets_model->check_latch_timesheet(date('m-Y'));
        
        $data['departments'] = $this->departments_model->get();
        $data['staffs_li'] = $this->staff_model->get();
        $data['roles']         = $this->roles_model->get();
        $data['positions'] = $this->roles_model->get();

        $data['day_by_month_tk'] = [];
        $data['day_by_month_tk'][] = _l('staff_id');
        $data['day_by_month_tk'][] = _l('staff');

        $data['set_col_tk'] = [];
        $data['set_col_tk'][] = ['data' => _l('staff_id'), 'type' => 'text'];
        $data['set_col_tk'][] = ['data' => _l('staff'), 'type' => 'text','readOnly' => true,'width' => 200];

        for ($d = 1; $d <= $days_in_month; $d++) {
            $time = mktime(12, 0, 0, $month, $d, $month_year);
            if (date('m', $time) == $month) {
                array_push($data['day_by_month_tk'], date('D d', $time));
                array_push($data['set_col_tk'],[ 'data' => date('D d', $time), 'type' => 'text']);
            }
        }

        $data['day_by_month_tk'] = json_encode($data['day_by_month_tk']);
        $data_map = [];
        $data_timekeeping_form = get_timesheets_option('timekeeping_form');
        $data_timekeeping_manually_role = get_timesheets_option('timekeeping_manually_role');
        $data['data_timekeeping_form'] = $data_timekeeping_form;
        $data['staff_row_tk'] = [];
        $staffs = $this->timesheets_model->get_staff_timekeeping_applicable_object();
        $data['staffs_setting'] = $this->staff_model->get();
        $data['staffs'] = $staffs;

        
        if($data_timekeeping_form == 'timekeeping_task' && $data['check_latch_timesheet'] == false){
                foreach($staffs as $s){
                    $ts_date = '';
                    $ts_ts = '';
                    $result_tb = [];
                    $from_date = date('Y-m-01');
                    $to_date = date('Y-m-t');
                    $staffsTasksWhere = [];
                    if($from_date != '' && $to_date != ''){
                        $staffsTasksWhere = 'IF(duedate IS NOT NULL,((startdate <= "'.$from_date.'" and duedate >= "'.$from_date.'") or (startdate <= "'.$to_date.'" and duedate >= "'.$to_date.'") or (startdate > "'.$to_date.'" and duedate < "'.$from_date.'")), IF(datefinished IS NOT NULL,IF(status = 5 ,((startdate <= "'.$from_date.'" and date_format(datefinished, "%Y-%m-%d") >= "'.$from_date.'") or (startdate <= "'.$to_date.'" and date_format(datefinished, "%Y-%m-%d") >= "'.$to_date.'") or (startdate > "'.$to_date.'" and date_format(datefinished, "%Y-%m-%d") < "'.$from_date.'")), (startdate <= "'.$from_date.'" or (startdate > "'.$from_date.'" and startdate <= "'.$to_date.'"))),(startdate <= "'.$from_date.'" or (startdate > "'.$from_date.'" and startdate <= "'.$to_date.'"))))';
                }

                $staff_task = $this->tasks_model->get_tasks_by_staff_id($s['staffid'], $staffsTasksWhere);
                $list_in_out = [];
                foreach ($staff_task as $key_task => $task) {
                    $list_taskstimers = $this->timesheets_model->get_taskstimers($task['id'], $s['staffid']);
                    foreach ($list_taskstimers as $taskstimers) {
                        $list_date = $this->timesheets_model->get_list_date(date('Y-m-d',$taskstimers['start_time']), date('Y-m-d',$taskstimers['end_time']));
                        foreach ($list_date as $curent_date) {
                            $start_work_time = "";
                            $end_work_time = "";
                            $data_shift_list = $this->timesheets_model->get_shift_work_staff_by_date($s['staffid'], $curent_date);

                            foreach ($data_shift_list as $ss) {
                                $data_shift_type = $this->timesheets_model->get_shift_type($ss); 
                                if($start_work_time == "" || strtotime($start_work_time) > strtotime($curent_date.' '.$data_shift_type->time_start_work.':00')){
                                    $start_work_time = $curent_date.' '.$data_shift_type->time_start_work.':00';
                                }
                                if($end_work_time == "" || strtotime($end_work_time) < strtotime($curent_date.' '.$data_shift_type->time_end_work.':00')){
                                    $end_work_time = $curent_date.' '.$data_shift_type->time_end_work.':00';
                                }
                            } 
                            if(strtotime($start_work_time) < strtotime($curent_date.' '.date('H:i:s',$taskstimers['start_time']))){
                                $start_work_time = $curent_date.' '.date('H:i:s',$taskstimers['start_time']);
                            }
                            if(strtotime($end_work_time) > strtotime($curent_date.' '.date('H:i:s',$taskstimers['end_time'])) && strtotime(date('Y-m-d',$taskstimers['end_time'])) == strtotime($curent_date)){
                                $end_work_time = $curent_date.' '.date('H:i:s',$taskstimers['end_time']);
                            }

                            if(strtotime($from_date) <= strtotime(date('Y-m-d',strtotime($start_work_time))) && strtotime($to_date) >= strtotime(date('Y-m-d',strtotime($start_work_time)))){
                                if(isset($list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'])){
                                    if(strtotime($list_in_out[date('Y-m-d',strtotime($start_work_time))]['in']) > strtotime($start_work_time)){
                                        $list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'] = $start_work_time;
                                    }
                                }else{
                                    $list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'] = $start_work_time;
                                }



                                if(isset($list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'])){
                                    if(strtotime($list_in_out[date('Y-m-d',strtotime($start_work_time))]['out']) < strtotime($start_work_time)){
                                        $list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'] = $start_work_time;
                                    }
                                }else{
                                    $list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'] = $start_work_time;
                                }
                            }
                            if(strtotime($from_date) <= strtotime(date('Y-m-d',strtotime($end_work_time))) && strtotime($to_date) >= strtotime(date('Y-m-d',strtotime($end_work_time)))){
                                if(isset($list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'])){
                                    if(strtotime($list_in_out[date('Y-m-d',strtotime($end_work_time))]['in']) >strtotime($end_work_time)){
                                        $list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'] = $end_work_time;
                                    }
                                }else{
                                    $list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'] = $end_work_time;
                                }

                                if(isset($list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'])){
                                    if(strtotime($list_in_out[date('Y-m-d',strtotime($end_work_time))]['out']) <strtotime($end_work_time)){
                                        $list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'] = $end_work_time;
                                    }
                                }else{
                                    $list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'] = $end_work_time;
                                }
                            }
                        }

                    }
                }
                foreach ($list_in_out as $date_ => $in_out) {
                    $vl = $this->timesheets_model->get_data_insert_timesheets($s['staffid'], $in_out['in'], $in_out['out']);
                    if(!isset($data_map[$s['staffid']][$date_]['ts'])){
                        $data_map[$s['staffid']][$date_]['date'] = date('D d', strtotime($date_));
                        $data_map[$s['staffid']][$date_]['ts'] = '';
                    }
                    if($vl['late'] > 0){
                        $data_map[$s['staffid']][$date_]['ts'] .= 'L:'.$vl['late'].'; ';
                    }
                    if($vl['early'] > 0){
                        $data_map[$s['staffid']][$date_]['ts'] .= 'E:'.$vl['early'].'; ';
                    }
                    if($vl['work'] > 0){
                        $data_map[$s['staffid']][$date_]['ts'] .= 'W:'.$vl['work'].'; ';
                    }

                    $data_map[$s['staffid']][$date_]['ts'] = rtrim($data_map[$s['staffid']][$date_]['ts'], '; ');
                }

                if(isset($data_map[$s['staffid']])){
                    foreach ($data_map[$s['staffid']] as $key => $value) {
                        $ts_date = $data_map[$s['staffid']][$key]['date'];
                        $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                        $result_tb[] = [$ts_date => $ts_ts];
                    }
                }

                $dt_ts = [];
                $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                $note = [];
                $list_dtts = [];
                foreach ($result_tb as $key => $rs) {
                    foreach ($rs as $day => $val) {
                       $list_dtts[$day] = $val;
                    }
                }

                $list_date = $this->timesheets_model->get_list_date(date('Y-m-01'), date('Y-m-t'));
                foreach ($list_date as $key => $value) {
                    $date_s = date('D d', strtotime($value));
                    $max_hour = $this->timesheets_model->get_hour_shift_staff($s['staffid'],$value);

                    $ts_db = '';
                    $data_timesheet = $this->timesheets_model->get_ts_staff_by_date($s['staffid'],$value);
                    if($data_timesheet){
                        foreach ($data_timesheet as $key => $ts) {
                            $ts_db .= $ts["type"].':'.$ts["value"].'; ';
                        }
                    }
                    if($ts_db != ''){
                        $ts_db = rtrim($ts_db,'; ');
                    }
                    if($max_hour > 0){
                        
                        $ts_lack = '';
                        if(isset($list_dtts[$date_s])){
                            $ts_lack = $list_dtts[$date_s].'; ';
                        }
                        $total_lack = $ts_lack.''.$ts_db;
                        if($total_lack){
                            $total_lack = rtrim($total_lack, '; ');
                        }
                        $data_result = $this->timesheets_model->merge_ts($total_lack, $max_hour);
                        $dt_ts[$date_s] = $data_result;
                    }
                }
                array_push($data['staff_row_tk'], $dt_ts);
            }                        
        }
        elseif($data_timekeeping_form == 'timekeeping_manually' && $data['check_latch_timesheet'] == false){

            $data_ts = $this->timesheets_model->get_timesheets_ts_by_month(date('m'), date('Y'));
            foreach($data_ts as $ts){
                $staff_info = array();
                $staff_info['date'] = date('D d', strtotime($ts['date_work']));  
                $ts_type = $this->timesheets_model->get_ts_by_date_and_staff($ts['date_work'],$ts['staff_id']);

                if(count($ts_type) <= 1){
                    if($ts['value'] > 0){
                        $staff_info['ts'] = $ts['type'].':'.$ts['value'];
                    }else{
                        $staff_info['ts'] = '';
                    }
                }else{
                    $str = '';
                    foreach($ts_type as $tp){
                        if($tp['value'] > 0){
                            if($tp['type'] == 'HO' || $tp['type'] == 'M'){
                                if($str == ''){
                                    $str .= $tp['type'];
                                }else{
                                    $str .= "; ".$tp['type'];
                                }
                            }else{
                                if($str == ''){
                                    $str .= $tp['type'].':'.round($tp['value'], 2);
                                }else{
                                    $str .= "; ".$tp['type'].':'.round($tp['value'], 2);
                                }
                            }
                        }                     
                    }
                    $staff_info['ts'] = $str;
                }         
                if(!isset($data_map[$ts['staff_id']])){
                    $data_map[$ts['staff_id']] = array();
                }
                $data_map[$ts['staff_id']][$staff_info['date']] = $staff_info;
            }
            foreach($staffs as $s){
                $ts_date = '';
                $ts_ts = '';
                $result_tb = [];
                if(isset($data_map[$s['staffid']])){
                    foreach ($data_map[$s['staffid']] as $key => $value) {
                        $ts_date = $data_map[$s['staffid']][$key]['date'];
                        $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                        $result_tb[] = [$ts_date => $ts_ts];
                    }
                }

                $dt_ts = [];
                $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                $note = [];
                $list_dtts = [];
                foreach ($result_tb as $key => $rs) {
                    foreach ($rs as $day => $val) {
                       $list_dtts[$day] = $val;
                    }
                }
                $list_date = $this->timesheets_model->get_list_date(date('Y-m-01'), date('Y-m-t'));
                foreach ($list_date as $key => $value) {
                    $date_s = date('D d', strtotime($value));
                    $max_hour = $this->timesheets_model->get_hour_shift_staff($s['staffid'],$value);

                    if($max_hour > 0){
                        $ts_lack = '';
                        if(isset($list_dtts[$date_s])){
                            $ts_lack = $list_dtts[$date_s].'; ';
                        }
                        $total_lack = $ts_lack;
                        if($total_lack){
                            $total_lack = rtrim($total_lack, '; ');
                        }
                        $data_result = $this->timesheets_model->merge_ts($total_lack, $max_hour);
                        $dt_ts[$date_s] = $data_result;
                    }
                 }
               array_push($data['staff_row_tk'], $dt_ts);
            }
        }else{
            $data_ts = $this->timesheets_model->get_timesheets_ts_by_month(date('m'), date('Y'));
            foreach($data_ts as $ts){
                $staff_info = array();
                $staff_info['date'] = date('D d', strtotime($ts['date_work']));                
                $ts_type = $this->timesheets_model->get_ts_by_date_and_staff($ts['date_work'],$ts['staff_id']);
                if(count($ts_type) <= 1){
                    if($ts['value'] > 0){
                        $staff_info['ts'] = $ts['type'].':'.$ts['value'];
                    }else{
                        $staff_info['ts'] = '';
                    }
                }else{
                    $str = '';
                    foreach($ts_type as $tp){
                        if($tp['value'] > 0){
                            if($tp['type'] == 'HO' || $tp['type'] == 'M'){
                                if($str == ''){
                                    $str .= $tp['type'];
                                }else{
                                    $str .= "; ".$tp['type'];
                                }
                            }else{
                                if($str == ''){
                                    $str .= $tp['type'].':'.round($tp['value'], 2);
                                }else{
                                    $str .= "; ".$tp['type'].':'.round($tp['value'], 2);
                                }
                            }
                        }                     
                    }
                    $staff_info['ts'] = $str;
                }          
                
                if(!isset($data_map[$ts['staff_id']])){
                    $data_map[$ts['staff_id']] = array();
                }
                $data_map[$ts['staff_id']][$staff_info['date']] = $staff_info;
            }

            foreach($staffs as $s){
                $ts_date = '';
                $ts_ts = '';
                $result_tb = [];
                if(isset($data_map[$s['staffid']])){
                    foreach ($data_map[$s['staffid']] as $key => $value) {
                        $ts_date = $data_map[$s['staffid']][$key]['date'];
                        $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                        $result_tb[] = [$ts_date => $ts_ts];
                    }
                }

                $dt_ts = [];
                $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                $note = [];
                foreach ($result_tb as $key => $rs) {
                    foreach ($rs as $day => $val) {
                       $dt_ts[$day] = $val;

                    }
                }
                array_push($data['staff_row_tk'], $dt_ts);
            }
        }
        $data_lack = [];
        
        $data['data_lack'] = $data_lack;
        $data['set_col_tk'] = json_encode($data['set_col_tk']);

        $this->load->view('timekeeping/manage_timekeeping', $data);
    }
/**
 * add or update day off 
 */
public function day_off(){
    if($this->input->post()){
        $data = $this->input->post();
        if (!$this->input->post('id')) {
            $add = $this->timesheets_model->add_day_off($data); 
            if($add > 0){
                $message = _l('added_successfully', _l('day_off'));
                set_alert('success',$message);
            }
            redirect(admin_url('timesheets/setting?group=manage_dayoff'));
        }else{
            $id = $data['id'];
            unset($data['id']);
            $success = $this->timesheets_model->update_day_off($data,$id);
            if($success == true){
                $message = _l('updated_successfully', _l('day_off'));
                set_alert('success', $message);
            }
            redirect(admin_url('timesheets/setting?group=manage_dayoff'));
        }

    }
}
    /**
     * delete day off
     * @param  int $id      
     */
    public function delete_day_off($id){
        if (!$id) {
            redirect(admin_url('timesheets/setting?group=manage_dayoff'));
        }
        $response = $this->timesheets_model->delete_day_off($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced').' '. _l('day_off'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted').' '._l('day_off'));
        } else {
            set_alert('warning', _l('problem_deleting').' '. _l('day_off'));
        }
        redirect(admin_url('timesheets/setting?group=manage_dayoff'));
    }
    /**
     * add or edit shifts
     */
    public function shifts(){
        if($this->input->post()){
            $data = $this->input->post();
            if ($data['id'] == '') {
                $add = $this->timesheets_model->add_work_shift($data); 
                if($add > 0){
                    $message = _l('added_successfully', _l('shift'));
                    set_alert('success',$message);
                }
                redirect(admin_url('timesheets/shift_management'));
            }else{
                $success = $this->timesheets_model->update_work_shift($data);
                if($success == true){
                    $message = _l('updated_successfully', _l('shift'));
                    set_alert('success', $message);
                }
                redirect(admin_url('timesheets/shift_management'));
            }
        }   
    }
    /**
     * get_data_edit_shift
     * @param int $id 
     */
    public function get_data_edit_shift($id){
        $shift_handson = $this->timesheets_model->get_data_edit_shift($id);
        $result = [];
        $node = [];
        foreach ($shift_handson as $key => $value) {
            foreach ($value as $col => $val) {
                if($col == 'detail'){
                    if($key == 0){
                        $node[_l($col)] =  _l('time_start_work');
                    }elseif ($key == 1) {
                       $node[_l($col)] =  _l('time_end_work');
                    }elseif($key == 2){
                        $node[_l($col)] =  _l('start_lunch_break_time');
                    }elseif($key == 3){
                        $node[_l($col)] =  _l('end_lunch_break_time');
                    }elseif($key == 4){
                        $node[_l($col)] =  _l('late_latency_allowed');
                    }
                }else{

                    $node[_l($col)] = $val;
                }
            }
            $result[] = $node; 
        }
        echo json_encode([
            'handson' => $result,
        ]);
    }
    /**
     * delete shift
     * @param int $id    
     */
    public function delete_shift($id){
        if (!$id) {
            redirect(admin_url('timesheets/shift_management'));
        }
        $response = $this->timesheets_model->delete_shift($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced').' '. _l('shift'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted').' '._l('shift'));
        } else {
            set_alert('warning', _l('problem_deleting').' '. _l('shift'));
        }
        redirect(admin_url('timesheets/shift_management'));
    }
    /**
     * manage timesheets
     */
    public function manage_timesheets(){
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data)){
                
                if($data['latch'] == 1){
                    if(isset($data['month']) && $data['month'] != ""){
                        $data_month = explode("-", $data['month']);
                        if(strlen($data['month'][0]) == 4){
                            $month_latch = $data_month[1].'-'.$data_month[0];
                        }else{
                            $month_latch = $data_month[0].'-'.$data_month[1];
                        }
                    }else{
                        $month_latch = date("m-Y");
                    }

                    $day_month = [];
                    $day_by_month_tk = [];
                    $day_month_tk[] = 'staff_id';
                    $day_month_tk[] = 'staff_name';


                    $month      = explode('-',$data['month'])[0];
                    $month_year = explode('-',$data['month'])[1];
                   
                    for ($d = 1; $d <= 31; $d++) {
                        $time = mktime(12, 0, 0, $month, $d, $month_year);
                        if (date('m', $time) == $month) {
                            array_push($day_month, date('Y-m-d', $time));
                            array_push($day_month_tk, date('Y-m-d', $time));
                        }
                    }
                    $data['time_sheet'] = json_decode($data['time_sheet']);
                    $ts_val = [];
                    foreach ($data['time_sheet'] as $key => $value) {
                        $ts_val[] = array_combine($day_month_tk, $value);
                    }
                    
                    unset($data['time_sheet']);

                    $add = $this->timesheets_model->add_update_timesheet($ts_val, true);

                    $success = $this->timesheets_model->latch_timesheet($month_latch);

                    if($success){
                        set_alert('success',_l('timekeeping_latch_successful'));
                    }else{
                        set_alert('warning',_l('timekeeping_latch_false'));
                    }
                    redirect(admin_url('timesheets/timekeeping?group=timesheets'));
                }elseif($data['unlatch'] == 1){
                    if(isset($data['month']) && $data['month'] != ""){
                        $data['month'] = explode("-", $data['month']);
                        if(strlen($data['month'][0]) == 4){
                            $month = $data['month'][1].'-'.$data['month'][0];
                        }else{
                            $month = $data['month'][0].'-'.$data['month'][1];
                        }
                    }else{
                        $month = date("m-Y");
                    }
                    $success = $this->timesheets_model->unlatch_timesheet($month);

                    if($success){
                        set_alert('success',_l('timekeeping_unlatch_successful'));
                    }else{
                        set_alert('warning',_l('timekeeping_unlatch_false'));
                    }
                    redirect(admin_url('timesheets/timekeeping?group=timesheets'));
                }else{
                    $day_month = [];
                    $day_by_month_tk = [];
                    $day_month_tk[] = 'staff_id';
                    $day_month_tk[] = 'staff_name';


                    $month      = explode('-',$data['month'])[0];
                    $month_year = explode('-',$data['month'])[1];
                   
                    for ($d = 1; $d <= 31; $d++) {
                        $time = mktime(12, 0, 0, $month, $d, $month_year);
                        if (date('m', $time) == $month) {
                            array_push($day_month, date('Y-m-d', $time));
                            array_push($day_month_tk, date('Y-m-d', $time));
                        }
                    }
                    $data['time_sheet'] = json_decode($data['time_sheet']);
                    $ts_val = [];
                    foreach ($data['time_sheet'] as $key => $value) {
                        $ts_val[] = array_combine($day_month_tk, $value);
                    }
                    unset($data['time_sheet']);

                    $add = $this->timesheets_model->add_update_timesheet($ts_val, true);
                    if($add > 0){
                        set_alert('success',_l('timekeeping').' '._l('successfully'));
                    }else{
                        set_alert('warning',_l('alert_ts'));
                    }
                    redirect(admin_url('timesheets/timekeeping?group=timesheets'));
                }
            }
        }
    }
    
    /**
     * approval status
     * @return json 
     */
    public function approval_status(){
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $data = $this->input->post();
                
                $success = $this->timesheets_model->update_approval_status($data);
                if ($success) {
                    $message = _l('success');
                    echo json_encode([
                        'success'              => true,
                        'message'              => $message,
                    ]);
                }else{
                    $message = _l('payslip_latch_false');
                    echo json_encode([
                        'success'              => false,
                        'message'              => $message,
                    ]);
                }
            }
        }

    }
   
    /**
     * reload timesheets byfilter
     * @return json 
     */
    public function reload_timesheets_byfilter(){
        $data = $this->input->post();
        $date_ts = $this->timesheets_model->format_date($data['month'].'-01');
        $date_ts_end = $this->timesheets_model->format_date($data['month'].'-'.date('t'));

        $year = date('Y', strtotime($date_ts));
        $g_month = date('m', strtotime($date_ts));
        $month_filter = date('Y-m', strtotime($date_ts));



        $querystring = 'active=1';
        $department = $data['department'];
        $job_position = $data['job_position'];

        $data['month'] = date('m-Y', strtotime($date_ts));
        $data['check_latch_timesheet'] = $this->timesheets_model->check_latch_timesheet($data['month']);
        $staff = '';
        if(isset($data['staff'])){
            $staff = $data['staff'];
        }


        $staff_querystring='';
        $job_position_querystring = '';
        $department_querystring='';
        $month_year_querystring='';
        $month = date('m');
        $month_year = date('Y');
        $cmonth = date('m');
        $cyear = date('Y');
        if($year != ''){
            $month_new = (string)$g_month; 
            if(strlen($month_new)==1){
                $month_new='0'.$month_new;
            }
            $month = $month_new;
            $month_year = (int)$year;
        }    
        if($department != ''){
            $arrdepartment = $this->staff_model->get('', 'staffid in (select '.db_prefix().'staff_departments.staffid from '.db_prefix().'staff_departments where departmentid = '.$department.')');
            $temp = '';
            foreach ($arrdepartment as $value) {
                $temp = $temp.$value['staffid'].',';
            }
            $temp = rtrim($temp,",");
            $department_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }
        if($job_position != ''){
            $job_position_querystring = 'role = "'.$job_position.'"';
        }
        if($staff != ''){
           $temp = '';
           $araylengh = count($staff);
           for ($i = 0; $i < $araylengh; $i++) {
               $temp = $temp.$staff[$i];
               if($i != $araylengh-1){
                    $temp = $temp.',';
               }
           }
           $staff_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }else{
            $data_timekeeping_form = get_timesheets_option('timekeeping_form');

            $timekeeping_applicable_object = [];
            if($data_timekeeping_form == 'timekeeping_task'){
                if(get_timesheets_option('timekeeping_task_role') != ''){
                    $timekeeping_applicable_object = get_timesheets_option('timekeeping_task_role');
                }
            }elseif($data_timekeeping_form == 'timekeeping_manually'){
                if(get_timesheets_option('timekeeping_manually_role') != ''){
                    $timekeeping_applicable_object = get_timesheets_option('timekeeping_manually_role');
                }
            }elseif($data_timekeeping_form == 'csv_clsx'){
                if(get_timesheets_option('csv_clsx_role') != ''){
                    $timekeeping_applicable_object = get_timesheets_option('csv_clsx_role');
                }
            }
            $staff_querystring != '';
            if($data['job_position'] != ''){
                 $staff_querystring .= 'role = '.$data['job_position'];
            }
            else{
                if($timekeeping_applicable_object){
                    if($timekeeping_applicable_object != ''){
                        $staff_querystring .= 'FIND_IN_SET(role, "'.$timekeeping_applicable_object.'")';                    
                    }
                }
            }


            if(has_permission('timesheets_timekeeping','','view_own') && !is_admin()){
                $staff_querystring .= 'and staffid '. get_recursive_child_string(get_staff_user_id());
            }
        }

        $arrQuery = array($staff_querystring,$department_querystring, $month_year_querystring, $job_position_querystring, $querystring);
        $newquerystring = '';
            foreach ($arrQuery as $string) {
                if($string != ''){
                    $newquerystring = $newquerystring.$string.' AND ';
                }            
            }  

        $newquerystring=rtrim($newquerystring,"AND ");
        if($newquerystring == ''){
            $newquerystring = [];
        }

        $days_in_month = cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));

        $month      = $g_month;
        $month_year = $year;

        $data['departments'] = $this->departments_model->get();
        $data['staffs_li'] = $this->staff_model->get();
        $data['roles']         = $this->roles_model->get();
        $data['job_position']  = $this->roles_model->get();
        $data['positions'] = $this->roles_model->get();
        
        $data['shifts'] = $this->timesheets_model->get_shifts();

        $data['day_by_month_tk'] = [];
        $data['day_by_month_tk'][] = _l('staff_id');
        $data['day_by_month_tk'][] = _l('staff');

        $data['set_col_tk'] = [];
        $data['set_col_tk'][] = ['data' => _l('staff_id'), 'type' => 'text'];
        $data['set_col_tk'][] = ['data' => _l('staff'), 'type' => 'text','readOnly' => true,'width' => 200];

        for ($d = 1; $d <= $days_in_month; $d++) {
            $time = mktime(12, 0, 0, $month, $d, $month_year);
            if (date('m', $time) == $month) {
                array_push($data['day_by_month_tk'], date('D d', $time));
                array_push($data['set_col_tk'],[ 'data' => date('D d', $time), 'type' => 'text']);
            }
        }

        $data['day_by_month_tk'] = $data['day_by_month_tk'];

        $data_map = [];
        $data_timekeeping_form = get_timesheets_option('timekeeping_form');

        $data['staff_row_tk'] = [];
        $data['staff_row'] = [];

        $staffs = $this->timesheets_model->getStaff('', $newquerystring); 
        $data['staffs_setting'] = $this->staff_model->get();
        $data['staffs'] = $staffs;

        if($data_timekeeping_form == 'timekeeping_task' && $data['check_latch_timesheet'] == false){
            foreach($staffs as $s){
                $ts_date = '';
                $ts_ts = '';
                $result_tb = [];

                $from_date = $date_ts;
                $to_date =$date_ts_end;

                $staffsTasksWhere = [];
                if($from_date != '' && $to_date != ''){
                    $staffsTasksWhere = 'IF(duedate IS NOT NULL,((startdate <= "'.$from_date.'" and duedate >= "'.$from_date.'") or (startdate <= "'.$to_date.'" and duedate >= "'.$to_date.'") or (startdate > "'.$to_date.'" and duedate < "'.$from_date.'")), IF(datefinished IS NOT NULL,IF(status = 5 ,((startdate <= "'.$from_date.'" and date_format(datefinished, "%Y-%m-%d") >= "'.$from_date.'") or (startdate <= "'.$to_date.'" and date_format(datefinished, "%Y-%m-%d") >= "'.$to_date.'") or (startdate > "'.$to_date.'" and date_format(datefinished, "%Y-%m-%d") < "'.$from_date.'")), (startdate <= "'.$from_date.'" or (startdate > "'.$from_date.'" and startdate <= "'.$to_date.'"))),(startdate <= "'.$from_date.'" or (startdate > "'.$from_date.'" and startdate <= "'.$to_date.'"))))';
                }

                $staff_task = $this->tasks_model->get_tasks_by_staff_id($s['staffid'], $staffsTasksWhere);
                    $list_in_out = [];
                        foreach ($staff_task as $key_task => $task) {
                            $list_taskstimers = $this->timesheets_model->get_taskstimers($task['id'], $s['staffid']);
                            foreach ($list_taskstimers as $taskstimers) {
                                $list_date = $this->timesheets_model->get_list_date(date('Y-m-d',$taskstimers['start_time']), date('Y-m-d',$taskstimers['end_time']));
                                foreach ($list_date as $curent_date) {
                                    $start_work_time = "";
                                    $end_work_time = "";
                                    $data_shift_list = $this->timesheets_model->get_shift_work_staff_by_date($s['staffid'], $curent_date);

                                    foreach ($data_shift_list as $ss) {
                                        $data_shift_type = $this->timesheets_model->get_shift_type($ss); 
                                        if($start_work_time == "" || strtotime($start_work_time) > strtotime($curent_date.' '.$data_shift_type->time_start_work.':00')){
                                            $start_work_time = $curent_date.' '.$data_shift_type->time_start_work.':00';
                                        }
                                        if($end_work_time == "" || strtotime($end_work_time) < strtotime($curent_date.' '.$data_shift_type->time_end_work.':00')){
                                            $end_work_time = $curent_date.' '.$data_shift_type->time_end_work.':00';
                                        }
                                    } 
                                    if(strtotime($start_work_time) < strtotime($curent_date.' '.date('H:i:s',$taskstimers['start_time']))){
                                        $start_work_time = $curent_date.' '.date('H:i:s',$taskstimers['start_time']);
                                    }
                                    if(strtotime($end_work_time) > strtotime($curent_date.' '.date('H:i:s',$taskstimers['end_time'])) && strtotime(date('Y-m-d',$taskstimers['end_time'])) == strtotime($curent_date)){
                                        $end_work_time = $curent_date.' '.date('H:i:s',$taskstimers['end_time']);
                                    }

                                    if(strtotime($from_date) <= strtotime(date('Y-m-d',strtotime($start_work_time))) && strtotime($to_date) >= strtotime(date('Y-m-d',strtotime($start_work_time)))){
                                        if(isset($list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'])){
                                            if(strtotime($list_in_out[date('Y-m-d',strtotime($start_work_time))]['in']) > strtotime($start_work_time)){
                                                $list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'] = $start_work_time;
                                            }
                                        }else{
                                            $list_in_out[date('Y-m-d',strtotime($start_work_time))]['in'] = $start_work_time;
                                        }



                                        if(isset($list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'])){
                                            if(strtotime($list_in_out[date('Y-m-d',strtotime($start_work_time))]['out']) < strtotime($start_work_time)){
                                                $list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'] = $start_work_time;
                                            }
                                        }else{
                                            $list_in_out[date('Y-m-d',strtotime($start_work_time))]['out'] = $start_work_time;
                                        }
                                    }
                                    if(strtotime($from_date) <= strtotime(date('Y-m-d',strtotime($end_work_time))) && strtotime($to_date) >= strtotime(date('Y-m-d',strtotime($end_work_time)))){
                                        if(isset($list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'])){
                                            if(strtotime($list_in_out[date('Y-m-d',strtotime($end_work_time))]['in']) >strtotime($end_work_time)){
                                                $list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'] = $end_work_time;
                                            }
                                        }else{
                                            $list_in_out[date('Y-m-d',strtotime($end_work_time))]['in'] = $end_work_time;
                                        }

                                        if(isset($list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'])){
                                            if(strtotime($list_in_out[date('Y-m-d',strtotime($end_work_time))]['out']) <strtotime($end_work_time)){
                                                $list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'] = $end_work_time;
                                            }
                                        }else{
                                            $list_in_out[date('Y-m-d',strtotime($end_work_time))]['out'] = $end_work_time;
                                        }
                                    }
                                }

                            }
                        }
                        foreach ($list_in_out as $date_ => $in_out) {
                            $vl = $this->timesheets_model->get_data_insert_timesheets($s['staffid'], $in_out['in'], $in_out['out']);
                            if(!isset($data_map[$s['staffid']][$date_]['ts'])){
                                $data_map[$s['staffid']][$date_]['date'] = date('D d', strtotime($date_));
                                $data_map[$s['staffid']][$date_]['ts'] = '';
                            }
                            if($vl['late'] > 0){
                                $data_map[$s['staffid']][$date_]['ts'] .= 'L:'.$vl['late'].'; ';
                            }
                            if($vl['early'] > 0){
                                $data_map[$s['staffid']][$date_]['ts'] .= 'E:'.$vl['early'].'; ';
                            }
                            if($vl['work'] > 0){
                                $data_map[$s['staffid']][$date_]['ts'] .= 'W:'.$vl['work'].'; ';
                            }

                            $data_map[$s['staffid']][$date_]['ts'] = rtrim($data_map[$s['staffid']][$date_]['ts'], '; ');
                        }

                        if(isset($data_map[$s['staffid']])){
                            foreach ($data_map[$s['staffid']] as $key => $value) {
                                $ts_date = $data_map[$s['staffid']][$key]['date'];
                                $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                                $result_tb[] = [$ts_date => $ts_ts];
                            }
                        }

                        $dt_ts = [];
                        $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                        $note = [];
                        $list_dtts = [];
                        foreach ($result_tb as $key => $rs) {
                            foreach ($rs as $day => $val) {
                               $list_dtts[$day] = $val;
                            }
                        }

                        $list_date = $this->timesheets_model->get_list_date(date('Y-m-01'), date('Y-m-t'));
                        foreach ($list_date as $key => $value) {
                            $date_s = date('D d', strtotime($value));
                            $max_hour = $this->timesheets_model->get_hour_shift_staff($s['staffid'],$value);

                            $ts_db = '';
                            $data_timesheet = $this->timesheets_model->get_ts_staff_by_date($s['staffid'],$value);
                            if($data_timesheet){
                                foreach ($data_timesheet as $key => $ts) {
                                    $ts_db .= $ts["type"].':'.$ts["value"].'; ';
                                }
                            }
                            if($ts_db != ''){
                                $ts_db = rtrim($ts_db,'; ');
                            }
                            if($max_hour > 0){
                                
                                $ts_lack = '';
                                if(isset($list_dtts[$date_s])){
                                    $ts_lack = $list_dtts[$date_s].'; ';
                                }
                                $total_lack = $ts_lack.''.$ts_db;
                                if($total_lack){
                                    $total_lack = rtrim($total_lack, '; ');
                                }
                                $data_result = $this->timesheets_model->merge_ts($total_lack, $max_hour);
                                $dt_ts[$date_s] = $data_result;
                            }
                        }
                        array_push($data['staff_row_tk'], $dt_ts);
                    }   

              
        }elseif($data_timekeeping_form == 'timekeeping_manually' && $data['check_latch_timesheet'] == false){

            $data_ts = $this->timesheets_model->get_timesheets_ts_by_month(date('m'), date('Y'));
            foreach($data_ts as $ts){
                $staff_info = array();
                $staff_info['date'] = date('D d', strtotime($ts['date_work']));  
                $ts_type = $this->timesheets_model->get_ts_by_date_and_staff($ts['date_work'],$ts['staff_id']);
                if(count($ts_type) <= 1){
                    if($ts['value'] > 0){
                        $staff_info['ts'] = $ts['type'].':'.$ts['value'];
                    }else{
                        $staff_info['ts'] = '';
                    }
                }else{
                    $str = '';
                    foreach($ts_type as $tp){
                        if($tp['value'] > 0){
                            if($tp['type'] == 'HO' || $tp['type'] == 'M'){
                                if($str == ''){
                                    $str .= $tp['type'];
                                }else{
                                    $str .= "; ".$tp['type'];
                                }
                            }else{
                                if($str == ''){
                                    $str .= $tp['type'].':'.round($tp['value'], 2);
                                }else{
                                    $str .= "; ".$tp['type'].':'.round($tp['value'], 2);
                                }
                            }
                        }                     
                    }
                    $staff_info['ts'] = $str;
                }          
                
                if(!isset($data_map[$ts['staff_id']])){
                    $data_map[$ts['staff_id']] = array();
                }
                $data_map[$ts['staff_id']][$staff_info['date']] = $staff_info;
            }
            foreach($staffs as $s){
                $ts_date = '';
                $ts_ts = '';
                $result_tb = [];
                if(isset($data_map[$s['staffid']])){
                    foreach ($data_map[$s['staffid']] as $key => $value) {
                        $ts_date = $data_map[$s['staffid']][$key]['date'];
                        $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                        $result_tb[] = [$ts_date => $ts_ts];
                    }
                }

                 $dt_ts = [];
                $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                $note = [];
                $list_dtts = [];
                foreach ($result_tb as $key => $rs) {
                    foreach ($rs as $day => $val) {
                       $list_dtts[$day] = $val;
                    }
                }

                $list_date = $this->timesheets_model->get_list_date($date_ts, $date_ts_end);
                foreach ($list_date as $key => $value) {
                     $date_s = date('D d', strtotime($value));

                    $max_hour = $this->timesheets_model->get_hour_shift_staff($s['staffid'],$value);

                    if($max_hour > 0){

                        $ts_lack = '';
                        if(isset($list_dtts[$date_s])){
                            $ts_lack = $list_dtts[$date_s].'; ';
                        }
                        $total_lack = $ts_lack;
                        if($total_lack){
                            $total_lack = rtrim($total_lack, '; ');
                        }
                        $data_result = $this->timesheets_model->merge_ts($total_lack, $max_hour);
                        $dt_ts[$date_s] = $data_result;
                    }
                }

                array_push($data['staff_row_tk'], $dt_ts);
            }
        }else {
            $data_ts = $this->timesheets_model->get_timesheets_ts_by_month($g_month, $year);

            foreach($data_ts as $ts){
                $staff_info = array();
                $staff_info['date'] = date('D d', strtotime($ts['date_work']));                
                $ts_type = $this->timesheets_model->get_ts_by_date_and_staff($ts['date_work'],$ts['staff_id']);

                if(count($ts_type) <= 1){
                    if($ts['value'] > 0){
                        $staff_info['ts'] = $ts['type'].':'.$ts['value'];
                    }else{
                        $staff_info['ts'] = '';
                    }
                }else{
                    $str = '';
                    foreach($ts_type as $tp){
                        if($tp['value'] > 0){
                            if($tp['type'] == 'HO' || $tp['type'] == 'M'){
                                if($str == ''){
                                    $str .= $tp['type'];
                                }else{
                                    $str .= "; ".$tp['type'];
                                }
                            }else{
                                if($str == ''){
                                    $str .= $tp['type'].':'.round($tp['value'], 2);
                                }else{
                                    $str .= "; ".$tp['type'].':'.round($tp['value'], 2);
                                }
                            }
                        }                     
                    }
                    $staff_info['ts'] = $str;
                }          
                
                if(!isset($data_map[$ts['staff_id']])){
                    $data_map[$ts['staff_id']] = array();
                }
                $data_map[$ts['staff_id']][$staff_info['date']] = $staff_info;
            }

            foreach($staffs as $s){
                $ts_date = '';
                $ts_ts = '';
                $result_tb = [];
                if(isset($data_map[$s['staffid']])){
                    foreach ($data_map[$s['staffid']] as $key => $value) {
                        $ts_date = $data_map[$s['staffid']][$key]['date'];
                        $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                        $result_tb[] = [$ts_date => $ts_ts];
                    }
                }

                $dt_ts = [];
                $dt_ts = [_l('staff_id') => $s['staffid'],_l('staff') => $s['firstname'].' '.$s['lastname']];
                $note = [];
                foreach ($result_tb as $key => $rs) {
                    foreach ($rs as $day => $val) {
                       $dt_ts[$day] = $val;

                    }
                }
                array_push($data['staff_row_tk'], $dt_ts);
            }
        }
        $data_lack = [];
        $data['data_lack'] = $data_lack;
            echo json_encode([
                'arr' => $data['staff_row_tk'],
                'set_col_tk' =>  $data['set_col_tk'],
                'day_by_month_tk' =>  $data['day_by_month_tk'],
                'check_latch_timesheet' => $data['check_latch_timesheet'],
                'month' => $data['month'],
                'data_lack' => $data['data_lack'],
            ]);
            die;
    }

    public function add_requisition_ajax(){
            if($_FILES['file']['name'] != ''){
                $_FILES = $_FILES;
            }else{
                unset($_FILES);
            }
            if ($this->input->post()) {
                $data = $this->input->post();
                $data['start_time'] = $this->timesheets_model->format_date_time($data['start_time']);
                $data['end_time'] = $this->timesheets_model->format_date_time($data['end_time']);
                $data['staff_id'] = get_staff_user_id();
                if(isset($data['according_to_the_plan'])){
                    $data['according_to_the_plan'] = 0;
                }

                $result = $this->timesheets_model->add_requisition_ajax($data);
                if ($result != '') {
                   echo json_encode([
                        'message' => 'success',
                        'success' => true,
                    ]);

                    $rel_type = '';
                    if($data['rel_type'] == '1'){
                      $rel_type = 'Leave';
                    }elseif($data['rel_type']->rel_type == '2'){
                      $rel_type = 'Late_early';
                    }elseif($data['rel_type']->rel_type == '3'){
                      $rel_type = 'Go_out';
                    }elseif($data['rel_type']->rel_type == '4'){
                      $rel_type = 'Go_on_bussiness';
                    }elseif($data['rel_type']->rel_type == '5'){
                       $rel_type = 'quit_job'; 
                    }

                    $data_app['rel_id'] = $result;
                    $data_app['rel_type'] = $rel_type;
                    $data_app['addedfrom'] = $data['staff_id'];

                    $check_proccess = $this->timesheets_model->get_approve_setting($rel_type);
                    $check = '';
                    if($check_proccess){
                        $checks = $this->timesheets_model->check_choose_when_approving($rel_type);
                        if($checks == 0){
                            $this->send_request_approve_requisition($data_app);
                            $check = 'not_choose';
                        }else{
                            $check = 'choose';
                        }
                    }else{
                        $check = 'no_proccess';
                    }

                    $followers_id = $data['followers_id'];
                    $staffid = $data['staff_id'];
                    $subject = $data['subject'];
                    $link = 'timesheets/requisition_detail/' . $result;

                   

                    if($followers_id != ''){
                        if ($staffid != $followers_id) {
                            $notification_data = [
                                'description' => _l('you_are_added_to_follow_the_leave_application').'-'.$subject,
                                'touserid'    => $followers_id,
                                'link'        => $link,
                            ];

                            $notification_data['additional_data'] = serialize([
                                $subject,
                            ]);

                            if (add_notification($notification_data)) {
                                pusher_trigger_notification([$followers_id]);
                            }

                        }
                    }
                    redirect(admin_url('timesheets/requisition_detail/'.$result.'?check='.$check));
                }else{
                    redirect(admin_url('timesheets/requisition_manage'));
                }        
      }
  }
    /**
     * table registration leave
     * @return 
     */
    public function table_registration_leave()
    {
        $this->app->get_table_data(module_views_path('timesheets', 'table_registration_leave'));
    }

    /**
     * table additional timesheets
     * @return 
     */
    public function table_additional_timesheets()
    {
        $this->app->get_table_data(module_views_path('timesheets', 'timekeeping/table_additional_timesheets'));
    }

    /**
     * get request leave data ajax
     * @return 
     */
    public function get_request_leave_data_ajax()
    {
        $data[] = $this->timesheets_model->get_category_for_leave();
    }

    /**
     * requisition detail
     * @param  int $id 
     * @return view
     */
     public function requisition_detail($id){
        $send_mail_approve = $this->session->userdata("send_mail_approve");
        if((isset($send_mail_approve)) && $send_mail_approve != ''){
            $data['send_mail_approve'] = $send_mail_approve;
            $this->session->unset_userdata("send_mail_approve");
        }
        $data['request_leave'] = $this->timesheets_model->get_request_leave($id);

        $status_leave = $this->timesheets_model->get_number_of_days_off($data['request_leave']->staff_id);
        $day_off = $this->timesheets_model->get_day_off($data['request_leave']->staff_id);
        $data['number_day_off'] = 0;
        if($day_off != null){
            $data['number_day_off'] = $day_off->remain;
        }

        $leave_isset = $this->db->query('select * from '.db_prefix().'timesheets_requisition_leave')->result_array();
        $data['id'] = $id;
        $data['leave_isset'] = $leave_isset;
      
        $rel_type = '';
        if($data['request_leave']->rel_type == '1'){
          $rel_type = 'Leave';
        }elseif($data['request_leave']->rel_type == '2'){
          $rel_type = 'Late_early';
        }elseif($data['request_leave']->rel_type == '3'){
          $rel_type = 'Go_out';
        }elseif($data['request_leave']->rel_type == '4'){
          $rel_type = 'Go_on_bussiness';
        }elseif($data['request_leave']->rel_type == '5'){
           $rel_type = 'quit_job'; 
        }

        $this->load->model('staff_model');

        if($data['request_leave']->rel_type == '4'){
            $data['advance_payment'] = $this->timesheets_model->get_go_bussiness_advance_payment($id);
        }

        $id_file = $this->db->query('select id from '.db_prefix().'files where rel_id ='.$id)->row();
        $data['id_file'] = $id_file;
        $data['rel_type'] = $rel_type;
        $data['list_staff'] = $this->staff_model->get();
        $data['check_approve_status'] = $this->timesheets_model->check_approval_details($id,$rel_type);
        $data['list_approve_status'] = $this->timesheets_model->get_list_approval_details($id,$rel_type);

        $this->load->view('timesheets/requisition_detail', $data);
    }

    /**
     * delete requisition
     * @param  int $id
     * @return redirect
     */
    public function delete_requisition($id)
    {
        $response = $this->timesheets_model->delete_requisition($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_source_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_source')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_source_lowercase')));
        }
        redirect(admin_url('timesheets/requisition_manage'));
    }

    /**
     * infor staff 
     * @param  int $id
     * @return data
     */
     public function infor_staff($id)
     {
        $this->db->select('s.email,r.name as name_role, d.name');
        $this->db->from('staff as s');
        $this->db->join('staff_departments as sd','s.staffid = sd.staffid');
        $this->db->join('departments as d','sd.departmentid = d.departmentid');
        $this->db->join('roles as r','s.role = r.roleid');
        $this->db->where('s.staffid',$id);
        $data = $this->db->get()->row();
        json_encode($data);
        return $data;
     }

     /**
      * approve request leave
      * @param  int $status
      * @param  int $id
      * @return redirect
      */
     public function approve_request_leave($status,$id){
        $result = $this->timesheets_model->approve_request_leave($status, $id);
        if($result == 'approved'){
            set_alert('success',_l('approved').' '._l('request_leave').' '._l('successfully'));
        }elseif($result == 'reject'){
            set_alert('success',_l('reject').' '._l('request_leave').' '._l('successfully'));
        }else{
            set_alert('warning',_l('action').' '._l('fail'));
        }
        redirect(admin_url('timesheets/requisition_detail/'.$id));
    }

    /**
     * file
     * @param  int $id
     * @param  int $rel_id
     * @return view
     */
     public function file($id, $rel_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin']             = is_admin();
        $data['file'] = $this->timesheets_model->get_file($id, $rel_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('_file', $data);
    }
    
    /**
     * requisition manage
     * @return view
     */
    public function requisition_manage(){
        $send_mail_approve = $this->session->userdata("send_mail_approve");
        if((isset($send_mail_approve)) && $send_mail_approve != ''){
            $data['send_mail_approve'] = $send_mail_approve;
            $this->session->unset_userdata("send_mail_approve");
        }
        
        $status_leave = $this->timesheets_model->get_number_of_days_off();
        $day_off = $this->timesheets_model->get_day_off();
        $data['number_day_off'] = 0;
        $data['days_off'] = 0;
        if($day_off != null){
            $data['number_day_off'] = $day_off->remain;
            if($data['number_day_off'] < 0){
                $data['number_day_off'] = 0;
            }
            $data['days_off'] = $day_off->days_off;
            if($data['days_off'] > $day_off->total){
                $data['days_off'] = $day_off->total;
            }
        }
        $data['data_timekeeping_form'] = get_timesheets_option('timekeeping_form');
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $data['current_date'] = date('Y-m-d H:i:s');
        $status_leave = $this->timesheets_model->get_option_val();
        $this->load->model('staff_model');
        $data['pro'] = $this->staff_model->get();
        $data['userid'] = get_staff_user_id();
        $data['tab'] = $this->input->get('tab');
        $data['title'] = _l('leave');
        $data['additional_timesheets_id'] = $this->input->get('additional_timesheets_id');
        $data['additional_timesheets'] = $this->timesheets_model->get_additional_timesheets();
        $this->load->view('timesheets/timekeeping/manage_requisition_hrm', $data);
       
    }

    /**
     * automatic timekeeping
     * @param  $data
     * @return json
     */
    public function automatic_timekeeping($data){
        
        $success = $this->timesheets_model->automatic_timekeeping($data);

        if ($success) {
            set_alert('success', _l('successfully'));
        } else {
            set_alert('warning', _l('fail'));
        }

        echo json_encode([
            'success' => $success,
        ]);
        die();
    }

    /**
     * setting timekeeper
     * @return redirect
     */
    public function setting_timekeeper(){
        $data = $this->input->post();
        $success = $this->timesheets_model->setting_timekeeper($data);
        if($success){
            set_alert('success',_l('save_setting_success'));
        }else{
            set_alert('danger',_l('save_setting_fail'));
        }
        redirect(admin_url('timesheets/setting?group=timekeeping_settings'));

    }

    /**
     * edit timesheets
     * @return redirect
     */
    public function edit_timesheets(){
        $data = $this->input->post();
        $success = $this->timesheets_model->edit_timesheets($data);
        if($success){
            set_alert('success',_l('save_setting_success'));
        }else{
            set_alert('danger',_l('save_setting_fail'));
        }
        redirect(admin_url('timesheets/timekeeping?group=timesheets'));
    }

    /**
     * send additional timesheets
     * @return redirect
     */
    public function send_additional_timesheets(){
        $data = $this->input->post();
        $success = false;
        if(isset($data['additional_day'])){
            $check_latch_timesheet = $this->timesheets_model->check_latch_timesheet(date('m-Y',strtotime(to_sql_date($data['additional_day']))));
            if($check_latch_timesheet){
                set_alert('danger',_l('timekeeping_latched'));
                redirect(admin_url('timesheets/member/'.get_staff_user_id().'?tab=timekeeping'));
            }
            $success = $this->timesheets_model->add_additional_timesheets($data);
        }
        if($success){
            set_alert('success', _l('added_successfully', _l('additional_timesheets')));
        }else{
            set_alert('warning', _l('fail'));
        }
        redirect(admin_url('timesheets/requisition_manage?tab=additional_timesheets&additional_timesheets_id='.$success));
    }

    /**
     * approve additional timesheets
     * @param  int $id
     * @return json
     */
    public function approve_additional_timesheets($id){
        $data = $this->input->post();

        $message = _l('rejected_successfully');

        $success = $this->timesheets_model->update_additional_timesheets($data, $id);

        if($success){
            $this->db->where('id', $id);
            $additional_timesheet = $this->db->get(db_prefix().'timesheets_additional_timesheet')->row();

            $this->timesheets_model->edit_timesheets($additional_timesheet);

            $ci = &get_instance();
            $staff_id = get_staff_user_id();
            $additional_data = '';
            if($data['status'] == 1){
                $mes_creator = 'notify_send_creator_additional_timesheet_approved';
            }else{
                $mes_creator = 'notify_send_creator_additional_timesheet_rejected';
            }

            $title_creator = 'approval';

            $link = 'timesheets/requisition_manage?tab=additional_timesheets?additional_timesheets_id='.$id;

            if(isset($additional_timesheet->creator)  && $additional_timesheet->creator != $staff_id){
                $notified = add_notification([
                'description'     => $mes_creator,
                'touserid'        => $additional_timesheet->creator,
                'link'            => $link,
                'additional_data' => serialize([
                    $additional_data,
                ]),
                ]);
                if ($notified) {
                    pusher_trigger_notification([$additional_timesheet->creator]);
                }

                $ci->email->initialize();
                $ci->load->library('email');    
                $ci->email->clear(true);
                $ci->email->from(get_staff_email_by_id($staff_id), get_staff_full_name($staff_id));
                $ci->email->to(get_staff_email_by_id($additional_timesheet->creator));
                
                $ci->email->subject(_l($title_creator));
                $ci->email->message(_l($mes_creator).' <a target="_blank" class="u6" href="'.admin_url($link).'">Link</a>');
              
                $ci->email->send(true);
            }

            if($data['status'] == 1){
                $message = _l('approved_successfully');
            }else{
                $message = _l('rejected_successfully');
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
        die();    
    }

    /**
     * show detail timesheets
     * @return json
     */
    public function show_detail_timesheets(){
        $data = $this->input->post();
        $d = substr($data['ColHeader'],  4, 2);
        $time = $data['month'].'-'.$d;
        $d = _d($time);
        $st = $this->staff_model->get($data['staffid']);
        if(!isset($st->staffid)){
            echo json_encode([
            'title' => '',
            'html' => '',
            ]);
            die();
        }
        $title = get_staff_full_name($st->staffid). ' - '. $d;

        $data['value'] = explode('; ', $data['value']);
        $html = '';
        foreach ($data['value'] as $key => $value) {
            $value = explode(':', $value);
            if(isset($value[1]) && $value[1] > 0 || $value[0] == 'M' || $value[0] == 'HO'){
                switch ($value[0]) {
                    case 'AL':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('p_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_p">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'W':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('W_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_w">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'A':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('A_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_a">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'HO':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('Le_timekeeping').'
                              </li>';
                        break;
                    case 'E':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('E_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_e">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'L':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('L_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_l">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'B':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('CT_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_b">'.round($value[1], 2).'</span>
                                  </li>';
                        break;    
                    case 'U':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('U_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'OM':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('OM_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_om">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'M':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('TS_timekeeping').'
                              </li>';
                        break;
                    case 'R':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('R_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'Ro':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('Ro_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'SI':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('CD_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'CO':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('CO_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;                    
                    case 'ME':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('H_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_me">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'OT':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('OT_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_me">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'PO':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('PN_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_po">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                }
            }
        }

        
        $ws_day ='';
        $color = '';
        $list_shift = $this->timesheets_model->get_shift_work_staff_by_date($data['staffid'], $time);

        foreach ($list_shift as $ss) {
            $data_shift_type = $this->timesheets_model->get_shift_type($ss);
            if($data_shift_type){
                $ws_day .= '<li class="list-group-item justify-content-between">'._l('work_times').': '.$data_shift_type->time_start_work.' - '.$data_shift_type->time_end_work.'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$data_shift_type->start_lunch_break_time.' - '.$data_shift_type->end_lunch_break_time.'</li>';
            }  
        }

        if($ws_day != ''){
            $html .= $ws_day;
        }
        $access_history_string = '';
        $access_history = $this->timesheets_model->get_list_check_in_out( $time,$data['staffid']);
        if($access_history){
            foreach ($access_history as $key => $value) {
                if($value['type_check'] == '1'){
                    $access_history_string .= '<li class="list-group-item"><i class="fa fa-sign-in text-success" aria-hidden="true"></i> '._dt($value['date']).'</li>';
                }else{
                    $access_history_string .= '<li class="list-group-item"><i class="fa fa-sign-out text-danger" aria-hidden="true"></i> '._dt($value['date']).'</li>';
                }
            }
        }
        if($access_history_string != ''){
            $html .= '<li class="list-group-item justify-content-between"><ul class="list-group">
                            <li class="list-group-item active">'._l('access_history').'</li>
                            '.$access_history_string.'
                        </ul></li>';
        }
        echo json_encode([
            'title' => $title,
            'html' => $html,
        ]);
        die();
    }

    /**
     * approval process
     * @param  string $id 
     * @return redirect
     */
    public function approval_process($id = ''){
        if (!has_permission('staffmanage_approval', '', 'view') && !is_admin() ) {
            access_denied('approval_process');
        }

        if ($this->input->post()) {
            $data                = $this->input->post();
            $id = $data['approval_setting_id'];
            unset($data['approval_setting_id']);
            if ($id == '') {
                if (!has_permission('staffmanage_approval', '', 'create')) {
                    access_denied('approval_process');
                }
                $id = $this->timesheets_model->add_approval_process($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('approval_process')));
                }
            } else {
                if (!has_permission('staffmanage_approval', '', 'edit')) {
                    access_denied('approval_process');
                }
                $success = $this->timesheets_model->update_approval_process($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('approval_process')));
                }
            }
            redirect(admin_url('timesheets/setting?group=approval_process'));
        }
    }

    /**
     * table approval process
     * @return 
     */
    public function table_approval_process(){
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('timesheets', 'approval_process/table_approval_process'));
        }
    }

    /**
     * get html approval setting
     * @param  string $id
     * @return 
     */
    public function get_html_approval_setting($id = '')
    {
        $html = '';
        $staffs = $this->staff_model->get();
        $approver = [
                0 => ['id' => 'direct_manager', 'name' => _l('direct_manager')],
                1 => ['id' => 'department_manager', 'name' => _l('department_manager')],
                2 => ['id' => 'staff', 'name' => _l('staff')]];
        if(is_numeric($id)){
            $approval_setting = $this->accounting_model->get_approval_setting($id);

            $setting = json_decode($approval_setting->setting);
            
            foreach ($setting as $key => $value) {
                if($key == 0){
                    $html .= '<div id="item_approve">
                                    <div class="col-md-11">
                                    <div class="col-md-4"> '.
                                    render_select('approver['.$key.']',$approver,array('id','name'),'task_single_related', $value->approver).'
                                    </div>
                                    <div class="col-md-4">
                                    '. render_select('staff['.$key.']',$staffs,array('staffid','full_name'),'staff', $value->staff).'
                                    </div>
                                    <div class="col-md-4">
                                        '. render_select('action['.$key.']',$action,array('id','name'),'action', $value->action).' 
                                    </div>
                                    </div>
                                    <div class="col-md-1 contents-nowrap">
                                    <span class="pull-bot">
                                        <button name="add" class="btn new_vendor_requests btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                                        </span>
                                  </div>
                                </div>';
                }else{
                    $direct_manager = '';
                    $department_manager = '';
                    $staff = '';
                    if($value->approver == 'direct_manager'){
                        $direct_manager = 'selected';

                    }elseif($value->approver == 'department_manager'){
                        $department_manager = 'selected';

                    }elseif($value->approver == 'staff'){
                        $staff = 'selected';
                    }
                     $html .= '<div id="item_approve">
                                    <div class="col-md-11">
                                    <div class="col-md-4">
                                        '.
                                    render_select('approver['.$key.']',$approver,array('id','name'),'task_single_related', $value->approver).' 
                                    </div>

                                    <div class="col-md-6">
                                        <div class="select-placeholder form-group">
                                          <label for="approver['.$key.']">'. _l('approver').'</label>
                                          <select name="approver['.$key.']" id="approver['.$key.']" data-id="'.$key.'" class="selectpicker" data-width="100%" data-none-selected-text="'. _l('dropdown_non_selected_tex').'" data-hide-disabled="true" required>
                                              <option value=""></option>
                                              <option value="direct_manager" '.$direct_manager.'>'. _l('direct_manager').'</option>
                                              <option value="department_manager" '.$department_manager.'>'. _l('department_manager').'</option>
                                              <option value="staff" '.$staff.'>'. _l('staff').'</option>
                                          </select>
                                        </div> 
                                      </div>
                                      <div class="col-md-6 hide" id="is_staff_'.$key.'">
                                        <div class="select-placeholder form-group">
                                          <label for="staff['.$key.']">'. _l('staff').'</label>
                                          <select name="staff['.$key.']" id="staff['.$key.']" class="selectpicker" data-width="100%" data-none-selected-text="'. _l('dropdown_non_selected_tex').'" data-hide-disabled="true" data-live-search="true">
                                              <option value=""></option>';
                                               foreach($staffs as $val){
                                                if($value->staff == $val){
                                                    $html .= '<option value="'. $val['staffid'].'" selected>
                                                       '. get_staff_full_name($val['staffid']).'
                                                    </option>';
                                                }else{
                                                    $html .= '<option value="'. $val['staffid'].'">
                                                       '. get_staff_full_name($val['staffid']).'
                                                    </option>';
                                                }
                                             }
                                          $html .= '</select>
                                        </div> 
                                      </div>
                                    </div>
                                    <div class="col-md-1 contents-nowrap">
                                    <span class="pull-bot">
                                        <button name="add" class="btn remove_vendor_requests btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                                        </span>
                                  </div>
                                </div>';
                }
            }   
        }else{
            $html .= '<div id="item_approve">
                        <div class="col-md-11">
                        <div class="col-md-6">
                    <div class="select-placeholder form-group">
                      <label for="approver[0]">'. _l('approver').'</label>
                      <select name="approver[0]" id="approver[0]" data-id="0" class="selectpicker" data-width="100%" data-none-selected-text="'. _l('dropdown_non_selected_tex').'" data-hide-disabled="true" required>
                          <option value=""></option>
                          <option value="direct_manager">'. _l('direct_manager').'</option>
                          <option value="department_manager">'. _l('department_manager').'</option>
                          <option value="staff">'. _l('staff').'</option>
                      </select>
                    </div> 
                  </div>
                  <div class="col-md-6 hide" id="is_staff_0">
                    <div class="select-placeholder form-group">
                      <label for="staff[0]">'. _l('staff').'</label>
                      <select name="staff[0]" id="staff[0]" class="selectpicker" data-width="100%" data-none-selected-text="'. _l('dropdown_non_selected_tex').'" data-hide-disabled="true" data-live-search="true">
                          <option value=""></option>';
                           foreach($staffs as $val){
                        $html .= '<option value="'. $val['staffid'].'">
                           '. get_staff_full_name($val['staffid']).'
                        </option>';
                         }
                      $html .= '</select>
                    </div> 
                  </div>
                        </div>
                        <div class="col-md-1 contents-nowrap">
                        <span class="pull-bot">
                            <button name="add" class="btn new_vendor_requests btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                            </span>
                      </div>
                    </div>';
        }

        echo json_encode([
                    $html
                ]);
    }

    /**
     * new approval setting
     * @return view
     */
    public function new_approval_setting(){
        
        $data['title']                 = _l('add_approval_process');
        $this->load->model('roles_model');
        $data['staffs'] = $this->staff_model->get();
        $data['departments'] = $this->departments_model->get();
        $data['job_positions'] = $this->roles_model->get();

        $this->load->view('approval_process/add_edit_approval_process', $data);
    }

    /**
     * edit approval setting
     * @param  string $id
     * @return 
     */
    public function edit_approval_setting($id = ''){
        $data['approval_setting'] = $this->timesheets_model->get_approval_process($id);
        $data['title']                 = _l('edit_approval_process');

        $data['departments'] = $this->departments_model->get();
        $data['job_positions'] = $this->roles_model->get();

        $data['staffs'] = $this->staff_model->get();

        $this->load->view('approval_process/add_edit_approval_process', $data);
    }

    /**
     * delete approval setting
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_approval_setting($id)
    {
        if (!$id) {
            redirect(admin_url('timesheets/approval_process'));
        }
        $response = $this->timesheets_model->delete_approval_setting($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('approval_process')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('approval_process')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('approval_process')));
        }
        redirect(admin_url('timesheets/setting?group=approval_process'));
    }

    /**
     * send request approve
     * @return json
     */
    public function send_request_approve(){
        $data = $this->input->post();
        $message = 'Send request approval fail';
        $check = $this->timesheets_model->check_choose_when_approving($data['rel_type']);
        if($check == 0){
            $success = $this->timesheets_model->send_request_approve($data);


            if ($success === true) {                
                    $message = _l('send_request_approval_success');
                    $data_new = [];
                    $data_new['send_mail_approve'] = $data;
                    $this->session->set_userdata($data_new);
            }elseif($success === false){
                $message = _l('no_matching_process_found');
                $success = false;
                
            }else{
                $message = _l('could_not_find_approver_with', _l($success));
                $success = false;
            }
            echo json_encode([
                'type' => 'choose',
                'success' => $success,
                'message' => $message,
            ]); 
            die;
        }else{
            $this->load->model('staff_model');
            $list_staff = $this->staff_model->get();

            $html = '<div class="col-md-12">';
            $html .= '<div class="col-md-9"><select name="approver_c" class="selectpicker" data-live-search="true" id="approver_c" data-width="100%" data-none-selected-text="'. _l('please_choose_approver').'" required> 
                                        <option value=""></option>'; 
            foreach($list_staff as $staff){ 
                $html .= '<option value="'.$staff['staffid'].'">'.$staff['firstname'].' '.$staff['lastname'].'</option>';                  
            }
            $html .= '</select></div>';
            if($data['rel_type'] == 'additional_timesheets'){
                $html .= '<div class="col-md-3"><a href="#" onclick="choose_approver('.$data['rel_id'].','.$data['addedfrom'].');" class="btn btn-success lead-top-btn lead-view" data-loading-text="'._l('wait_text').'">'._l('choose').'</a></div>';
            }else{
                $html .= '<div class="col-md-3"><a href="#" onclick="choose_approver();" class="btn btn-success lead-top-btn lead-view" data-loading-text="'._l('wait_text').'">'._l('choose').'</a></div>';
            }
            $html .= '</div>';

            echo json_encode([
                'type' => 'not_choose',
                'html' => $html,
                'message' => _l('please_choose_approver'),
            ]);
        }
    }

    /**
     * send request approve requisition
     * @param  data
     * @return 
     */
    public function send_request_approve_requisition($data){

        $message = 'Send request approval fail';
        
        
        $success = $this->timesheets_model->send_request_approve($data);


        if ($success === true) {                
                $message = _l('send_request_approval_success');
                $data_new = [];
                $data_new['send_mail_approve'] = $data;
                $this->session->set_userdata($data_new);
        }elseif($success === false){
            $message = _l('no_matching_process_found');
            $success = false;
            
        }else{
            $message = _l('could_not_find_approver_with', _l($success));
            $success = false;
        }

    }

    /**
     * approve request
     * @return json
     */
    public function approve_request(){
        $data = $this->input->post();

        $data['staff_approve'] = get_staff_user_id();
        $success = false; 
        $code = '';
        $status_string = 'status_'.$data['approve'];
        $message = '';
        $check_approve_status = $this->timesheets_model->check_approval_details($data['rel_id'],$data['rel_type']);
        if(isset($data['approve']) && in_array(get_staff_user_id(), $check_approve_status['staffid'])){

            $success = $this->timesheets_model->update_approval_details($check_approve_status['id'], $data);
        

            $message = _l('approved_successfully');

            if ($success) {
                if($data['approve'] == 1){
                    $message = _l('approved_successfully');
                    $data_log = [];

                   
                    $data_log['note'] = "approve_request";
                    
                   
                    $check_approve_status = $this->timesheets_model->check_approval_details($data['rel_id'],$data['rel_type']);
                    if ($check_approve_status === true){
                        $this->timesheets_model->update_approve_request($data['rel_id'],$data['rel_type'], 1);
                        if($data['rel_type'] == 'quit_job'){
                            $this->load->model('staff_model');

                            $this->db->where('id',$data['rel_id']);
                            $requisition =  $this->db->get(db_prefix().'timesheets_requisition_leave')->row();
                            if($requisition){
                                $data_quitting_work=[];
                                 $staff = $this->staff_model->get($requisition->staff_id);
                                    if($staff){
                                        $department = $this->departments_model->get_staff_departments($requisition->staff_id);
                                        $role_name = $this->roles_model->get($requisition->staff_id);
                                        $data_quitting_work['staffs'] =  array('0' => $requisition->staff_id, );
                                        $data_quitting_work['email'] = $staff->email;
                                        $data_quitting_work['department'] = '';
                                        $data_quitting_work['role'] = '';
                                        if(count($department) > 0){
                                            $data_quitting_work['department'] = $department[0]['name'];
                                        }

                                        if($role_name){
                                            $data_quitting_work['role'] = $role_name->name;
                                        }
                                        $this->timesheets_model->add_tbllist_staff_quitting_work($data_quitting_work);
                                    }
                            }


                        }

                    }
                }else{
                    $message = _l('rejected_successfully');                    
                    $this->timesheets_model->update_approve_request($data['rel_id'],$data['rel_type'], 2);
                }
            }
        }

        $data_new = [];
        $data_new['send_mail_approve'] = $data;
        $this->session->set_userdata($data_new);
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
        die();      
    }

    /**
     * send mail
     * @return json
     */
    public function send_mail()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if((isset($data)) && $data != ''){
                $success = 'success';
                echo json_encode([
                'success' => $success,                
            ]); 
            }
        }
    }


/**
 * choose approver
 * @return json
 */
public function choose_approver(){
        $data = $this->input->post();
        $message = 'Send request approval fail';
        
        $success = $this->timesheets_model->choose_approver($data);
        if ($success === true) {                
                $message = 'Send request approval success';
                $data_new = [];
                $data_new['send_mail_approve'] = $data;
                $this->session->set_userdata($data_new);
        }elseif($success === false){
            $message = _l('no_matching_process_found');
            $success = false;
            
        }else{
            $message = _l('could_not_find_approver_with', _l($success));
            $success = false;
        }
        echo json_encode([
            'type' => 'choose',
            'success' => $success,
            'message' => $message,
        ]); 
        die;
        
    }

    public function get_data_additional_timesheets($id){
        $check_approve_status = $this->timesheets_model->check_approval_details($id,'additional_timesheets');
        $list_approve_status = $this->timesheets_model->get_list_approval_details($id,'additional_timesheets');

        $additional_timesheets = $this->timesheets_model->get_additional_timesheets($id);

        $html ='
        <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">
                       <span>'. _l('additional_timesheets') .'</span>
                  </h4>
              </div>
        <div class="modal-body">';

        $html .= '<div class="col-md-12">';
            if($additional_timesheets){ 
                $status_class = 'info';
                    $status_text = 'status_0';
                    if($additional_timesheets->status == 1){
                        $status_class = 'success';
                        $status_text = 'status_1';
                    }elseif ($additional_timesheets->status == 2) {
                        $status_class = 'danger';
                        $status_text = 'status_-1';
                    }
                    
                    $creator = '';
                    if(isset($additional_timesheets->creator)){
                       $creator = '<a href="' . admin_url('staff/profile/' . $additional_timesheets->creator) . '">' . staff_profile_image($additional_timesheets->creator, [
                                'staff-profile-image-small',
                                ]) . '</a> <a href="' . admin_url('staff/profile/' . $additional_timesheets->creator) . '">' . get_staff_full_name($additional_timesheets->creator) . '</a>';
                    }
                    $html .= '<table class="table border table-striped margin-top-0">
                    <tbody>
                       <tr class="project-overview">
                            <td class="bold" width="30%">'. _l('creator') .'</td>
                            <td><a href="' . admin_url('staff/profile/' . $additional_timesheets->creator) . '">' . staff_profile_image($additional_timesheets->creator, [
                                'staff-profile-image-small',
                                ]) . '</a> <a href="' . admin_url('staff/profile/' . $additional_timesheets->creator) . '">' . get_staff_full_name($additional_timesheets->creator) . '</a>
                            </td>
                        </tr>
                        <tr class="project-overview">
                           <td class="bold" width="30%">'. _l('status') .'</td>
                           <td><span class="label label-'. $status_class .' mr-1 mb-1 mt-1">'. _l($status_text) .'</span></td>
                       </tr>
                       <tr class="project-overview">
                          <td class="bold">'. _l('additional_day') .'</td>
                          <td>'. _d($additional_timesheets->additional_day).'</td>
                       </tr>
                       <tr class="project-overview">
                          <td class="bold">'. _l('time_in') .'</td>
                          <td>'. $additional_timesheets->time_in.'</td>
                       </tr>
                       <tr class="project-overview">
                          <td class="bold">'. _l('time_out') .'</td>
                          <td>'. $additional_timesheets->time_out.'</td>
                       </tr>
                       ';
                    
                    $html .= '  <tr class="project-overview">
                              <td class="bold" width="30%">'. _l('timekeeping_value') .'</td>
                              <td>'.$additional_timesheets->timekeeping_value.'</td>
                           </tr>
                           <tr class="project-overview">
                              <td class="bold" width="30%">'. _l('reason_') .'</td>
                              <td>'.$additional_timesheets->reason.'</td>
                           </tr>
                        </tbody>
                    </table>';
            }
            $html .='
        <p class="bold margin-top-15">'._l('approval_infor').'</p>
        <hr class="border-0-5" /><div class="col-md-12">

        <div class="project-overview-right">';
            if(count($list_approve_status) > 0){
                          
            $html .= '<div class="row">
                           <div class="col-md-12 project-overview-expenses-finance">';

            $this->load->model('staff_model');
            $enter_charge_code = 0;
            foreach ($list_approve_status as $value) {
                $value['staffid'] = explode(', ',$value['staffid']);

                $html .= '<div class="col-md-6" class="font-15">
                                 <p class="text-uppercase text-muted no-mtop bold">';
                $staff_name = '';
                    foreach ($value['staffid'] as $key => $val) {
                        if($staff_name != '')
                        {
                            $staff_name .= ' or ';
                        }
                        $staff_name .= $this->staff_model->get($val)->firstname;
                    }
                $html .=  $staff_name.'</p>';

                if($value['approve'] == 1){
                    $html .= '<img src="'.site_url(TIMESHEETS_PATH.'approval/approved.png').'" class="wh-150-80">';
                    $html .= '<br><br>  
                                <p class="bold text-center text-success">'. _dt($value['date']).'</p> 
                            ';

                }elseif($value['approve'] == 2){ 
                    $html .= '<img src="'.site_url(TIMESHEETS_PATH.'approval/rejected.png').'" class="wh-150-80">';
                    $html .= '<br><br>  
                                <p class="bold text-center text-danger">'. _dt($value['date']).'</p> 
                            ';
                }  
                $html .= '</div>'; 
            }
            $html .= '</div></div>';
        }

        $html .=  '</div>
<div class="clearfix"></div></br>
       <div class="modal-footer">';

            
        if($additional_timesheets->status != 1 && ($check_approve_status == false || $check_approve_status == 'reject')){ 
            $html .= '<div id="choose_approver"><a data-toggle="tooltip" data-loading-text="'. _l('wait_text').'" class="btn btn-success pull-left lead-top-btn lead-view" data-placement="top" href="#" onclick="send_request_approve('. $additional_timesheets->id.','.$additional_timesheets->creator.'); return false;">'. _l('send_request_approve').'</a></div>';
        }
                        
        if(isset($check_approve_status['staffid'])){
            if(in_array(get_staff_user_id(), $check_approve_status['staffid'])){
                $html .= '<div class="btn-group pull-left" >
               <a href="#" class="btn btn-success dropdown-toggle " data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'. _l('approve').'<span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-left wh-500-190">
                <li>
                  <div class="col-md-12">
                    '.render_textarea('reason', 'reason').'
                  </div>
                </li>
                  <li>
                    <div class="row text-right col-md-12">
                      <a href="#" data-loading-text="'._l('wait_text').'" onclick="approve_request('.$additional_timesheets->id.',\'additional_timesheets\'); return false;" class="btn btn-success margin-left-right-15">'. _l('approve').'</a>
                     <a href="#" data-loading-text="'._l('wait_text').'" onclick="deny_request('. $additional_timesheets->id.',\'additional_timesheets\'); return false;" class="btn btn-warning">'._l('deny').'</a>
                     </div>
                  </li>
               </ul>
            </div>';
            }              
        }
            
        $html .= '<a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target=".additional-timesheets-sidebar">'. _l('close') .'</a></div>';
        $html .= '</div>
        </div>
        
        
        <div class="clearfix"></div>
        </div>
        </div>';
        echo json_encode([
            'html' => $html,
        ]);
        die();
    }

    /**
     * reports
     * @return view
     */
    public function reports()
    {
        if(!has_permission('timesheets_report', '', 'view') && !is_admin() ){   
            access_denied('payroll_table');
        }
        
        $this->load->model('staff_model');
        $this->load->model('departments_model');
        $this->load->model('roles_model');
        $data['mysqlVersion'] = $this->db->query('SELECT VERSION() as version')->row();
        $data['sqlMode']      = $this->db->query('SELECT @@sql_mode as mode')->row();
        $data['staff']     = $this->staff_model->get();
        $data['department']     = $this->departments_model->get();
        $data['roles']         = $this->roles_model->get();
        
        $data['title'] = _l('hr_reports');
        $this->load->view('reports/manage_reports', $data);
    }

    /**
     * report by leave statistics
     * @return json
     */
    public function report_by_leave_statistics()
    {
        echo json_encode($this->timesheets_model->report_by_leave_statistics());
    }

    /**
     * report by working hours
     * @return json
     */
    public function report_by_working_hours()
    {
        echo json_encode($this->timesheets_model->report_by_working_hours());
    }

    /**
     * [HR_is_working description]
     */
    public function HR_is_working(){
        if ($this->input->is_ajax_request()) { 
            $year = (string)date('Y');
            $months_report = $this->input->post('months_report');
           
            if($months_report == '' || !isset($months_report)){
                
            }
            if($months_report == 'this_month'){
                
                
            }
            if($months_report == '1'){ 
                
            }
            if($months_report == 'this_year'){
                $year = (string)date('Y');

            }
            if($months_report == 'last_year'){
                $year = (string)((int)date('Y')-1);

            }

            if($months_report == '3'){


            }
            if($months_report == '6'){
  
            }
            if($months_report == '12'){

            }
            $month_default = 12;

            $list_data = array();
            for ($i=1; $i <= $month_default; $i++) {
                 $staff_list = $this->timesheets_model->get_dstafflist_by_year($year ,$i);
                 $count = count($staff_list);
                 array_push($list_data, $count);
                }
            

            echo json_encode([
                'data' => $list_data,
                'data_ratio' => $list_data,
            ]); 
        }
    }

    /**
     * file view requisition
     * @param  int $id
     * @param  int $rel_id
     * @return 
     */
    public function file_view_requisition($id, $rel_id)
    {
        $data['file'] = $this->timesheets_model->get_file_requisition($id, $rel_id);
        $data['rel_id'] = $rel_id;
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('includes/_file', $data);
    }

   /**
    * leave reports
    * @return json
    */
    public function leave_reports()
    {
        if ($this->input->is_ajax_request()) {
            if($this->input->post()){
            $months_report = $this->input->post('months_filter');
            $role_filter = $this->input->post('role_filter');
            $department_filter = $this->input->post('department_filter');
            $staff_filter = $this->input->post('staff_filter');

            $year_filter = $this->input->post('year_requisition');

            $year = date('Y');
            if($months_report == 'last_year'){
               $year = (int)$year-1;
            }
            $select = [
                'staffid',
                'firstname',

                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
            ];
            $query = '';
            if(isset($role_filter)){
                $position_list = implode(',', $role_filter);
                $query .= ' role in ('.$position_list.') and';
            }
            if(isset($staff_filter)){
                $staffid_list = implode(',', $staff_filter);
                $query .= ' staffid in ('.$staffid_list.') and';
            }
            if(isset($department_filter)){
                $department_list = implode(',', $department_filter);
                $query .= ' staffid in (SELECT staffid FROM '.db_prefix().'staff_departments where departmentid in ('.$department_list.')) and';
            }

            if(isset($year_filter)){
                $year_leave = $year_filter;
            }else{
                $year_leave = date('Y');
            }

            $total_query = '';
            if(($query)&&($query != '')){
                $total_query = rtrim($query, ' and');
                $total_query = ' where '.$total_query;
            }
            $where              = [$total_query];


            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = db_prefix() . 'staff';
            $join         = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'lastname',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {

                $requisition_number_of_day_off = $this->timesheets_model->get_requisition_number_of_day_off($aRow['staffid'], $year_leave);

                $timesheets_max_leave_in_year = $requisition_number_of_day_off['total_day_off_in_year'];
                $timesheets_total_day_off = 0;

                $row = [];

                $row[] = $aRow['staffid'];
                $row[] = trim($aRow['firstname'].' '.$aRow['lastname']);
                $total_leave = $timesheets_max_leave_in_year;

                $row[] = $total_leave;
                $sum_count = 0;
                for($i=1;$i<=12;$i++){

                    if($i < 10){
                        $months_filter = $year_leave.'-0'.$i;

                    }else{
                        $months_filter = $year_leave.'-'.$i;

                    }
                    $count =$this->timesheets_model->get_date_leave_in_month($aRow['staffid'], $months_filter);
                    $row[] = $count;
                    $timesheets_total_day_off += $count;
                }
                $row[] = $timesheets_total_day_off;
                $row[] = $total_leave - $timesheets_total_day_off;

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
            }
        }
    }

    /**
     * general summation
     * @param  int $month
     * @param  int $year
     * @param  int $staffid
     * @return 
     */
     public function general_summation($month, $year, $staffid){
        $result = 0;
        $data_leave = $this->timesheets_model->get_timesheets_day_leave_by_staffid($staffid,$year);
        foreach ($data_leave as $key => $value) {
                $start_month = $value['start_month'];
                $end_month = $value['end_month'];
                if($start_month == $end_month){
                    if($month == $start_month){
                        $result += $value['day_leave'];
                    }
                }
                if($start_month != $end_month){
                    if($month == $start_month){
                        $result += $value['day_start_for'];
                    }
                    if($month == $end_month){
                        $result += $value['day_end_for'];
                    }
                }
        }
        return $result;
     }

     /**
      * general public report
      * @return json
      */
     public function general_public_report(){
        if ($this->input->is_ajax_request()) {
        if($this->input->post()){
             $months_report = $this->input->post('months_filter');
            $position_filter = $this->input->post('position_filter');
            $department_filter = $this->input->post('department_filter');
            $staff_filter = $this->input->post('staff_filter');
            if($months_report == 'this_month'){

                $from_date = date('Y-m-01');
                $to_date   = date('Y-m-t');
            }

            if($months_report == '1'){ 
                $from_date = date('Y-m-01', strtotime('first day of last month'));
                $to_date   = date('Y-m-t', strtotime('last day of last month'));              
            }


            if($months_report == 'this_year'){
                $from_date = date('Y-m-d', strtotime(date('Y-01-01')));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }

            if($months_report == 'last_year'){
                $from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
                $to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));               
            }

            if($months_report == '3'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == '6'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }

            if($months_report == '12'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');

            }

            if($months_report == 'custom'){
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));                                      
            }



            $select = [
                
                'staffid',
                'firstname',

                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
                'staffid',
            ];
            $query = '';
            if(isset($position_filter)){
                $position_list = implode(',', $position_filter);
                $query .= ' job_position in ('.$position_list.') and';
            }
            if(isset($staff_filter)){
                $staffid_list = implode(',', $staff_filter);
                $query .= ' staffid in ('.$staffid_list.') and';
            }
            if(isset($department_filter)){
                $department_list = implode(',', $department_filter);
                $query .= ' staffid in (SELECT staffid FROM '.db_prefix().'staff_departments where departmentid in ('.$department_list.')) and';
            }

            $total_query = '';
            if(($query)&&($query != '')){
                $total_query = rtrim($query, ' and');
                $total_query = ' where '.$total_query;
            }
           
        
            $where              = [$total_query];


            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = db_prefix() . 'staff';
            $join         = [];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'staffid',
                'firstname',
                'lastname',

                'email',
            ]);

            $output  = $result['output'];
            $rResult = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['staffid'];
                $row[] = $aRow['firstname'].' '.$aRow['lastname'];


                $total = 0;
                $total2 = 0;
                $total3 = 0;
                $total4 = 0;
                $total7 = 0;
                $total8 = 0;
                $total9 = 0;
                $total10 = 0;

                 $data_timesheet = $this->timesheets_model->get_timesheet($aRow['staffid'],$from_date,$to_date);                 
                foreach ($data_timesheet as $key => $value) {
                    if(strtolower($value['type'])== 'w'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total+=$cal;
                        }
                    }

                    if(strtolower($value['type'])== 'al'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total2+=$cal;
                        }
                    }

                    if(strtolower($value['type'])== 'u'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total3+=$cal;
                        }
                    }

                    if(strtolower($value['type'])== 'ho'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total4+=$cal;
                        }
                    }
                    if(strtolower($value['type'])== 'b'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total7+=$cal;
                        }
                    }
                    if(strtolower($value['type'])== 'si'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total8+=$cal;
                        }
                    }
                     if(strtolower($value['type'])== 'm'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total9+=$cal;
                        }
                    }
                    if(strtolower($value['type'])== 'me'){
                        if(is_numeric($value['value'])){
                           $cal = $value['value']/8;
                           $total10+=$cal;
                        }
                    }
                }

                $row[] = ($total > 0) ? (float)number_format($total,2) : 0;
                $row[] = ($total2 > 0) ? (float)number_format($total2,2) : 0;
                $row[] = ($total3 > 0) ? (float)number_format($total3,2) : 0;
                $row[] = ($total4 > 0) ? (float)number_format($total4,2) : 0;
       
                $row[] = ($total7 > 0) ? (float)number_format($total7,2) : 0;
                $row[] = ($total8 > 0) ? (float)number_format($total8,2) : 0;
                $row[] = ($total9 > 0) ? (float)number_format($total9,2) : 0;
                $row[] = ($total10 > 0) ? (float)number_format($total10,2) : 0;

                $total_row = number_format($total - ($total2 + $total3 + $total4 + $total7 + $total8 + $total9 + $total10),2);
                $row[] = ($total_row > 0) ? $total_row : 0; 
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
         }
        }
     }
   

    /*mass delete for multiple feature*/
    public function timesheets_delete_bulk_action()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $total_deleted = 0;

        if ($this->input->post()) {

            $ids                   = $this->input->post('ids');
            $rel_type                   = $this->input->post('rel_type');

            /*check permission*/
            switch ($rel_type) {
                case 'timesheets_requisition':
                    if (!has_permission('timesheets_manage_requisition', '', 'delete') && !is_admin()) {
                        access_denied('manage_requisition');
                    }
                    break;
                default:
                    # code...
                    break;
            }
            
            /*delete data*/
            if ($this->input->post('mass_delete')) {
                if (is_array($ids)) {
                    foreach ($ids as $id) {

                            switch ($rel_type) {
                                case 'timesheets_requisition':
                                    if ($this->timesheets_model->delete_requisition($id)) {
                                        $total_deleted++;
                                        break;
                                    }else{
                                        break;
                                    }
                                default:
                                    # code...
                                    break;
                            }


                        }
                    }

                /*return result*/
                switch ($rel_type) {
                    case 'timesheets_requisition':
                        set_alert('success', _l('total_requisition'). ": " .$total_deleted);
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
    }

    /**
     * get rest time
     * @return json
     */
    public function get_rest_time(){
        $data = $this->input->post();
        $rest_time = $this->timesheets_model->get_rest_time($data['date']);
        echo json_encode($rest_time);
    }

    /**
     * delete additional timesheets
     * @param  int $id
     * @return redirect
     */
    public function delete_additional_timesheets($id)
    {
        $response = $this->timesheets_model->delete_additional_timesheets($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted'));
        } else {
            set_alert('warning', _l('problem_deleting'));
        }
        redirect(admin_url('timesheets/requisition_manage?tab=additional_timesheets'));
    }

    /**
     * table shiftwork
     * @return view
     */
    public function table_shiftwork()
    {
        $this->load->model('staff_model');

        $data['title']                 = _l('table_shiftwork');

        $data['departments'] = $this->departments_model->get();
        $data['staffs'] = $this->staff_model->get();
        $data['roles']         = $this->roles_model->get();
        $data['job_position'] = $this->roles_model->get();
        $data['positions'] = $this->roles_model->get();
        $data['shifts'] = $this->timesheets_model->get_shifts();

        $month      = date('m');
        $month_year = date('Y');

        $this->load->model('staff_model');
        $list_staff_id = [];
        if(is_admin()){
            $data_staff_list = $this->staff_model->get('', ['active' => 1]);
            foreach ($data_staff_list as $key => $value) {
               $list_staff_id[] = $value['staffid'];
            }            
        }
        else{
               $list_staff_id[] = get_staff_user_id();            
        }
        $data_hs = $this->set_col_tk(1, 31, $month, $month_year, true, $list_staff_id);
        $data['day_by_month']  = json_encode($data_hs->day_by_month);
        $data['list_data']  = json_encode($data_hs->list_data);
        $list_date = $this->timesheets_model->get_list_date($month_year.'-'.$month.'-01', $month_year.'-'.$month.'-31');  
        $data_object = [];
        foreach ($list_staff_id as $key => $value) {
            $row_data_staff = new stdClass();
            $row_data_staff->staffid = $value;
            $row_data_staff->staff = get_staff_full_name($value);
            $row_data_color = new stdClass();
            $row_data_color->staffid = '';
            $row_data_color->staff = '';

            
            foreach ($list_date as $kdbm => $day) {
               $shift_s = '';
               $color = '';
               $list_shift = $this->timesheets_model->get_shift_work_staff_by_date($value, $day);
               foreach ($list_shift as $ss) {
                    $data_shift_type = $this->timesheets_model->get_shift_type($ss);
                    if($data_shift_type){
                        if($color == ''){
                            $color = $data_shift_type->color;
                        }

                        $start_date = $data_shift_type->time_start_work;
                        $st_1 = explode(':',$start_date);
                        $st_time = $st_1[0].'h'.$st_1[1];

                        $end_date = $data_shift_type->time_end_work;
                        $e_2 = explode(':',$end_date);
                        $e_time = $e_2[0].'h'.$e_2[1];

                        $shift_s .= $data_shift_type->shift_type_name.' ('.$st_time.' - '.$e_time.')'."\n";
                    }  
                }
                $day_s = date('D d',strtotime($day));
                $row_data_staff->$day_s = $shift_s;
                $row_data_color->$day_s = $color;
            }
            $data_object[] = $row_data_staff;
            $data_color[] = $row_data_color;
        }
        $data['data_object'] = $data_object;
        $data['data_color'] = $data_color;
        $this->load->view('timekeeping/manage_table_shiftwork', $data);        
    }

    /**
     * reload shift work byfilter
     * @return json
     */
    public function reload_shift_work_byfilter(){
        $data = $this->input->post();
        $year = date('Y',strtotime(to_sql_date('01/'.$data['month'])));
        $g_month = date('m',strtotime(to_sql_date('01/'.$data['month'])));
        $department = $data['department'];
        $role = $data['role'];

        $data['month'] = date('m-Y',strtotime(to_sql_date('01/'.$data['month'])));
        $data['check_latch_timesheet'] = $this->timesheets_model->check_latch_timesheet($data['month']);

        $staff = '';
        if(isset($data['staff'])){
            $staff = $data['staff'];
        }
        $staff_querystring='';
        $role_querystring = '';
        $department_querystring='';
        $month_year_querystring='';
        $month = date('m');
        $month_year = date('Y');
        $cmonth = date('m');
        $cyear = date('Y');
        if($year != ''){
            $month_new = (string)$g_month; 
            if(strlen($month_new)==1){
                $month_new='0'.$month_new;
            }
            $month = $month_new;
            $month_year = (int)$year;
        }

        if($department != ''){
            $arrdepartment = $this->staff_model->get('', 'staffid in (select '.db_prefix().'staff_departments.staffid from '.db_prefix().'staff_departments where departmentid = '.$department.')');
            $temp = '';  
            foreach ($arrdepartment as $value) {
                $temp = $temp.$value['staffid'].',';
            }
            $temp = rtrim($temp,",");
            $department_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }

        if($role != ''){
            $role_querystring = 'role = "'.$role.'"';
        }

        if($staff != ''){
           $temp = '';
           $araylengh = count($staff);
           for ($i = 0; $i < $araylengh; $i++) { 
               $temp = $temp.$staff[$i];
               if($i != $araylengh-1){
                    $temp = $temp.',';
               }
           }
           $staff_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }else{
           $staff_querystring = 'FIND_IN_SET(staffid, "'.get_timesheets_option('timekeeping_applicable_object').'")';
        }

        $arrQuery = array($staff_querystring,$department_querystring, $month_year_querystring, $role_querystring);
        $newquerystring = '';
        foreach ($arrQuery as $string) {
            if($string != ''){
                $newquerystring = $newquerystring.$string.' AND ';
            }            
        }    

        $newquerystring=rtrim($newquerystring,"AND ");
        if($newquerystring == ''){
            $newquerystring = [];
        }
        
        $data['staff_row'] = [];
        $shift_staff = [];
        if($newquerystring != ''){

            $data['day_by_month'] = [];
            $data['day_by_month'][] = _l('staff');

            $data['set_col'] = [];
            $data['set_col'][] = ['data' => _l('staff'), 'type' => 'text'];

            $month      =  $g_month;
            $month_year =  $year;
            for ($d = 1; $d <= 31; $d++) {
                $time = mktime(12, 0, 0, $month, $d, $month_year);
                if (date('m', $time) == $month) {

                    array_push($data['day_by_month'], date('D d', $time));
                    array_push($data['set_col'],[ 'data' => date('D d', $time), 'type' => 'text']);
                   
                }
            }
     
        $data['staffs_setting'] = $this->timesheets_model->getStaff('', $newquerystring);

        foreach($data['staffs_setting'] as $ss){

            $work_shift['shift_s'] = $this->timesheets_model->get_data_edit_shift_by_staff($ss['staffid']);
            $shift_staff = [_l('staff') => $ss['firstname'].' '.$ss['lastname']];


                if(isset($work_shift['shift_s'])){
                    for ($d = 1; $d <= 31; $d++) {
                        $time = mktime(12, 0, 0, $g_month, $d, $year);
                        if (date('m', $time) == $g_month) {
                            if(date('N', $time) == 1){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['monday'] .' - '.$work_shift['shift_s'][1]['monday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['monday'].' - '.$work_shift['shift_s'][3]['monday'];
                            }elseif(date('N', $time) == 2){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['tuesday'] .' - '.$work_shift['shift_s'][1]['tuesday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['tuesday'].' - '.$work_shift['shift_s'][3]['tuesday'];
                            }elseif(date('N', $time) == 3){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['wednesday'] .' - '.$work_shift['shift_s'][1]['wednesday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['wednesday'].' - '.$work_shift['shift_s'][3]['wednesday'];
                            }elseif(date('N', $time) == 4){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['thursday'] .' - '.$work_shift['shift_s'][1]['thursday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['thursday'].' - '.$work_shift['shift_s'][3]['thursday'];
                            }elseif(date('N', $time) == 5){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['friday'] .' - '.$work_shift['shift_s'][1]['friday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['friday'].' - '.$work_shift['shift_s'][3]['friday'];
                            }elseif(date('N', $time) == 7){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['sunday'] .' - '.$work_shift['shift_s'][1]['sunday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['sunday'].' - '.$work_shift['shift_s'][3]['sunday'];
                            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 1){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['saturday_odd'] .' - '.$work_shift['shift_s'][1]['saturday_odd'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['saturday_odd'].' - '.$work_shift['shift_s'][3]['saturday_odd'];
                            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 0){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['saturday_even'] .' - '.$work_shift['shift_s'][1]['saturday_even'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['saturday_even'].' - '.$work_shift['shift_s'][3]['saturday_even'];
                            }
                        }
                    }
                }

                if($shift_staff != 'null' && $shift_staff !=''){

                    array_push($data['staff_row'], $shift_staff);

                }
        }
    }
            echo json_encode([
                'staff_row' => $data['staff_row'],
                'day_by_month_n' => $data['day_by_month'],
                'set_col_n' => $data['set_col'],
            ]);
            die;
    }


    /**
     * show detail timesheets mem 
     * @return
     */
    public function show_detail_timesheets_mem(){
        $data = $this->input->post();
        $year = date("Y");

        $day = $data['day'];  
        $month = implode($data['month']); 
        $member_id = $data['member_id'];

        $t = $day.'/'.$month;
        $time = strtotime(to_sql_date($t.'/'.date('Y')));
        $d =   date('Y-m-d', strtotime($year.'-'.$month.'-'.$day));
       
        
        $title = get_staff_full_name($member_id). ' - '. _d($d);
        $work_shift = $this->timesheets_model->get_data_edit_shift_by_staff($member_id);

        $data['value'] = explode('; ', $data['value']);
        $html = '';
        foreach ($data['value'] as $key => $value) {
            $value = explode(':', $value);
            if(isset($value[1]) && $value[1] > 0  || $value[0] == 'M' || $value[0] == 'HO'){
                switch ($value[0]) {
                    case 'L':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('p_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_p">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'W':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('W_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_w">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'U':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('A_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_a">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'HO':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('Le_timekeeping').'
                              </li>';
                        break;
                    case 'E':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('E_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_e">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'L':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('L_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_l">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'B':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('CT_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_l">'.round($value[1], 2).'</span>
                                  </li>';
                        break;    
                    case 'OM':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('OM_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'M':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('TS_timekeeping').'
                              </li>';
                        break;
                    case 'R':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('R_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'Ro':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('Ro_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'SI':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('CD_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;
                    case 'CO':
                    $html .= '<li class="list-group-item justify-content-between">
                              '._l('CO_timekeeping').'
                              <span class="badgetext badge badge-primary badge-pill style_u">'.round($value[1], 2).'</span>
                              </li>';
                        break;                    
                    case 'H':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('H_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_me">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'OT':
                    $html .= '<li class="list-group-item justify-content-between">
                                  '._l('OT_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_me">'.round($value[1], 2).'</span>
                                  </li>';
                        break;
                    case 'PN':
                        $html .= '<li class="list-group-item justify-content-between">
                                  '._l('PN_timekeeping').'
                                  <span class="badgetext badge badge-primary badge-pill style_p">'.round($value[1], 2).'</span>
                                  </li>';
                        break;                    
                }
            }
        }

        $ws_day ='';
        $data['staff_sc'] = $this->timesheets_model->get_staff_shift_applicable_object();
        $list_staff_sc = [];
        foreach ($data['staff_sc'] as $key => $value) {
            $list_staff_sc[] = $value['staffid'];
        }
        if(in_array($member_id, $list_staff_sc)){
            $shift = $this->timesheets_model->get_shiftwork_sc_date_and_staff($d, $member_id);
            if(isset($shift)){
                $work_shift = $this->timesheets_model->get_shift_sc($shift);

                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift->time_start_work.' - '.$work_shift->time_end_work.'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift->start_lunch_break_time.' - '.$work_shift->end_lunch_break_time.'</li>';
            }
        }else{
            if(date('N', $time) == 1){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['monday'] .' - '.$work_shift[1]['monday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['monday'].' - '.$work_shift[3]['monday'].'</li>';
            }elseif(date('N', $time) == 2){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['tuesday'] .' - '.$work_shift[1]['tuesday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['tuesday'].' - '.$work_shift[3]['tuesday'].'</li>';
            }elseif(date('N', $time) == 3){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['wednesday'] .' - '.$work_shift[1]['wednesday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['wednesday'].' - '.$work_shift[3]['wednesday'].'</li>';
            }elseif(date('N', $time) == 4){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['thursday'] .' - '.$work_shift[1]['thursday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['thursday'].' - '.$work_shift[3]['thursday'].'</li>';
            }elseif(date('N', $time) == 5){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['friday'] .' - '.$work_shift[1]['friday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['friday'].' - '.$work_shift[3]['friday'].'</li>';
            }elseif(date('N', $time) == 7){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['sunday'] .' - '.$work_shift[1]['sunday'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['sunday'].' - '.$work_shift[3]['sunday'].'</li>';
            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 1){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['saturday_odd'] .' - '.$work_shift[1]['saturday_odd'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['saturday_odd'].' - '.$work_shift[3]['saturday_odd'].'</li>';
            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 0){
                $ws_day = '<li class="list-group-item justify-content-between">'._l('work_times').': '.$work_shift[0]['saturday_even'] .' - '.$work_shift[1]['saturday_even'].'</li><li class="list-group-item justify-content-between">'._l('lunch_break').': '.$work_shift[2]['saturday_even'].' - '.$work_shift[3]['saturday_even'].'</li>';
            }
        }
        if($ws_day != ''){
            $html .= $ws_day;
        }

        $access_history_string = '';
        $staff_identifi = $this->timesheets_model->get_staff_identifi($member_id);
        $access_history = $this->timesheets_model->get_access_history($staff_identifi, $d);
        
        if($access_history){
            foreach ($access_history as $key => $value) {
                if($value['type'] == 'in'){
                    $access_history_string .= '<li class="list-group-item"><i class="fa fa-sign-in text-success" aria-hidden="true"></i> '._dt($value['time']).'</li>';
                }else{
                    $access_history_string .= '<li class="list-group-item"><i class="fa fa-sign-out text-danger" aria-hidden="true"></i> '._dt($value['time']).'</li>';
                }
            }
        }
        if($access_history_string != ''){
            $html .= '<li class="list-group-item justify-content-between"><ul class="list-group">
                            <li class="list-group-item active">'._l('access_history').'</li>
                            '.$access_history_string.'
                        </ul></li>';
        }
        echo json_encode([
            'title' => $title,
            'html' => $html,
        ]);
        die();
    }


    /**
     * Calculates the number days off.
     */
    public function calculate_number_days_off(){
        $data = $this->input->post();

        $start_time = $this->timesheets_model->format_date($data['start_time']);
        $end_time = $this->timesheets_model->format_date($data['end_time']);
        $list_date = $this->timesheets_model->get_list_date($start_time, $end_time);
        $list_af_date = [];
        foreach ($list_date as $key => $next_start_date) {
          $data_work_time = $this->timesheets_model->get_hour_shift_staff($data['staffid'], $next_start_date);
          $data_day_off = $this->timesheets_model->get_day_off_staff_by_date($data['staffid'], $next_start_date);
          if($data_work_time > 0 && count($data_day_off) == 0){
            $list_af_date[] = $next_start_date;
          }
        }
        $count = count($list_af_date);
        echo json_encode($count);
    }


    /**
     * [table_registration_leave description]
     * @return [type] [description]
     */
    public function table_registration_leave_by_staff()
    {
        $this->app->get_table_data(module_views_path('timesheets', 'table_registration_leave_by_staff'));
    }


    /**
     * [get_data_date_leave description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_data_date_leave()
    {
        $memberid = $this->input->post('memberid');
        $year_requisition = $this->input->post('year_requisition');

        $list = $this->timesheets_model->get_requisition_number_of_day_off($memberid, $year_requisition);

        echo json_encode([ 
            'total_day_off_in_year' => $list['total_day_off_in_year'],
            'total_day_off' => $list['total_day_off'],
            'total_day_off_allowed_in_year' => $list['total_day_off_allowed_in_year'],
            
        ]);
    }

    /**
     * shifts sc
     * @return [type] [description]
     */
    public function shifts_sc(){
        if($this->input->post()){
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $add = $this->timesheets_model->add_shift_sc($data); 
                if($add > 0){
                    $message = _l('added_successfully', _l('shift'));
                    set_alert('success',$message);
                }
                redirect(admin_url('timesheets/setting?group=shift'));
            }else{
                $id = $data['id'];
                unset($data['id']);
                $success = $this->timesheets_model->update_shift_sc($data,$id);
                if($success == true){
                    $message = _l('updated_successfully', _l('shift'));
                    set_alert('success', $message);
                }
                redirect(admin_url('timesheets/setting?group=shift'));
            }
        }   
    }

    /**
     * delete shift sc
     * @param  int $id
     * @return redirect
     */
    public function delete_shift_sc($id){
        if (!$id) {
            redirect(admin_url('timesheets/setting?group=shift'));
        }
        $response = $this->timesheets_model->delete_shift_sc($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('shift')));
        } else {
            set_alert('warning', _l('problem_deleting').' '. _l('shift'));
        }
        redirect(admin_url('timesheets/setting?group=shift'));
    }

    /**
     * setting shift
     * @return redirect
     */
    public function setting_shift(){
        $data = $this->input->post();
        $success = $this->timesheets_model->setting_shift($data);
        if($success){
            set_alert('success',_l('save_setting_success'));
        }else{
            set_alert('danger',_l('save_setting_fail'));
        }
        redirect(admin_url('timesheets/setting?group=shift_setting'));

    }

    /**
     * [shiftwork_sc description]
     * @return [type] [description]
     */
    public function shiftwork_sc(){
        $data = $this->input->post();
        $success = $this->timesheets_model->update_shiftwork_sc($data);
        if($success > 0){
            $message = _l('updated_successfully');
            set_alert('success', $message);
        }
        redirect(admin_url('timesheets/timekeeping?group=table_shiftwork_sc'));
    }

    /**
     * reload shiftwork sc by filter
     * @return json
     */
    public function reload_shiftwork_sc_byfilter(){
        $data = $this->input->post();
        $year = date('Y',strtotime(to_sql_date('01/'.$data['month'])));
        $g_month = date('m',strtotime(to_sql_date('01/'.$data['month'])));
        $days_in_month = cal_days_in_month(CAL_GREGORIAN,$g_month,$year);

        $month_filter = date('Y-m',strtotime(to_sql_date('01/'.$data['month'])));
        $querystring = '(select count(*) from '.db_prefix().'staff_contract where staff = '.db_prefix().'staff.staffid and DATE_FORMAT(start_valid, "%Y-%m") <="'.$month_filter.'" and IF(end_valid != null, DATE_FORMAT(end_valid, "%Y-%m") >="'.$month_filter.'",1=1)) > 0 and status_work="working" and active=1';
        $department = $data['department'];
        $job_position = $data['job_position'];

        $data['month'] = date('m-Y',strtotime(to_sql_date('01/'.$data['month'])));

        $staff = '';
        if(isset($data['staff'])){
            $staff = $data['staff'];
        }

        $staff_querystring='';
        $job_position_querystring = '';
        $department_querystring='';
        $month_year_querystring='';
        $month = date('m');
        $month_year = date('Y');
        $cmonth = date('m');
        $cyear = date('Y');
        if($year != ''){
            $month_new = (string)$g_month; 
            if(strlen($month_new)==1){
                $month_new='0'.$month_new;
            }
            $month = $month_new;
            $month_year = (int)$year;
        }    
       
        if($department != ''){
            $arrdepartment = $this->staff_model->get('', 'staffid in (select '.db_prefix().'staff_departments.staffid from '.db_prefix().'staff_departments where departmentid = '.$department.')');
            $temp = '';
            foreach ($arrdepartment as $value) {
                $temp = $temp.$value['staffid'].',';
            }
            $temp = rtrim($temp,",");
            $department_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }
        if($job_position != ''){
            $job_position_querystring = 'job_position = "'.$job_position.'"';
        }
        if($staff != ''){
           $temp = '';
           $araylengh = count($staff);
           for ($i = 0; $i < $araylengh; $i++) {
               $temp = $temp.$staff[$i];
               if($i != $araylengh-1){
                    $temp = $temp.',';
               }
           }
           $staff_querystring = 'FIND_IN_SET(staffid, "'.$temp.'")';
        }else{
            $staff_querystring = 'FIND_IN_SET(job_position, "'.get_timesheets_option('shift_applicable_object').'")';
            if(has_permission('timesheets_timekeeping','','view_own') && !is_admin()){
                $staff_querystring .= 'and staffid '. get_recursive_child_string(get_staff_user_id());
            }
        }

        $arrQuery = array($staff_querystring,$department_querystring, $month_year_querystring, $job_position_querystring, $querystring);
        $newquerystring = '';
            foreach ($arrQuery as $string) {
                if($string != ''){
                    $newquerystring = $newquerystring.$string.' AND ';
                }            
            }  

        $newquerystring=rtrim($newquerystring,"AND ");
        if($newquerystring == ''){
            $newquerystring = [];
        }
      
        $data['staff_row_sc'] = [];
        $data['days_in_month'] = $days_in_month;
    
        if($newquerystring != ''){
              $staffs = $this->timesheets_model->getStaff('', $newquerystring);
                $shift_staff = [];
                foreach($staffs as $s)
                {
                    $work_shift['shift_s'] = $this->timesheets_model->get_data_edit_shift_by_staff($s['staffid'], $month_year.'-'.$month.'-01');

                    $shift_staff = ['staffid' => $s['staffid'], _l('staff') => $s['firstname'].' '.$s['lastname']];
                    if(isset($work_shift['shift_s'])){
                        for ($d = 1; $d <= $days_in_month; $d++) {
                            $time = mktime(12, 0, 0, $month, $d, $month_year);
                            $shift_staff[date('d/m D', $time)] = $this->timesheets_model->get_shiftwork_sc_date_and_staff(date('Y-m-d', $time),$s['staffid']);
                        }
                    }
                    array_push($data['staff_row_sc'], $shift_staff);
                }
            }

            $data['set_col_sc'] = [];

            $data['shift_sc'] = $this->timesheets_model->get_shift_sc();
            $data['select_shift_sc'] = [];
            foreach ($data['shift_sc'] as $key => $value) {
                $node = [];
                $node['id'] = $value['id'];
                $node['label'] = $value['shift_symbol'];
                $data['select_shift_sc'][] = $node;
            }

            for ($d = 1; $d <= $days_in_month; $d++) {
                $time = mktime(12, 0, 0, $month, $d, $month_year);
                if (date('m', $time) == $month) {
                    $data['set_col_sc'][] = date('d/m D', $time);
                }
            }
            $data['set_col_sc'] = $data['set_col_sc'];

            $data['day_by_month'] = [];
            $data['day_by_month'][] = _l('staff_id');
            $data['day_by_month'][] = _l('staff');

            for ($d = 1; $d <= 31; $d++) {
                $time = mktime(12, 0, 0, $month, $d, $month_year);
                if (date('m', $time) == $month) {
                    array_push($data['day_by_month'], date('d/m D', $time));
                }
            }

            $data['day_by_month'] = $data['day_by_month'];

            echo json_encode([
                'arr' => $data['staff_row_sc'],
                'set_col_sc' =>  $data['set_col_sc'],
                'select_shift_sc' => $data['select_shift_sc'],
                'month' => $data['month'],
                'day_by_month' => $data['day_by_month'],
                'days_in_month' => $data['days_in_month'],
            ]);
            die;
    }

    /**
     * cancel request
     * @return 
     */
    public function cancel_request(){
        $data = $this->input->post();
        $success = false; 

        $success = $this->timesheets_model->cancel_request($data);

        echo json_encode([
            'success' => $success,
        ]);
        die();      
    }

    /**
     * add allocate shiftwork
     * @param string $id 
     */
    public function add_allocate_shiftwork($id = '')
    {
        $this->load->model('staff_model');

        $data['additional_timesheets_id'] = $this->input->get('additional_timesheets_id');
        $data['group'] = $this->input->get('group');
        $data['title']                 = _l($data['group']);
        
        $status_leave = $this->timesheets_model->get_option_val();

        $data['tab'][] = 'table_shiftwork';
        $data['tab'][] = 'allocate_shiftwork';


        if($data['group'] == ''){
            $data['group'] == 'table_shiftwork';
        }

        
        if($data['group'] == 'timesheets'){
            $data['check_latch_timesheet'] = $this->timesheets_model->check_latch_timesheet(date('m-Y'));
        }
        
        $data['departments'] = $this->departments_model->get();
        $data['staffs_li'] = $this->staff_model->get();
        $data['roles']         = $this->roles_model->get();
        $data['job_position'] = $this->roles_model->get();
        $data['positions'] = $this->roles_model->get();
        $data['additional_timesheets'] = $this->timesheets_model->get_additional_timesheets();
        $data['holiday'] = $this->timesheets_model->get_break_dates('holiday');
        $data['event_break'] = $this->timesheets_model->get_break_dates('event_break');
        $data['unexpected_break'] = $this->timesheets_model->get_break_dates('unexpected_break');
        $data['shifts'] = $this->timesheets_model->get_shifts();


        $data['day_by_month'] = [];
        $data['day_by_month_tk'] = [];
        $data['day_by_month'][] = _l('staff');
        $data['day_by_month_tk'][] = _l('staff_id');
        $data['day_by_month_tk'][] = _l('hr_code');
        $data['day_by_month_tk'][] = _l('staff');

        $data['set_col'] = [];
        $data['set_col_tk'] = [];
        $data['set_col_tk'][] = ['data' => _l('staff_id'), 'type' => 'text'];
        $data['set_col_tk'][] = ['data' => _l('hr_code'), 'type' => 'text','readOnly' => true, 'width' => 55];
        $data['set_col_tk'][] = ['data' => _l('staff'), 'type' => 'text','readOnly' => true,'width' => 200];
        $data['set_col'][] = ['data' => _l('staff'), 'type' => 'text'];

        $month      = date('m');
        $month_year = date('Y');
        for ($d = 1; $d <= 31; $d++) {
            $time = mktime(12, 0, 0, $month, $d, $month_year);
            if (date('m', $time) == $month) {
                array_push($data['day_by_month_tk'], date('D d', $time));
                array_push($data['day_by_month'], date('D d', $time));
                array_push($data['set_col'],[ 'data' => date('D d', $time), 'type' => 'text']);
                array_push($data['set_col_tk'],[ 'data' => date('D d', $time), 'type' => 'text']);
            }
        }

        $data['day_by_month'] = json_encode($data['day_by_month']);
        $data['day_by_month_tk'] = json_encode($data['day_by_month_tk']);

        $data['set_col'] = json_encode($data['set_col']);
        $data['set_col_tk'] = json_encode($data['set_col_tk']);

        $data_ts = $this->timesheets_model->get_timesheets_ts_by_month(date('m'), date('Y'));


        $data_map = [];
        foreach($data_ts as $ts){
            $staff_info = array();
            $staff_info['date'] = date('D d', strtotime($ts['date_work']));

            
            $ts_type = $this->timesheets_model->get_ts_by_date_and_staff($ts['date_work'],$ts['staff_id']);
            if(count($ts_type) <= 1){
                 $staff_info['ts'] = $ts['type'].':'.$ts['value'];
                
            }else{
                $str = '';
                foreach($ts_type as $tp){
                    if($str == ''){
                        $str .= $tp['type'].':'.$tp['value'];
                    }else{
                        $str .= '-'.$tp['type'].':'.$tp['value'];
                    }
                }
                $staff_info['ts'] = $str;
            }
              
            if(!isset($data_map[$ts['staff_id']])){
                $data_map[$ts['staff_id']] = array();
            }
            $data_map[$ts['staff_id']][$staff_info['date']] = $staff_info;
        }
  

        $data['staff_row_tk'] = [];
        $data['staff_row'] = [];
        $staffs = $this->timesheets_model->get_staff_timekeeping_applicable_object();
        $data['staffs_setting'] = $this->staff_model->get();
        $data['staffs'] = $staffs;

        $shift_staff = [];
        foreach($data['staffs_setting'] as $ss){
            $work_shift['shift_s'] = $this->timesheets_model->get_data_edit_shift_by_staff($ss['staffid']);
            $shift_staff = [_l('staff') => $ss['firstname'].' '.$ss['lastname']];
            if(isset($work_shift['shift_s'])){
                if($work_shift['shift_s']){
                    for ($d = 1; $d <= 31; $d++) {
                        $time = mktime(12, 0, 0, $month, $d, $month_year);
                        if (date('m', $time) == $month) {
                            if(date('N', $time) == 1){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['monday'] .' - '.$work_shift['shift_s'][1]['monday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['monday'].' - '.$work_shift['shift_s'][3]['monday'];
                            }elseif(date('N', $time) == 2){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['tuesday'] .' - '.$work_shift['shift_s'][1]['tuesday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['tuesday'].' - '.$work_shift['shift_s'][3]['tuesday'];
                            }elseif(date('N', $time) == 3){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['wednesday'] .' - '.$work_shift['shift_s'][1]['wednesday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['wednesday'].' - '.$work_shift['shift_s'][3]['wednesday'];
                            }elseif(date('N', $time) == 4){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['thursday'] .' - '.$work_shift['shift_s'][1]['thursday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['thursday'].' - '.$work_shift['shift_s'][3]['thursday'];
                            }elseif(date('N', $time) == 5){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['friday'] .' - '.$work_shift['shift_s'][1]['friday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['friday'].' - '.$work_shift['shift_s'][3]['friday'];
                            }elseif(date('N', $time) == 7){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['sunday'] .' - '.$work_shift['shift_s'][1]['sunday'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['sunday'].' - '.$work_shift['shift_s'][3]['sunday'];
                            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 1){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['saturday_odd'] .' - '.$work_shift['shift_s'][1]['saturday_odd'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['saturday_odd'].' - '.$work_shift['shift_s'][3]['saturday_odd'];
                            }elseif(date('N', $time) == 6 && (date('d', $time)%2) == 0){
                                $shift_staff[date('D d', $time)] = _l('time_working').': '.$work_shift['shift_s'][0]['saturday_even'] .' - '.$work_shift['shift_s'][1]['saturday_even'].'  '._l('time_lunch').': '.$work_shift['shift_s'][2]['saturday_even'].' - '.$work_shift['shift_s'][3]['saturday_even'];
                            }
                        }
                    }
                }
            }
            array_push($data['staff_row'], $shift_staff);
        }
        foreach($staffs as $s){    
            $ts_date = '';
            $ts_ts = '';
            $result_tb = [];
            if(isset($data_map[$s['staffid']])){
                foreach ($data_map[$s['staffid']] as $key => $value) {
                    $ts_date = $data_map[$s['staffid']][$key]['date'];
                    $ts_ts =  $data_map[$s['staffid']][$key]['ts'];
                    $result_tb[] = [$ts_date => $ts_ts];
                }
               
            }
            $dt_ts = [];
            $dt_ts = [_l('staff_id') => $s['staffid'],_l('hr_code') => $s['staff_identifi'],_l('staff') => $s['firstname'].' '.$s['lastname']];
            foreach ($result_tb as $key => $rs) {
                foreach ($rs as $day => $val) {
                   $dt_ts[$day] = $val;
                }
            }
            array_push($data['staff_row_tk'], $dt_ts);            
        }

        $data['tabs']['view'] = 'timekeeping/'.$data['group'];
        $this->load->view('timekeeping/add_allocate_shiftwork', $data);
        
    }
    
    /**
     * get date leave 
     * @return date
     */
    public function get_date_leave(){
        $data = $this->input->post();
        $staffid = $data['staffid'];
        $number_of_days = $data['number_of_days'];
        $start_date = date('Y-m-d');            
        if(!$this->timesheets_model->check_format_date_ymd($data['startdate'])){
            $start_date = to_sql_date($data['startdate']);
        }else{
            $start_date = $data['startdate'];            
        }
        $ceiling_number_of_days = ceil($number_of_days);

        $list_date = [];
        $i = 0; 
        while(count($list_date) != $ceiling_number_of_days) {

          $next_start_date = date('Y-m-d', strtotime($start_date .' +'.$i.' day'));
          $data_work_time = $this->timesheets_model->get_hour_shift_staff($staffid, $next_start_date);
          $data_day_off = $this->timesheets_model->get_day_off_staff_by_date($staffid, $next_start_date);
          if($data_work_time > 0 && count($data_day_off) == 0){
            $list_date[] = $next_start_date;
          }
          $i++;
          if($i > 100){
            break;
          }
        }
        $end_date = ($list_date[count($list_date) - 1]);
        echo json_encode([
            'end_date' => _d($end_date)
        ]);
        die;    
    }
    /**
     * table shift type
     * @return json 
     */
    public function table_shift_type(){
          if ($this->input->is_ajax_request()) {
            if($this->input->post()){
                $staff_filter = $this->input->post('bed_category_filter'); 
                $query = '';
                if($staff_filter!=''){
                        $query = ' where bed_category_id in ('.implode(',', $staff_filter).')';
                } 
                $select = [
                      'id',
                      'shift_type_name',         
                      'description',
                      'id'          
                ];
                $where              = [(($query!='')?$query:'')];


                $aColumns     = $select;
                $sIndexColumn = 'id';
                $sTable       = db_prefix() . 'shift_type';
                $join         = [];

                $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                      'id',
                      'shift_type_name',
                      'color',
                      'time_start',
                      'time_end',
                      'time_start_work',
                      'time_end_work',
                      'start_lunch_break_time',
                      'end_lunch_break_time',
                      'description',  
                ]);


                $output  = $result['output'];
                $rResult = $result['rResult'];
                foreach ($rResult as $aRow) {
                    $row = [];
                    $row[] = $aRow['id'];             
                    $row[] = $aRow['shift_type_name'];   
                    $row[] = $aRow['description'];

                    $option = '';
                    if(is_admin()){
                      $option .= '<a href="#" class="btn btn-default btn-icon" onclick="edit_shift_type(this); return false;" data-id="'.$aRow['id'].'" data-shift_type_name="'.$aRow['shift_type_name'].'" data-color="'.$aRow['color'].'" data-time_start="'.$aRow['time_start'].'" data-time_end="'.$aRow['time_end'].'" data-time_start_work="'.$aRow['time_start_work'].'" data-time_end_work="'.$aRow['time_end_work'].'" data-start_lunch_break_time="'.$aRow['start_lunch_break_time'].'" data-end_lunch_break_time="'.$aRow['end_lunch_break_time'].'" data-description="'.$aRow['description'].'" >';
                      $option .= '<i class="fa fa-edit"></i>';
                      $option .= '</a>';
                      $option .= '<a href="' . admin_url('timesheets/delete_shift_type/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete">';
                      $option .= '<i class="fa fa-remove"></i>';
                      $option .= '</a>';
                    }

                    $row[] = $option; 
                    $output['aaData'][] = $row;                                      
                }
                
                echo json_encode($output);
                die();
             }
        }
    }
    public function manage_shift_type(){
        $data['title'] = _l('manage_shift_type');
        if($this->input->post()){
            $data = $this->input->post(); 
            $data['datecreated'] = date('Y-m-d');
            $data['add_from'] = get_staff_user_id();
            $message = '';
            if($data['id'] == ''){
                $result = $this->timesheets_model->add_shift_type($data);
                if($result > 0){
                    $message = _l('added_successfully');
                }                            
            }
            else{

                $success = $this->timesheets_model->update_shift_type($data);
                if($success == true){
                    $message = _l('updated_successfully');
                }                
            }
            set_alert('success', $message);
            redirect(admin_url('timesheets/manage_shift_type'));  
        }
        $this->load->view('manage_shift_type', $data);
    }
    public function shift_management(){
        $data['title'] = _l('shift_management');
        $this->load->view('shift_management', $data);
    }

    public function set_col_tk($from_day, $to_day, $month, $month_year, $absolute_type = true, $stafflist = '', $work_shift_id = ''){    
        $list_data = [];
        $data_day_by_month = [];
        $data_time = [];
        $data_day_by_month_tk = [];
        $data_set_col = [];        
        $data_set_col_tk = [];
        $data_object = [];
        $data_shift_type = $this->timesheets_model->get_shift_type();
        $new_list_shift = [];
       
       
        if($absolute_type == true){           
            if($stafflist){                        
                array_push($data_day_by_month, 'staffid');
                array_push($data_day_by_month, _l('staff'));
                array_push($list_data,[
                              'data' => 'staffid', 'type' => 'text','readOnly' => true
                ]);
                array_push($list_data,[
                              'data' => 'staff', 'type' => 'text','readOnly' => true
                ]);
            } 
            for ($d = $from_day; $d <= $to_day; $d++) {
                $time = mktime(12, 0, 0, $month, $d, $month_year);
                if (date('m', $time) == $month) {                  
                    array_push($data_time, $time);
                    array_push($data_day_by_month_tk, date('D d', $time));
                    array_push($data_day_by_month, date('D d', $time));
                    array_push($data_set_col,[ 'data' => date('D d', $time), 'type' => 'text']);
                    array_push($data_set_col_tk,[ 'data' => date('D d', $time), 'type' => 'text']);

                    array_push($data_set_col_tk,[ 'data' => date('D d', $time), 'type' => 'text']);
                    array_push($list_data,[
                          'data' =>  date('D d', $time),
                          'editor' => "chosen",
                          'chosenOptions' => [
                            'data' => $new_list_shift
                          ]
                  ]);
               }
            }
            if($stafflist){
                $this->load->model('staff_model');                        
                foreach ($stafflist as $key => $value) {
                    $data_staff = $this->staff_model->get($value);
                    $staff_id = $data_staff->staffid;
                    $staff_name = $data_staff->firstname.' '.$data_staff->lastname;
                    $data_shift_staff = [];

                    $row_data_staff = new stdClass();
                    $row_data_staff->staffid = $staff_id;
                    $row_data_staff->staff = $staff_name;
                    foreach ($data_time as $k => $time) {
                        $times = date('D d', $time);
                        $date_s = date('Y-m-d', $time);
                        $row_data_staff->$times = $this->timesheets_model->get_id_shift_type_by_date_and_master_id($staff_id, $date_s, $work_shift_id);
                    }
                    $data_object[] = $row_data_staff;
                }
             }
             else{
                    $row_data_staff = new stdClass();
                    foreach ($data_time as $k => $time) {
                        $times = date('D d', $time);
                        $date_s = date('Y-m-d', $time);
                        $id_shift_type = '';
                        $staff_id = '';
                        $first_staff = $this->timesheets_model->get_first_staff_work_shift($work_shift_id);
                        if($first_staff){
                            $staff_id = $first_staff->staff_id;
                        }
                        $data_s = $this->timesheets_model->get_id_shift_type_by_date_and_master_id($staff_id, $date_s, $work_shift_id);
                        $row_data_staff->$times = $data_s;
                    }
                    $data_object[] = $row_data_staff;
             }
          }
          else{
            $day_list = ["Mon","Tue","Web","Thu","Fri","Sat","Sun"];
            if($stafflist){                        
                array_push($data_day_by_month, 'staffid');
                array_push($data_day_by_month, _l('staff'));
                array_push($list_data,[
                              'data' => 'staffid', 'type' => 'text','readOnly' => true
                ]);
                array_push($list_data,[
                              'data' => 'staff', 'type' => 'text','readOnly' => true
                ]);
            }                
            foreach ($day_list as $key => $value) {
                array_push($data_day_by_month_tk, $value);
                array_push($data_day_by_month, $value);
                array_push($data_set_col,[ 'data' => $value, 'type' => 'text']);
                array_push($data_set_col_tk,[ 'data' => $value, 'type' => 'text']);
                array_push($list_data,[
                      'data' =>  $value,
                      'editor' => "chosen",
                      'chosenOptions' => [
                        'data' => $new_list_shift
                      ]
                ]);
            }     
            if($stafflist){
                $this->load->model('staff_model');                        
                foreach ($stafflist as $key => $value) {
                    $data_staff = $this->staff_model->get($value);
                    $staff_id = $data_staff->staffid;
                    $staff_name = $data_staff->firstname.' '.$data_staff->lastname;

                    $data_shift_staff = [];
                    $row_data_staff = new stdClass();
                    $row_data_staff->staffid = $staff_id;
                    $row_data_staff->staff = $staff_name;
                    for($i = 1; $i <= 7; $i++){
                        $shift_type_id = '';
                        $data_shift_type = $this->timesheets_model->get_shift_type_id_by_number_day($work_shift_id, $i, $staff_id);
                        if($data_shift_type){
                            $shift_type_id = $data_shift_type->shift_id;
                        }
                         $day_name = $day_list[$i-1];
                        $row_data_staff->$day_name = $shift_type_id;
                    }
                    $data_object[] = $row_data_staff;
                }
            } 
            else{
                $row_data_staff = new stdClass();
                for($i = 1; $i <= 7; $i++){
                    $shift_type_id = '';
                    $data_shift_type = $this->timesheets_model->get_shift_type_id_by_number_day($work_shift_id, $i);

                    if($data_shift_type){
                        $shift_type_id = $data_shift_type->shift_id;
                    }
                    $day_name = $day_list[$i-1];
                    $row_data_staff->$day_name = $shift_type_id;
                }
                $data_object[] = $row_data_staff;
            }  
        }        
        $obj = new stdClass();
        $obj->day_by_month = $data_day_by_month;
        $obj->day_by_month_tk = $data_day_by_month_tk;
        $obj->set_col = $data_set_col;
        $obj->set_col_tk = $data_set_col_tk;
        $obj->list_data = $list_data;
        $obj->data_object = $data_object;
        return $obj;
    }
    /**
     * add allocation shiftwork
     * @param integer $id 
     * @param view 
     */
    public function add_allocation_shiftwork($id = ''){
        $data['title'] = _l('new_shift');
        $data['departments'] = $this->departments_model->get();
        $data['staffs'] = $this->staff_model->get();
        $data['roles']         = $this->roles_model->get();     

        $month      = date('m');
        $month_year = date('Y');
        $data_hs = $this->set_col_tk(1,8, $month, $month_year,false);
        $data['head_data']  = $data_hs->day_by_month;
        $data['list_data']  = $data_hs->list_data;
        $data['data_object'] = $data_hs->data_object;

        $data_shift_type = $this->timesheets_model->get_shift_type();
        $new_list_shift = [];
        foreach ($data_shift_type as $key => $value) {
            $start_date = $value['time_start_work'];
            $st_1 = explode(':',$start_date);
            $st_time = $st_1[0].'h'.$st_1[1];

            $end_date = $value['time_end_work'];
            $e_2 = explode(':',$end_date);
            $e_time = $e_2[0].'h'.$e_2[1];

            array_push($new_list_shift, array('id' => $value['id'],
             'label' => $value['shift_type_name'].' ('.$st_time.' - '.$e_time.')'             

            ));
        }
        if($id != ''){
            $data['word_shift'] = $this->timesheets_model->get_workshiftms($id);
            $month      = date('m');
            $month_year = date('Y');
            $department = $data['word_shift']->department;
            $role = $data['word_shift']->position;
            if($data['word_shift']->staff!=''){
                $staff = explode(',',$data['word_shift']->staff);
            }
            else{
                $staff = '';
            }
            $from_date = $data['word_shift']->from_date;
            $to_date = $data['word_shift']->to_date;

            $type_shiftwork = $this->input->post('type_shiftwork');
            if($data['word_shift']->type_shiftwork == 'repeat_periodically'){
                $data_hs = $this->set_col_tk(1,8, $month, $month_year,false,$staff, $id);
                $data['head_data'] = $data_hs->day_by_month;
                $data['list_data'] = $data_hs->list_data;
                $data['data_object'] = $data_hs->data_object;
            }

            if($data['word_shift']->type_shiftwork == 'by_absolute_time'){
                $start_month = 1;
                $end_month = 31;
                if($from_date){
                    $temp = explode('-', $from_date);
                    $start_month = $temp[2];
                }
                if($to_date){
                    $temp = explode('-', $to_date);
                    $end_month = $temp[2];
                }
                $data_hs = $this->set_col_tk($start_month, $end_month, $month, $month_year,true,$staff, $id);
                $data['head_data'] = $data_hs->day_by_month;
                $data['list_data'] = $data_hs->list_data;
                $data['data_object'] = $data_hs->data_object;
            }
            $data['title'] = _l('edit_shift');
        }
        $data['shift_type'] = $new_list_shift;
        $this->load->view('timekeeping/add_allocate_shiftwork', $data);
    }

    public function delete_shift_type($id){
        if($id != ''){
           $message = '';
           $result = $this->timesheets_model->delete_shift_type($id);
           if($result == true){
                $message = _l('deleted');
           }
           else{
                $message = _l('problem_deleting');
           }
           set_alert('success', $message);           
           redirect(admin_url('timesheets/manage_shift_type'));  
        }
    }

    function get_hanson_shiftwork(){

            $month      = date('m');
            $month_year = date('Y');
            $department = $this->input->post('department');
            $role = $this->input->post('role');
            $staff = $this->input->post('staff');
            $from_date = $this->input->post('from_date');
            $to_date = $this->input->post('to_date');

            $type_shiftwork = $this->input->post('type_shiftwork');
            if($type_shiftwork == 'repeat_periodically'){
                $data_hs = $this->set_col_tk(1,8, $month, $month_year,false,$staff);
                echo json_encode([
                    'head_data' => $data_hs->day_by_month,
                    'list_data' => $data_hs->list_data,
                    'data_object' => $data_hs->data_object
                ]);
            }
            if($type_shiftwork == 'by_absolute_time'){
                $start_month = 1;
                $end_month = 31;
                if(!$this->timesheets_model->check_format_date_ymd($from_date)){
                    $from_date = to_sql_date($from_date);
                }
                if(!$this->timesheets_model->check_format_date_ymd($to_date)){
                    $to_date = to_sql_date($to_date);
                }
                if($from_date){
                    $temp = explode('-', $from_date);
                    $start_month = $temp[2];
                }
                if($to_date){
                    $temp = explode('-', $to_date);
                    $end_month = $temp[2];
                }
                $data_hs = $this->set_col_tk($start_month, $end_month, $month, $month_year,true,$staff);
                echo json_encode([
                    'head_data' => $data_hs->day_by_month,
                    'list_data' => $data_hs->list_data,
                    'data_object' => $data_hs->data_object
                ]);
            }
            die;
    }

    function get_custom_type_shiftwork(){
            $department = $this->input->post('department');
            $role = $this->input->post('role');
            $staff = $this->input->post('staff');
    }
    /**
     * shift table
     * @return json 
     */
    function shift_table(){
          if ($this->input->is_ajax_request()) {
            if($this->input->post()){
               $this->load->model('departments_model');
               $this->load->model('roles_model');

                $query = '';
 
                $select = [
                    'from_date',
                    'to_date',
                    'department',
                    'position',
                    'staff',
                    'date_create',
                    'add_from'
                ];

                $where              = [(($query!='')?' where '.rtrim($query,' and '):'')];

                $aColumns     = $select;
                $sIndexColumn = 'id';
                $sTable       = db_prefix() . 'work_shift';
                $join         = [];

                $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                      'id',
                      'shift_code',
                      'shift_name',
                      'shift_type',
                      'department',
                      'position',
                      'staff',
                      'add_from',
                      'date_create',
                      'from_date',
                      'to_date',
                ]);


                $output  = $result['output'];
                $rResult = $result['rResult'];
                foreach ($rResult as $aRow) {
                    $row = [];
                    $row[] = _d($aRow['from_date']); 
                    $row[] = _d($aRow['to_date']); 
                    $department_name = '';
                    if($aRow['department']!=0 && $aRow['department']!=''){
                        $departmentid = explode(',', $aRow['department']);
                        foreach ($departmentid as $key => $value) {
                           $data_department = $this->departments_model->get($value);
                           if($data_department){
                                $department_name .= $data_department->name.', ';
                           }
                        }
                    }
                    if($department_name!=''){
                        $department_name = rtrim($department_name,', ');
                    }

                    $position_name = '';
                    if($aRow['position'] != 0 && $aRow['position']!=''){
                        $positionid = explode(',', $aRow['position']);
                        foreach ($positionid as $key => $value) {
                           $data_position = $this->roles_model->get($value);
                           if($data_position){
                                $position_name .= $data_position->name.', ';
                           }
                        }
                    }
                    if($position_name!=''){
                        $position_name = rtrim($position_name,', ');
                    }

                    $staff_name = '';
                    if($aRow['staff'] != 0 && $aRow['staff']!=''){
                        $staffid = explode(',', $aRow['staff']);
                        foreach ($staffid as $key => $value) {
                                $staff_name .= get_staff_full_name($value).', ';
                        }
                    }

                    if($staff_name!=''){
                        $staff_name = rtrim($staff_name,', ');
                    }

                    if($department_name != ''){
                        $row[] = $department_name; 
                    }else{
                        $row[] = _l('all'); 
                    }

                    if($position_name != ''){
                        $row[] = $position_name; 
                    }else{
                        $row[] = _l('all'); 
                    }

                    if($staff_name != ''){
                        $row[] = $staff_name; 
                    }else{
                        $row[] = _l('all'); 
                    }

                    $row[] = _d($aRow['date_create']); 

                    $option = '';
                    $option .= '<a href="' . admin_url('timesheets/add_allocation_shiftwork/' . $aRow['id']) . '" class="btn btn-default btn-icon">';
                    $option .= '<i class="fa fa-pencil-square-o"></i>';
                    $option .= '</a>';

                    $option .= '<a href="' . admin_url('timesheets/delete_shift/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete">';
                    $option .= '<i class="fa fa-remove"></i>';
                    $option .= '</a>';

                    $row[] = $option; 

                    $output['aaData'][] = $row;                                      
                }
                
                echo json_encode($output);
                die();
             }
       }
    }
    /**
     * check in timesheet
     */
    public function check_in_ts(){
        if($this->input->post()){
            $data = $this->input->post();
            $re = $this->timesheets_model->check_in($data);
            if($re > 0){
                set_alert('success',_l('check_in_successfull'));            
            }
            else{
                set_alert('warning',_l('check_in_not_successfull'));            
            }
            redirect(admin_url('timesheets/timekeeping?group=timesheets'));
        }
    }
    /**
     * check out timesheet 
     */
    public function check_out_ts(){
        if($this->input->post()){
            $data = $this->input->post();
            $re = $this->timesheets_model->check_out($data);
            if($re > 0){
                set_alert('success',_l('check_out_successfull'));            
            }
            else{
                set_alert('warning',_l('check_out_not_successfull'));            
            }
            redirect(admin_url('timesheets/timekeeping?group=timesheets'));
        }
    }
    /**
     * get leave setting
     * @return json 
     */
    public function get_leave_setting(){
            $new_array_obj = [];
            $data = $this->input->post();
            $staffid = isset($data['staffid']) ? $data['staffid'] : '';
            $departmentid = isset($data['departmentid']) ? $data['departmentid'] : '';
            $roleid = isset($data['roleid']) ? $data['roleid'] : '';
            $query = '';
            if($staffid != ''){
                $list = implode(',', $staffid);
                $query .= ' staffid in ('.$list.') and';
            }
            if($departmentid != ''){
                $list = implode(',', $departmentid);
                $query .= ' staffid in (SELECT staffid FROM '.db_prefix().'staff_departments where departmentid in ('.$list.')) and';
            }
            if($roleid != ''){
                $list = implode(',', $roleid);
                $query .= ' role in ('.$list.') and';
            }       
            $query = rtrim($query, ' and');
            $data_staff = $this->timesheets_model->get_staff_query($query);
            foreach ($data_staff as $key => $value) {
                $department_name = '';
                $data_department = $this->departments_model->get_staff_departments($value['staffid']);
                if($data_department){
                    $department_name = $data_department[0]['name'];
                }

                $role_name = '';
                if($value['role']!=''){
                    $data_role = $this->timesheets_model->get_role($value['role']);
                    if(isset($data_role)){
                        if($data_role){
                            if(isset($data_role->name)){
                                $role_name = $data_role->name;
                            }
                        }
                    }
                }
                $day = 0;
                $data_leave = $this->timesheets_model->get_day_off($value['staffid']);
                if($data_leave){
                    if($data_leave->total != ''){
                        $day = $data_leave->total;
                    }
                }
                array_push($new_array_obj, array('staffid' => $value['staffid'], 'staff'=>  $value['firstname'].' '.$value['lastname'], 'department' => $department_name, 'role' => $role_name,'maximum_leave_of_the_year' => $day));
            }
            echo json_encode([
                'data' => $new_array_obj
            ]);
    }
   
   /**
    * requisition report
    * @return 
    */
    public function requisition_report(){
         if ($this->input->is_ajax_request()) {
        if($this->input->post()){
            $months_report = $this->input->post('months_filter');
            $position_filter = $this->input->post('position_filter');
            $department_filter = $this->input->post('department_filter');
            $staff_filter = $this->input->post('staff_filter');
             if($months_report == 'this_month'){
                $from_date = date('Y-m-01');
                $to_date   = date('Y-m-t');
            }//thang nay
            if($months_report == '1'){ 
                $from_date = date('Y-m-01', strtotime('first day of last month'));
                $to_date   = date('Y-m-t', strtotime('last day of last month'));       
            }//Trang truoc
            if($months_report == 'this_year'){
                $from_date = date('Y-m-d', strtotime(date('Y-01-01')));
                $to_date = date('Y-m-d', strtotime(date('Y-12-31')));
            }//nam nay
            if($months_report == 'last_year'){
                $from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
                $to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));       
            }//năm truoc

            if($months_report == '3'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }//3 thang qua
            if($months_report == '6'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');
            }//6 thang qua
            if($months_report == '12'){
                $months_report--;
                $from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
                $to_date   = date('Y-m-t');

            }//12 thang qua
            if($months_report == 'custom'){
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));                                    
            }//12 thang qua 


            $select = [
                'staff_id',
                'subject',
                'start_time',
                'end_time',
                'reason',
                'rel_type',               
            ];

            $query = '';
           
            if(isset($staff_filter)){
                $staffid_list = implode(',', $staff_filter);
                $query .= ' staff_id in ('.$staffid_list.') and';
            }
            if(isset($department_filter)){
                $department_list = implode(',', $department_filter);
                $query .= ' staff_id in (SELECT staffid FROM '.db_prefix().'staff_departments where departmentid in ('.$department_list.')) and';
            }

            if(isset($months_report)){
                $query .= ' date_format(start_time, "%Y-%m-%d") >= "'.$from_date.'" AND date_format(end_time, "%Y-%m-%d") <= "'.$to_date.'"';

            }


            $total_query = '';
            if(($query)&&($query != '')){
                $total_query = rtrim($query, ' and');
                $total_query = ' where '.$total_query;
            }
            $where              = [$total_query];


            $aColumns     = $select;

            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'timesheets_requisition_leave';
            $join         = [];

            /*get requisition approval*/
            $where_status = ' AND status = "1"';
            array_push($where, $where_status);

            if(isset($position_filter)){

                $position_list = implode(',', $position_filter);

                $where[] = 'and '.db_prefix().'timesheets_requisition_leave.staff_id IN (SELECT  staffid FROM '.db_prefix().'staff where job_position  IN ('.$position_list.'))';

            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'id',
                'subject',
                'start_time',
                'end_time',
                'reason',
                'rel_type',

            ]);


            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {

                $row = [];
                $row[] = $aRow['staff_id'];

                $row[] = '<a data-toggle="tooltip" data-title="' .get_staff_full_name($aRow['staff_id']) . '" href="' . admin_url('profile/' . $aRow['staff_id']) . '">' . staff_profile_image($aRow['staff_id'], [
                    'staff-profile-image-small',
                    ]) . ' ' . get_staff_full_name($aRow['staff_id']) . '</a><span class="hide">' . get_staff_full_name($aRow['staff_id']) . '</span>';

                $row[] = $aRow['subject'];
                $row[] = _d($aRow['start_time']);  
                $row[] = _d($aRow['end_time']);  
                $row[] = $aRow['reason'];  

                if($aRow['rel_type'] == 1){
                     $row[] = '<p>'. _l('Leave') .'</p>';
                   }else if($aRow['rel_type'] == 2 ){
                     $row[] = '<p>'. _l('Late_early') .'</p>';
                   }else if($aRow['rel_type'] == 3 ){
                     $row[] = '<p>'. _l('Go_out') .'</p>';
                   }else if($aRow['rel_type'] == 4 ){
                     $row[] = '<p>'. _l('Go_on_bussiness') .'</p>';
                   }else{
                    $row[] = '<p>'. _l('quit_job') .'</p>';
                   }
            $output['aaData'][] = $row;

            }
            
            echo json_encode($output);
            die();
         }
        }
    }

    /**
     * import timesheets
     * @return
     */
    public function import_timesheets(){
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }
        $total_row_false = 0; 
        $total_rows = 0;
        $dataerror  = 0;
        $total_row_success = 0;
        if (isset($_FILES['file_timesheets']['name']) && $_FILES['file_timesheets']['name'] != '') {

            // Get the temp file path
            $tmpFilePath = $_FILES['file_timesheets']['tmp_name'];                
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                $tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

                if (!file_exists(TEMP_FOLDER)) {
                    mkdir(TEMP_FOLDER, 0755);
                }

                if (!file_exists($tmpDir)) {
                    mkdir($tmpDir, 0755);
                }

                // Setup our new file path
                $newFilePath = $tmpDir . $_FILES['file_timesheets']['name'];                    

                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $import_result = true;                        
                    $rows          = [];
                    
                    $objReader = new PHPExcel_Reader_Excel2007();
                    $objReader->setReadDataOnly(true);
                    $objPHPExcel = $objReader->load($newFilePath);
                    $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();   
                    $sheet = $objPHPExcel->getActiveSheet();

                    $styleArray = array(
                    'font'  => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'ff0000')
                        
                    ));
                    $numRow = 2;
                    $total_rows = 0;
                    
                    foreach($rowIterator as $row){
                        $rowIndex = $row->getRowIndex ();
                        if($rowIndex > 1){
                            $rd = array();
                            $flag = 0;

                            $string_error ='';

                            $value_staffid = $sheet->getCell('A' . $rowIndex)->getValue();
                            $value_time_in = $sheet->getCell('B' . $rowIndex)->getValue();
                            $value_time_out = $sheet->getCell('C' . $rowIndex)->getValue();

                            if(is_null($value_staffid) == true){
                                $string_error .=_l('staffid'). _l('not_yet_entered');
                                $flag = 1;
                            }

                            if(is_null($value_time_in) == true){
                                $string_error .=_l('time_in'). _l('not_yet_entered');
                                $flag = 1;
                            }
                            if(is_null($value_time_out) == true){
                                $string_error .=_l('time_out'). _l('not_yet_entered');
                                $flag = 1;
                            }

                            if(($flag == 0)){
                                $rd = [];
                                $rd['staffid'] = $sheet->getCell('A' . $rowIndex)->getValue();
                                $rd['time_in'] = $sheet->getCell('B' . $rowIndex)->getValue();
                                $rd['time_out'] = $sheet->getCell('C' . $rowIndex)->getValue();
                                $rows[] = $rd;
                            }
                            $total_rows ++; 
                        }
                                                   
                    }
                    $this->timesheets_model->import_timesheets($rows);
                    set_alert('success',_l('import_timesheets')); 
                }
            } else {
                set_alert('warning', _l('import_upload_failed'));
            }
        }
        redirect(admin_url('timesheets/timekeeping'));  
    }

    /**
     * Sets the leave.
     */
    public function set_leave(){
        if($this->input->post()){
            $data = $this->input->post();
            $success = $this->timesheets_model->set_leave($data);
            if($success > 0){
                $message = _l('updated_successfully', _l('setting'));
                    set_alert('success', $message);
            }
            redirect(admin_url('timesheets/setting?group=manage_leave'));
        }
    }

    /**
     * send notifi handover recipients
     * @return
     */
     public function send_notifi_handover_recipients()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if((isset($data)) && $data != ''){
                $this->timesheets_model->send_notifi_handover_recipients($data);

                $success = 'success';
                echo json_encode([
                'success' => $success,                
            ]); 
            }
        }
    }

    /**
     * send notification recipient
     * @return [type] [description]
     */
    public function send_notification_recipient()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if((isset($data)) && $data != ''){
                $this->timesheets_model->send_notification_recipient($data);

                $success = 'success';
                echo json_encode([
                'success' => $success,                
            ]); 
            }
        }
    }

    /**
     * delete timesheets attachment file
     * @param  int $attachment_id
     * @return
     */
    public function delete_timesheets_attachment_file($attachment_id)
    {
        $file = $this->misc_model->get_file($attachment_id);
            echo json_encode([
                'success' => $this->timesheets_model->delete_timesheets_attachment_file($attachment_id),
            ]);
    }   
    
    /**
     * reload shiftwork byfilter
     * @return json 
     */
    public function reload_shiftwork_byfilter(){
        $data = $this->input->post();       
        $query = "";
        if(isset($data["staff"])){
            if($data["staff"] != ''){
                $list_id = implode(', ', $data["staff"]);
                $query .= 'FIND_IN_SET(staffid, "'.$list_id.'") and ';
            }
        }

        if(isset($data["department"])){
            if($data["department"] != ''){
                $query .= 'staffid in (select '.db_prefix().'staff_departments.staffid from '.db_prefix().'staff_departments where departmentid = '.$data["department"].') and ';
            }
        }

        if(isset($data["role"])){
            if($data["role"] != ''){
                $query .= 'role = "'.$data["role"].'" and ';
            }
        }
       

       if($query != ''){
            $query = rtrim($query, ' and ');
       }
        $month      = date('m');
        $month_year = date('Y');
        $this->load->model('staff_model');
        $list_staff_id = [];
        if(is_admin()){
            $data_staff_list = $this->timesheets_model->get_staff_list($query != '' ? $query = 'where '.$query : '');
            foreach ($data_staff_list as $key => $value) {
               $list_staff_id[] = $value['staffid'];
            }            
        }
        else{
               $list_staff_id[] = get_staff_user_id();            
        }
        $data_hs = $this->set_col_tk(1, 31, $month, $month_year, true, $list_staff_id);
        $data['day_by_month']  = $data_hs->day_by_month;
        $data['list_data']  = $data_hs->list_data;
        $list_date = $this->timesheets_model->get_list_date($month_year.'-'.$month.'-01', $month_year.'-'.$month.'-31');  
        $data_object = [];
        foreach ($list_staff_id as $key => $value) {
            $row_data_staff = new stdClass();
            $row_data_staff->staffid = $value;
            $row_data_staff->staff = get_staff_full_name($value);
            $row_data_color = new stdClass();
            $row_data_color->staffid = '';
            $row_data_color->staff = '';            
            foreach ($list_date as $kdbm => $day) {
               $shift_s = '';
               $color = '';
               $list_shift = $this->timesheets_model->get_shift_work_staff_by_date($value, $day);
               foreach ($list_shift as $ss) {
                    $data_shift_type = $this->timesheets_model->get_shift_type($ss);
                    if($data_shift_type){
                        if($color == ''){
                            $color = $data_shift_type->color;
                        }

                        $start_date = $data_shift_type->time_start_work;
                        $st_1 = explode(':',$start_date);
                        $st_time = $st_1[0].'h'.$st_1[1];

                        $end_date = $data_shift_type->time_end_work;
                        $e_2 = explode(':',$end_date);
                        $e_time = $e_2[0].'h'.$e_2[1];

                        $shift_s .= $data_shift_type->shift_type_name.' ('.$st_time.' - '.$e_time.')'."\n";
                    }  
                }
                $day_s = date('D d',strtotime($day));
                $row_data_staff->$day_s = $shift_s;
                $row_data_color->$day_s = $color;
            }
            $data_object[] = $row_data_staff;
            $data_color[] = $row_data_color;
        }

        $data['data_object'] = $data_object;
        $data['data_color'] = $data_color;
        echo json_encode([
            'data_object' => $data_object,
            'data_color' =>  $data_color,
            'day_by_month' => $data['day_by_month'],
            'list_data' => $data['list_data']
        ]);
        die;
    }
    /**
     * advance payment go on bussiness update
    */
    public function advance_payment_update(){
        if($this->input->post()){
            $data = $this->input->post();
            $id = $data['id'];
            unset($data['id']);
            if($data['amount_received'] != '' && $data['received_date'] != ''){
                $success = $this->timesheets_model->advance_payment_update($id, $data);
           
            }else{
                $success = false;
            }

            echo json_encode([
                'success' =>  $success,
            ]);
        }
    }
}