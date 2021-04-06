<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hrm_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }
        public function add_staff($data)
    {
        // $data['birthday']             = to_sql_date($data['birthday']);
        // $data['days_for_identity']    = to_sql_date($data['days_for_identity']);
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }
        // First check for all cases if the email exists.
        $this->db->where('email', $data['email']);
        $email = $this->db->get(db_prefix() . 'staff')->row();
        if ($email) {
            die('Email already exists');
        }
        $data['admin'] = 0;
        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            }
        }

        $send_welcome_email = true;
        $original_password  = $data['password'];
        if (!isset($data['send_welcome_email'])) {
            $send_welcome_email = false;
        } else {
            unset($data['send_welcome_email']);
        }

        $data['password']        = app_hash_password($data['password']);
        $data['datecreated']     = date('Y-m-d H:i:s');
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }

        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['nationality'])) {
            unset($data['nationality']);
        }
        if ($data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }

        $this->db->insert(db_prefix() . 'staff', $data);
        $staffid = $this->db->insert_id();
        if ($staffid) {
            
            if (!isset($data['lastname'])) { $data['lastname'] = ''; }
            
            $slug = $data['firstname'] . ' ' . $data['lastname'];

            if ($slug == ' ') {
                $slug = 'unknown-' . $staffid;
            }

            if ($send_welcome_email == true) {
                send_mail_template('staff_created', $data['email'], $staffid, $original_password);
            }

            $this->db->where('staffid', $staffid);
            $this->db->update(db_prefix() . 'staff', [
                'media_path_slug' => slug_it($slug),
            ]);

            if (isset($custom_fields)) {
                handle_custom_fields_post($staffid, $custom_fields);
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert(db_prefix() . 'staff_departments', [
                        'staffid'      => $staffid,
                        'departmentid' => $department,
                    ]);
                }
            }

            // Delete all staff permission if is admin we dont need permissions stored in database (in case admin check some permissions)
            // $this->update_permissions($data['admin'] == 1 ? [] : $permissions, $staffid);

            log_activity('New Staff Member Added [ID: ' . $staffid . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');

            // Get all announcements and set it to read.
            $this->db->select('announcementid');
            $this->db->from(db_prefix() . 'announcements');
            $this->db->where('showtostaff', 1);
            $announcements = $this->db->get()->result_array();
            foreach ($announcements as $announcement) {
                $this->db->insert(db_prefix() . 'dismissed_announcements', [
                    'announcementid' => $announcement['announcementid'],
                    'staff'          => 1,
                    'userid'         => $staffid,
                ]);
            }
            hooks()->do_action('staff_member_created', $staffid);

            return $staffid;
        }

        return false;
    }
        public function update_staff($data, $id)
    {
        
        if(isset($data['DataTables_Table_0_length'])){
            unset($data['DataTables_Table_0_length']);
        }
        $data['date_update']          = date('Y-m-d');
        $data['birthday']             = to_sql_date($data['birthday']);
        $data['days_for_identity']    = to_sql_date($data['days_for_identity']);
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }
        if (isset($data['nationality'])) {
            unset($data['nationality']);
        }
        if (isset($data['DataTables_Table_0_length'])) {
            unset($data['DataTables_Table_0_length']);
        }
        $data = hooks()->apply_filters('before_update_staff_member', $data, $id);

        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            } else {
                if ($id != get_staff_user_id()) {
                    if ($id == 1) {
                        return [
                            'cant_remove_main_admin' => true,
                        ];
                    }
                } else {
                    return [
                        'cant_remove_yourself_from_admin' => true,
                    ];
                }
                $data['admin'] = 0;
            }
        }

        $affectedRows = 0;
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }

        $permissions = [];
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password']             = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }


        if (isset($data['two_factor_auth_enabled'])) {
            $data['two_factor_auth_enabled'] = 1;
        } else {
            $data['two_factor_auth_enabled'] = 0;
        }

        if (isset($data['is_not_staff'])) {
            $data['is_not_staff'] = 1;
        } else {
            $data['is_not_staff'] = 0;
        }

        if (isset($data['admin']) && $data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }

        $data['email_signature'] = nl2br_save_html($data['email_signature']);

        $this->load->model('departments_model');
        $staff_departments = $this->departments_model->get_staff_departments($id);
        if (sizeof($staff_departments) > 0) {
            if (!isset($data['departments'])) {
                $this->db->where('staffid', $id);
                $this->db->delete(db_prefix() . 'staff_departments');
            } else {
                foreach ($staff_departments as $staff_department) {
                    if (isset($departments)) {
                        if (!in_array($staff_department['departmentid'], $departments)) {
                            $this->db->where('staffid', $id);
                            $this->db->where('departmentid', $staff_department['departmentid']);
                            $this->db->delete(db_prefix() . 'staff_departments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->where('staffid', $id);
                    $this->db->where('departmentid', $department);
                    $_exists = $this->db->get(db_prefix() . 'staff_departments')->row();
                    if (!$_exists) {
                        $this->db->insert(db_prefix() . 'staff_departments', [
                            'staffid'      => $id,
                            'departmentid' => $department,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert(db_prefix() . 'staff_departments', [
                        'staffid'      => $id,
                        'departmentid' => $department,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }

        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . 'staff', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($this->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $permissions), $id)) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            hooks()->do_action('staff_member_updated', $id);
            log_activity('Staff Member Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }
    public function delete_staff($id, $transfer_data_to)
    {
        if (!is_numeric($transfer_data_to)) {
            return false;
        }

        if ($id == $transfer_data_to) {
            return false;
        }

        hooks()->do_action('before_delete_staff_member', [
            'id'               => $id,
            'transfer_data_to' => $transfer_data_to,
        ]);

        $name           = get_staff_full_name($id);
        $transferred_to = get_staff_full_name($transfer_data_to);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'estimates', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('sale_agent', $id);
        $this->db->update(db_prefix() . 'estimates', [
            'sale_agent' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'invoices', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('sale_agent', $id);
        $this->db->update(db_prefix() . 'invoices', [
            'sale_agent' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'expenses', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'notes', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'newsfeed_post_comments', [
            'userid' => $transfer_data_to,
        ]);

        $this->db->where('creator', $id);
        $this->db->update(db_prefix() . 'newsfeed_posts', [
            'creator' => $transfer_data_to,
        ]);

        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . 'projectdiscussions', [
            'staff_id' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'projects', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'creditnotes', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . 'credits', [
            'staff_id' => $transfer_data_to,
        ]);

        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . 'project_files', [
            'staffid' => $transfer_data_to,
        ]);

        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . 'proposal_comments', [
            'staffid' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'proposals', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . 'task_comments', [
            'staffid' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->where('is_added_from_contact', 0);
        $this->db->update(db_prefix() . 'tasks', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('staffid', $id);
        $this->db->update(db_prefix() . 'files', [
            'staffid' => $transfer_data_to,
        ]);

        $this->db->where('renewed_by_staff_id', $id);
        $this->db->update(db_prefix() . 'contract_renewals', [
            'renewed_by_staff_id' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'task_checklist_items', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('finished_from', $id);
        $this->db->update(db_prefix() . 'task_checklist_items', [
            'finished_from' => $transfer_data_to,
        ]);

        $this->db->where('admin', $id);
        $this->db->update(db_prefix() . 'ticket_replies', [
            'admin' => $transfer_data_to,
        ]);

        $this->db->where('admin', $id);
        $this->db->update(db_prefix() . 'tickets', [
            'admin' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'leads', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . 'leads', [
            'assigned' => $transfer_data_to,
        ]);

        $this->db->where('staff_id', $id);
        $this->db->update(db_prefix() . 'taskstimers', [
            'staff_id' => $transfer_data_to,
        ]);

        $this->db->where('addedfrom', $id);
        $this->db->update(db_prefix() . 'contracts', [
            'addedfrom' => $transfer_data_to,
        ]);

        $this->db->where('assigned_from', $id);
        $this->db->where('is_assigned_from_contact', 0);
        $this->db->update(db_prefix() . 'task_assigned', [
            'assigned_from' => $transfer_data_to,
        ]);

        $this->db->where('responsible', $id);
        $this->db->update(db_prefix() . 'leads_email_integration', [
            'responsible' => $transfer_data_to,
        ]);

        $this->db->where('responsible', $id);
        $this->db->update(db_prefix() . 'web_to_lead', [
            'responsible' => $transfer_data_to,
        ]);

        $this->db->where('created_from', $id);
        $this->db->update(db_prefix() . 'subscriptions', [
            'created_from' => $transfer_data_to,
        ]);

        $this->db->where('notify_type', 'specific_staff');
        $web_to_lead = $this->db->get(db_prefix() . 'web_to_lead')->result_array();

        foreach ($web_to_lead as $form) {
            if (!empty($form['notify_ids'])) {
                $staff = unserialize($form['notify_ids']);
                if (is_array($staff)) {
                    if (in_array($id, $staff)) {
                        if (($key = array_search($id, $staff)) !== false) {
                            unset($staff[$key]);
                            $staff = serialize(array_values($staff));
                            $this->db->where('id', $form['id']);
                            $this->db->update(db_prefix() . 'web_to_lead', [
                                'notify_ids' => $staff,
                            ]);
                        }
                    }
                }
            }
        }

        $this->db->where('id', 1);
        $leads_email_integration = $this->db->get(db_prefix() . 'leads_email_integration')->row();

        if ($leads_email_integration->notify_type == 'specific_staff') {
            if (!empty($leads_email_integration->notify_ids)) {
                $staff = unserialize($leads_email_integration->notify_ids);
                if (is_array($staff)) {
                    if (in_array($id, $staff)) {
                        if (($key = array_search($id, $staff)) !== false) {
                            unset($staff[$key]);
                            $staff = serialize(array_values($staff));
                            $this->db->where('id', 1);
                            $this->db->update(db_prefix() . 'leads_email_integration', [
                                'notify_ids' => $staff,
                            ]);
                        }
                    }
                }
            }
        }

        $this->db->where('assigned', $id);
        $this->db->update(db_prefix() . 'tickets', [
            'assigned' => 0,
        ]);

        $this->db->where('staff', 1);
        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'dismissed_announcements');

        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'newsfeed_comment_likes');

        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'newsfeed_post_likes');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'customer_admins');

        $this->db->where('fieldto', 'staff');
        $this->db->where('relid', $id);
        $this->db->delete(db_prefix() . 'customfieldsvalues');

        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'events');

        $this->db->where('touserid', $id);
        $this->db->delete(db_prefix() . 'notifications');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'user_meta');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'project_members');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'project_notes');

        $this->db->where('creator', $id);
        $this->db->or_where('staff', $id);
        $this->db->delete(db_prefix() . 'reminders');

        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . 'staff_departments');

        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . 'todos');

        $this->db->where('staff', 1);
        $this->db->where('user_id', $id);
        $this->db->delete(db_prefix() . 'user_auto_login');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'staff_permissions');

        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . 'task_assigned');

        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . 'task_followers');

        $this->db->where('staff_id', $id);
        $this->db->delete(db_prefix() . 'pinned_projects');

        $this->db->where('staffid', $id);
        $this->db->delete(db_prefix() . 'staff');
        log_activity('Staff Member Deleted [Name: ' . $name . ', Data Transferred To: ' . $transferred_to . ']');

        hooks()->do_action('staff_member_deleted', [
            'id'               => $id,
            'transfer_data_to' => $transfer_data_to,
        ]);

        return true;
    }
    public function get_staff_permissions($id)
    {
        // Fix for version 2.3.1 tables upgrade
        if (defined('DOING_DATABASE_UPGRADE')) {
            return [];
        }

        $permissions = $this->app_object_cache->get('staff-' . $id . '-permissions');

        if (!$permissions && !is_array($permissions)) {
            $this->db->where('staff_id', $id);
            $permissions = $this->db->get('staff_permissions')->result_array();

            $this->app_object_cache->add('staff-' . $id . '-permissions', $permissions);
        }

        return $permissions;
    }
    public function get($id = '')
    {

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'salaries.id', $id);
            $salary = $this->db->get(db_prefix() . 'salaries')->row();
            return $salary;
        }
        return false;
    }
    //
     public function staffExist($id = '',$month = '')
    {

        // die(date('m',strtotime($month )) == date('m'));
        if (is_numeric($id)) {
            $this->db->where('staff_member', $id);
            $this->db->where('current_month',date('m',strtotime($month)));
            $staff = $this->db->get(db_prefix() . 'salaries')->row();
            return $staff;
        }
    }
    public function get_staff()
    {
        return $this->db->get(db_prefix() . 'staff')->result_array();
    }
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'salaries', $data);
        return true;
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'salaries', $data);
        return $this->db->insert_id();
    }
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'salaries');
        return true;
    }
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


}
