<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'timesheets_option')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_option` (
      `option_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `option_name` varchar(200) NOT NULL,
      `option_val` longtext NULL,
      `auto` tinyint(1) NULL,
      PRIMARY KEY (`option_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'manage_leave')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "manage_leave` (
      `leave_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_staff` int(11) NOT NULL,
      `leave_date` int(11) NULL,
      `leave_year` int(11) NULL,
      `accumulated_leave` int(11) NULL,
      `seniority_leave` int(11) NULL,
      `borrow_leave` int(11) NULL,
      `actual_leave` int(11) NULL,
      `expected_leave` int(11) NULL,
      PRIMARY KEY (`leave_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'day_off')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "day_off` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `off_reason` varchar(255) NOT NULL,
      `off_type` varchar(100) NOT NULL,
      `break_date` date NOT NULL,
      `timekeeping` varchar(45) NULL,
      `department` int(11) NULL DEFAULT '0',
      `position` int(11) NULL DEFAULT '0',
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'work_shift')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "work_shift` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shift_code` varchar(45) NOT NULL,
      `shift_name` varchar(200) NOT NULL,
      `shift_type` varchar(200) NOT NULL,
      `department` int(11) NULL DEFAULT '0',
      `position` int(11) NULL DEFAULT '0',
      `add_from` int(11) NOT NULL,
      `date_create` date NULL,
      `from_date` date NULL,
      `to_date` date NULL,
      `shifts_detail` TEXT NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_timesheet')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_timesheet` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `date_work` date NOT NULL,
      `value` text NULL,
      `type` varchar(45) NULL,
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('overtime_setting' ,db_prefix() . 'timesheets_timesheet')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_timesheet`
  ADD COLUMN `overtime_setting` INT(11) NULL AFTER `add_from`;');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_requisition_leave` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `subject` varchar(100) NULL,
      `start_time` DATETIME NOT NULL,
      `end_time` DATETIME NOT NULL,
      `reason` text NULL,
      `approver_id` int(11) NOT NULL,
      `followers_id` int(11) NULL,
      `rel_type` int(11) NOT NULL COMMENT '1:Leave 2:Late_early 3:Go_out 4:Go_on_bussiness',
      `status` int(11) NULL DEFAULT 0 COMMENT '0:Create 1:Approver 2:Reject',
      PRIMARY KEY (`id`,staff_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('place_of_business' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
  ADD COLUMN `place_of_business` LONGTEXT NULL AFTER `status`');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_additional_timesheet')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'timesheets_additional_timesheet` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `additional_day` VARCHAR(45) NOT NULL,
      `status` VARCHAR(45) NOT NULL,
      `timekeeping_value` VARCHAR(45) NOT NULL,
      `approver` INT(11) NOT NULL,
      `creator` INT(11) NOT NULL,
      PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_timekeeper_data')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'timesheets_timekeeper_data` (
      `staff_identifi` VARCHAR(25) NOT NULL,
      `time` DATETIME NOT NULL,
      `type` VARCHAR(45) NOT NULL,
      PRIMARY KEY (`staff_identifi`, `time`, `type`));');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_approval_setting')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'timesheets_approval_setting` (
    `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `related` VARCHAR(255) NOT NULL,
  `setting` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('choose_when_approving' ,db_prefix() . 'timesheets_approval_setting')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_approval_setting`
  ADD COLUMN `choose_when_approving` INT NOT NULL DEFAULT 0 AFTER `setting`');
}

if (!$CI->db->field_exists('notification_recipient' ,db_prefix() . 'timesheets_approval_setting')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_approval_setting`
  ADD COLUMN `notification_recipient` LONGTEXT  NULL  AFTER `choose_when_approving`');
}

if (!$CI->db->field_exists('number_day_approval' ,db_prefix() . 'timesheets_approval_setting')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_approval_setting`
  ADD COLUMN `number_day_approval` INT(11) NULL AFTER `notification_recipient`;');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_approval_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'timesheets_approval_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NOT NULL,
  `rel_type` VARCHAR(45) NOT NULL,
  `staffid` VARCHAR(45) NULL,
  `approve` VARCHAR(45) NULL,
  `note` TEXT NULL,
  `date` DATETIME NULL,
  `approve_action` VARCHAR(255) NULL,
  `reject_action` VARCHAR(255) NULL,
  `approve_value` VARCHAR(255) NULL,
  `reject_value` VARCHAR(255) NULL,
  `staff_approve` INT(11) NULL,
  `action` VARCHAR(45) NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('sender', db_prefix() . 'timesheets_approval_details')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'timesheets_approval_details` 
ADD COLUMN `sender` INT(11) NULL AFTER `action`,
ADD COLUMN `date_send` DATETIME NULL AFTER `sender`;');            
}

if (!$CI->db->field_exists('notification_recipient', db_prefix() . 'timesheets_approval_details')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . 'timesheets_approval_details` 
ADD COLUMN `notification_recipient` LONGTEXT NULL AFTER `date_send`,
ADD COLUMN `approval_deadline` DATE NULL AFTER `notification_recipient`;');            
}


if (!$CI->db->field_exists('type_of_leave', db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_requisition_leave`
  ADD COLUMN `type_of_leave` INT(11) NULL DEFAULT '0' AFTER `place_of_business`;");            
}

if (!$CI->db->field_exists('handover_recipients', db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_requisition_leave`
  ADD COLUMN `handover_recipients` LONGTEXT NULL  AFTER `type_of_leave`;");            
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_latch_timesheet')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_latch_timesheet` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `month_latch` VARCHAR(45) NULL,
      PRIMARY KEY (`id`));");
}

if (!$CI->db->field_exists('according_to_the_plan', db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_requisition_leave`
  ADD COLUMN `according_to_the_plan` INT(11) NULL DEFAULT '0' AFTER `type_of_leave`;");            
}


if (!$CI->db->field_exists('old_timekeeping', db_prefix() . 'timesheets_additional_timesheet')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_additional_timesheet`
  ADD COLUMN `old_timekeeping` VARCHAR(50) NULL  AFTER `creator`;");            
}

if (!$CI->db->field_exists('time_in' ,db_prefix() . 'timesheets_additional_timesheet')) {
   $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_additional_timesheet`
  ADD COLUMN `time_in` VARCHAR(45) NULL AFTER `old_timekeeping`,
  ADD COLUMN `time_out` VARCHAR(45) NULL AFTER `time_in`');
}

if (!$CI->db->field_exists('overtime_setting' ,db_prefix() . 'timesheets_additional_timesheet')) {
   $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_additional_timesheet`
  ADD COLUMN `overtime_setting` INT(11) NULL AFTER `time_out`;');
}

if (!$CI->db->field_exists('staff' ,db_prefix() . 'work_shift')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'work_shift`
  ADD COLUMN `staff` TEXT NULL AFTER `add_from`');
}

if (!$CI->db->field_exists('timekeeping_type' ,db_prefix() . 'timesheets_additional_timesheet')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "timesheets_additional_timesheet`
    ADD COLUMN `timekeeping_type` varchar(50) NULL AFTER `status`;");
}

if (!$CI->db->field_exists('amount_received' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
  ADD COLUMN `amount_received` TEXT NULL AFTER `reason`');
}

if (!$CI->db->field_exists('received_date' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
  ADD COLUMN `received_date` DATE NULL AFTER `reason`');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_day_off')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_day_off` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `staffid` INT(11) NOT NULL,
      `year` VARCHAR(45) NULL,
      `total` VARCHAR(45) NULL,
      `remain` VARCHAR(45) NULL,
      `accumulated` VARCHAR(45) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('reason', db_prefix() . 'timesheets_additional_timesheet')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_additional_timesheet`
  ADD COLUMN `reason` TEXT NULL;");            
}


if (!$CI->db->field_exists('departments', db_prefix() . 'timesheets_approval_setting')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_approval_setting`
  ADD COLUMN `departments` TEXT NULL AFTER `number_day_approval`,
  ADD COLUMN `job_positions` TEXT NULL AFTER `departments`;");            
}


if (!$CI->db->table_exists(db_prefix() . 'timesheets_shift_sc')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_shift_sc` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `shift_symbol` VARCHAR(45) NOT NULL,
        `time_start_work` VARCHAR(45) NOT NULL,
        `time_end_work` VARCHAR(45) NOT NULL,
        `start_lunch_break_time` VARCHAR(45) NOT NULL,
        `end_lunch_break_time` VARCHAR(45) NOT NULL,
        `late_latency_allowed` VARCHAR(45) NOT NULL,
        `description` TEXT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (row_timesheets_options_exist('"shift_applicable_object"') == 0){
  $CI->db->query('INSERT INTO `' . db_prefix() .'timesheets_option` (`option_name`, `option_val`, `auto`) VALUES ("shift_applicable_object", "", "1");');
}

if (!$CI->db->table_exists(db_prefix() . 'timesheets_shiftwork_sc')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_shiftwork_sc` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `date_work` date NOT NULL,
      `shift` int(11) NOT NULL,
      `datecreated` DATETIME NULL,
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('datecreated', db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `'.db_prefix() . "timesheets_requisition_leave`
  ADD COLUMN `datecreated` DATETIME NULL;");            
}

if (row_timesheets_options_exist('"timekeeping_form"') == 0){
  $CI->db->query('INSERT INTO `tbltimesheets_option` (`option_name`, `option_val`, `auto`) VALUES ("timekeeping_form", "timekeeping_manually", "1");
');
}

if (!$CI->db->table_exists(db_prefix() . 'leave_of_the_year')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "leave_of_the_year` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `value` double null,
      `datecreated` DATETIME NULL,
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->field_exists('number_of_days' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
  ADD COLUMN `number_of_days` float NULL');
}
if ($CI->db->field_exists('start_time' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
    MODIFY `start_time` DATE NULL,
    MODIFY `end_time` DATE NULL'
  );
}

if (!$CI->db->table_exists(db_prefix() . 'shift_type')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "shift_type` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `shift_type_name` varchar(150),
      `color` varchar(50),
      `time_start` date,
      `time_end` date,
      `time_start_work` varchar(50),
      `time_end_work` varchar(50),
      `start_lunch_break_time` varchar(50),
      `end_lunch_break_time` varchar(50),
      `description` longtext,
      `datecreated` DATETIME NULL,
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'work_shift_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "work_shift_detail` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NULL,
      `date` date NULL,
      `shift_id` int(11) NULL,
      `work_shift_id` int(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'check_in_out')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "check_in_out` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NULL,
      `date` datetime NULL,
      `type_check` int(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if ($CI->db->field_exists('department' ,db_prefix() . 'work_shift')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'work_shift`
    MODIFY `department` varchar(45) NULL,
    MODIFY `position` varchar(45) NULL'
  );
}
if (!$CI->db->field_exists('type_shiftwork' ,db_prefix() . 'work_shift')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'work_shift`
  ADD COLUMN `type_shiftwork` varchar(25) NULL');
}
if (!$CI->db->table_exists(db_prefix() . 'work_shift_detail_day_name')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "work_shift_detail_day_name` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NULL,
      `day_name` varchar(45) NULL,
      `shift_id` int(11) NULL,
      `work_shift_id` int(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'work_shift_detail_number_day')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "work_shift_detail_number_day` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NULL,
      `number` int(11) NULL,
      `shift_id` int(11) NULL,
      `work_shift_id` int(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('repeat_by_year' ,db_prefix() . 'day_off')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'day_off`
    ADD COLUMN `repeat_by_year` int(11) NULL');
}

if ($CI->db->field_exists('department' ,db_prefix() . 'day_off')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'day_off`
    MODIFY `department` varchar(45) NULL,
    MODIFY `position` varchar(45) NULL'
  );
}
if (!$CI->db->field_exists('days_off' ,db_prefix() . 'timesheets_day_off')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_day_off`
  ADD COLUMN `days_off` int(11) NULL DEFAULT 0');
}
if (row_timesheets_options_exist('"timekeeping_manually_role"') == 0){
  $CI->db->query('INSERT INTO `tbltimesheets_option` (`option_name`, `option_val`, `auto`) VALUES ("timekeeping_manually_role", "", "1");
');
}
if (row_timesheets_options_exist('"timekeeping_task_role"') == 0){
  $CI->db->query('INSERT INTO `tbltimesheets_option` (`option_name`, `option_val`, `auto`) VALUES ("timekeeping_task_role", "", "1");
');
}
if (row_timesheets_options_exist('"csv_clsx_role"') == 0){
  $CI->db->query('INSERT INTO `tbltimesheets_option` (`option_name`, `option_val`, `auto`) VALUES ("csv_clsx_role", "", "1");
');
}
if (!$CI->db->table_exists(db_prefix() . 'timesheets_leave')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "timesheets_leave` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `date_work` date NOT NULL,
      `value` text NULL,
      `type` varchar(45) NULL,
      `add_from` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->field_exists('type' ,db_prefix() . 'check_in_out')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'check_in_out`
    ADD COLUMN `type` varchar(5) not null default "W"');
}

if (!$CI->db->field_exists('latch' ,db_prefix() . 'timesheets_timesheet')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_timesheet`
    ADD COLUMN `latch` INT(11) NOT NULL DEFAULT 0');
}

if (!$CI->db->field_exists('number_of_leaving_day' ,db_prefix() . 'timesheets_requisition_leave')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheets_requisition_leave`
  ADD COLUMN `number_of_leaving_day` VARCHAR(45) NULL;');
}