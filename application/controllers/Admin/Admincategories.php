<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admincategories extends CI_Controller {
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

  public function categories($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'add') {
      $this->crud_model->add_category();
      $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
      redirect(site_url('admin/admincategories/categories'), 'refresh');
    }
    elseif ($param1 == "edit") {
      $this->crud_model->edit_category($param2);
      $this->session->set_flashdata('flash_message', get_phrase('data_updated_successfully'));
      redirect(site_url('admin/admincategories/categories'), 'refresh');
    }
    elseif ($param1 == "delete") {
      $this->crud_model->delete_category($param2);
      $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
      redirect(site_url('admin/admincategories/categories'), 'refresh');
    }
    $page_data['page_name'] = 'categories';
    $page_data['page_title'] = get_phrase('categories');
    $page_data['categories'] = $this->crud_model->get_categories($param2);
    $this->load->view('backend/index', $page_data);
  }

  public function category_form($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == "add_category") {
      $page_data['page_name'] = 'category_add';
      $page_data['categories'] = $this->crud_model->get_categories()->result_array();
      $page_data['page_title'] = get_phrase('add_category');
    }
    if ($param1 == "edit_category") {
      $page_data['page_name'] = 'category_edit';
      $page_data['page_title'] = get_phrase('edit_category');
      $page_data['categories'] = $this->crud_model->get_categories()->result_array();
      $page_data['category_id'] = $param2;
    }

    $this->load->view('backend/index', $page_data);
  }

  public function sub_categories_by_category_id($category_id = 0) {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    $category_id = $this->input->post('category_id');
    redirect(site_url("admin/admincategories/sub_categories/$category_id"), 'refresh');
  }

  public function sub_category_form($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'add_sub_category') {
      $page_data['page_name'] = 'sub_category_add';
      $page_data['page_title'] = get_phrase('add_sub_category');
    }
    elseif ($param1 == 'edit_sub_category') {
      $page_data['page_name'] = 'sub_category_edit';
      $page_data['page_title'] = get_phrase('edit_sub_category');
      $page_data['sub_category_id'] = $param2;
    }
    $page_data['categories'] = $this->crud_model->get_categories();
    $this->load->view('backend/index', $page_data);
  }
public function ajax_get_sub_category($category_id) {
    $page_data['sub_categories'] = $this->crud_model->get_sub_categories($category_id);

    return $this->load->view('backend/admin/ajax_get_sub_category', $page_data);
  }
}