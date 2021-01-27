<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminusers extends CI_Controller {
  public function __construct()
  {
    parent::__construct();

    $this->load->database();
    $this->load->library('session');
    /*cache control*/
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->output->set_header('Pragma: no-cache');
    if (!$this->session->userdata('cart_items')) {
      $this->session->set_userdata('cart_items', array());
    }
  }

 public function index() {
    if ($this->session->userdata('admin_login') == true) {
      $this->dashboard();
    }else {
      redirect(site_url('login'), 'refresh');
    }
  }
  public function dashboard() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $page_data['page_name'] = 'dashboard';
    $page_data['page_title'] = get_phrase('dashboard');
    $this->load->view('backend/index.php', $page_data);
  }

  public function blank_template() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $page_data['page_name'] = 'blank_template';
    $this->load->view('backend/index.php', $page_data);
  }



public function users($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == "add") {
      $this->user_model->add_user();
      redirect(site_url('admin/adminusers/users'), 'refresh');
    }
    elseif ($param1 == "edit") {
      $this->user_model->edit_user($param2);
      redirect(site_url('admin/adminusers/users'), 'refresh');
    }
    elseif ($param1 == "delete") {
      $this->user_model->delete_user($param2);
      redirect(site_url('admin/adminusers/users'), 'refresh');
    }

    $page_data['page_name'] = 'users';
    $page_data['page_title'] = get_phrase('teacher');
    $page_data['users'] = $this->user_model->get_user($param2);
    $this->load->view('backend/index', $page_data);
  }

  public function user_form($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'add_user_form') {
      $page_data['page_name'] = 'user_add';
      $page_data['page_title'] = get_phrase('student_add');
      $this->load->view('backend/index', $page_data);
    }
    elseif ($param1 == 'edit_user_form') {
      $page_data['page_name'] = 'user_edit';
      $page_data['user_id'] = $param2;
      $page_data['page_title'] = get_phrase('student_edit');
      $this->load->view('backend/index', $page_data);
    }
  }

public function enrol_student($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == 'enrol') {
      $this->crud_model->enrol_a_student_manually();
      redirect(site_url('admin/adminusers/enrol_history'), 'refresh');
    }
    $page_data['page_name'] = 'enrol_student';
    $page_data['page_title'] = get_phrase('enrol_a_student');
    $this->load->view('backend/index', $page_data);
  }

 public function instructor_revenue($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 != "") {
      $date_range                   = $this->input->post('date_range');
      $date_range                   = explode(" - ", $date_range);
      $page_data['timestamp_start'] = strtotime($date_range[0]);
      $page_data['timestamp_end']   = strtotime($date_range[1]);
    }else {
      $page_data['timestamp_start'] = strtotime('-29 days', time());
      $page_data['timestamp_end']   = strtotime(date("m/d/Y"));
    }
    $page_data['page_name'] = 'instructor_revenue';
    $page_data['payment_history'] = $this->crud_model->get_revenue_by_user_type($page_data['timestamp_start'], $page_data['timestamp_end'], 'instructor_revenue');
    $page_data['page_title'] = get_phrase('instructor_revenue');
    $this->load->view('backend/index', $page_data);
  }
 public function instructor_settings($param1 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == 'update') {
      $this->crud_model->update_instructor_settings();
      $this->session->set_flashdata('flash_message', get_phrase('instructor_settings_updated'));
      redirect(site_url('admin/adminusers/instructor_settings'), 'refresh');
    }

    $page_data['page_name'] = 'instructor_settings';
    $page_data['page_title'] = get_phrase('instructor_settings');
    $this->load->view('backend/index', $page_data);
  }


}