<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'salaries')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "salaries` (
  `id` int(11) NOT NULL,
  `staff_member` varchar(191) NOT NULL,
  `bank_name` varchar(191) NOT NULL,
  `bank_account_title` varchar(191) NOT NULL,
  `bank_account_name` varchar(191) NOT NULL,
  `designation` varchar(191) NOT NULL,
  `basic_salary` varchar(191) NOT NULL,

  `paid_leaves` varchar(191) NOT NULL,
  `unpaid_leaves` varchar(191) NOT NULL,
  `total_salary` varchar(191) NOT NULL,

  `bonus` varchar(191) NOT NULL,
  `created_at` date NOT NULL

) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'salaries`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
