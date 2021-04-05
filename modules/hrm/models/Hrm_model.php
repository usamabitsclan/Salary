<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hrm_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
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
     public function staffExist($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('staff_member', $id);
            $staff = $this->db->get(db_prefix() . 'salaries')->row();
            return $staff;
        }
    }
    public function getStaff($id = '', $where = [])
    {
        $select_str = '*,CONCAT(firstname,\' \',lastname) as full_name';

        // Used to prevent multiple queries on logged in staff to check the total unread notifications in core/AdminController.php
        if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
            $select_str .= ',(SELECT COUNT(*) FROM ' . db_prefix() . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
        }

        $this->db->select($select_str);
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('staffid', $id);
            $staff = $this->db->get(db_prefix() . 'staff')->row();

            // if ($staff) {
            //     $staff->permissions = $this->get_staff_permissions($id);
            // }

            return $staff;
        }
        $this->db->order_by('firstname', 'desc');

        return $this->db->get(db_prefix() . 'staff')->result_array();
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


}
