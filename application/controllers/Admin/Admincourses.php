<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admincourses extends CI_Controller {

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
public function courses() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }


    $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
    $page_data['selected_instructor_id'] = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : "all";
    $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
    $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";
    $page_data['courses']                = $this->crud_model->filter_course_for_backend($page_data['selected_category_id'], $page_data['selected_instructor_id'], $page_data['selected_price'], $page_data['selected_status']);
    $page_data['status_wise_courses']    = $this->crud_model->get_status_wise_courses();
    $page_data['instructors']            = $this->user_model->get_instructor();
    $page_data['page_name']              = 'courses';
    $page_data['categories']             = $this->crud_model->get_categories();
    $page_data['page_title']             = get_phrase('active_courses');
    $this->load->view('backend/index', $page_data);
  }

  public function pending_courses() {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }


    $page_data['page_name'] = 'pending_courses';
    $page_data['page_title'] = get_phrase('pending_courses');
    $this->load->view('backend/index', $page_data);
  }

  public function course_actions($param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == "add") {
      $this->crud_model->add_course();
      redirect(site_url('admin/admincourses/courses'), 'refresh');

    }
    elseif ($param1 == "edit") {
      $this->crud_model->update_course($param2);
      redirect(site_url('admin/admincourses/courses'), 'refresh');

    }
    elseif ($param1 == 'delete') {
      $this->is_drafted_course($param2);
      $this->crud_model->delete_course($param2);
      redirect(site_url('admin/admincourses/courses'), 'refresh');
    }
  }


  public function course_form($param1 = "", $param2 = "") {

    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param1 == 'add_course') {
      $page_data['languages'] = $this->get_all_languages();
      $page_data['categories'] = $this->crud_model->get_categories();
      $page_data['page_name'] = 'course_add';
      $page_data['page_title'] = get_phrase('add_course');
      $this->load->view('backend/index', $page_data);

    }elseif ($param1 == 'course_edit') {
      $this->is_drafted_course($param2);
      $page_data['page_name'] = 'course_edit';
      $page_data['course_id'] =  $param2;
      $page_data['page_title'] = get_phrase('edit_course');
      $page_data['languages'] = $this->get_all_languages();
      $page_data['categories'] = $this->crud_model->get_categories();
      $this->load->view('backend/index', $page_data);
    }
  }

  private function is_drafted_course($course_id){
    $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
    if ($course_details['status'] == 'draft') {
      $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
      redirect(site_url('admin/admincourses/courses'), 'refresh');
    }
  }

  public function change_course_status($updated_status = "") {
    $course_id = $this->input->post('course_id');
    $category_id = $this->input->post('category_id');
    $instructor_id = $this->input->post('instructor_id');
    $price = $this->input->post('price');
    $status = $this->input->post('status');
    if (isset($_POST['mail_subject']) && isset($_POST['mail_body'])) {
      $mail_subject = $this->input->post('mail_subject');
      $mail_body = $this->input->post('mail_body');
      $this->email_model->send_mail_on_course_status_changing($course_id, $mail_subject, $mail_body);
    }
    $this->crud_model->change_course_status($updated_status, $course_id);
    $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
    redirect(site_url('admin/admincourses/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
  }

  public function change_course_status_for_admin($updated_status = "", $course_id = "", $category_id = "", $status = "", $instructor_id = "", $price = "") {
    $this->crud_model->change_course_status($updated_status, $course_id);
    $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
    redirect(site_url('admin/admincourses/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
  }
   public function sections($param1 = "", $param2 = "", $param3 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($param2 == 'add') {
      $this->crud_model->add_section($param1);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_added_successfully'));
    }
    elseif ($param2 == 'edit') {
      $this->crud_model->edit_section($param3);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
    }
    elseif ($param2 == 'delete') {
      $this->crud_model->delete_section($param1, $param3);
      $this->session->set_flashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
    }
    redirect(site_url('admin/admincourses/course_form/course_edit/'.$param1));
  }

  public function lessons($course_id = "", $param1 = "", $param2 = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    if ($param1 == 'add') {
      $this->crud_model->add_lesson();
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_added_successfully'));
      redirect('admin/admincourses/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'edit') {
      $this->crud_model->edit_lesson($param2);
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_updated_successfully'));
      redirect('admin/admincourses/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'delete') {
      $this->crud_model->delete_lesson($param2);
      $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
      redirect('admin/admincourses/course_form/course_edit/'.$course_id);
    }
    elseif ($param1 == 'filter') {
      redirect('admin/admincourses/lessons/'.$this->input->post('course_id'));
    }
    $page_data['page_name'] = 'lessons';
    $page_data['lessons'] = $this->crud_model->get_lessons('course', $course_id);
    $page_data['course_id'] = $course_id;
    $page_data['page_title'] = get_phrase('lessons');
    $this->load->view('backend/index', $page_data);
  }
   function get_all_languages() {
    $language_files = array();
    $all_files = $this->get_list_of_language_files();
    foreach ($all_files as $file) {
      $info = pathinfo($file);
      if( isset($info['extension']) && strtolower($info['extension']) == 'json') {
        $file_name = explode('.json', $info['basename']);
        array_push($language_files, $file_name[0]);
      }
    }
    return $language_files;
  }

  function get_list_of_language_files($dir = APPPATH.'/language', &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }

  function get_list_of_directories_and_files($dir = APPPATH, &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }
  // Manage Quizes
  public function quizes($course_id = "", $action = "", $quiz_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }

    if ($action == 'add') {
      $this->crud_model->add_quiz($course_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
    }
    elseif ($action == 'edit') {
      $this->crud_model->edit_quiz($quiz_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
    }
    elseif ($action == 'delete') {
      $this->crud_model->delete_section($course_id, $quiz_id);
      $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
    }
    redirect(site_url('admin/admincourses/course_form/course_edit/'.$course_id));
  }

  // Manage Quize Questions
  public function quiz_questions($quiz_id = "", $action = "", $question_id = "") {
    if ($this->session->userdata('admin_login') != true) {
      redirect(site_url('login'), 'refresh');
    }
    $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

    if ($action == 'add') {
      $response = $this->crud_model->add_quiz_questions($quiz_id);
      echo $response;
    }

    elseif ($action == 'edit') {
      $response = $this->crud_model->update_quiz_questions($question_id);
      echo $response;
    }

    elseif ($action == 'delete') {
      $response = $this->crud_model->delete_quiz_question($question_id);
      $this->session->set_flashdata('flash_message', get_phrase('question_has_been_deleted'));
      redirect(site_url('admin/admincourses/course_form/course_edit/'.$quiz_details['course_id']));
    }
  }
  // AJAX PORTION

  // this function is responsible for managing multiple choice question
  function manage_multiple_choices_options() {
    $page_data['number_of_options'] = $this->input->post('number_of_options');
    $this->load->view('backend/admin/manage_multiple_choices_options', $page_data);
  }
   public function ajax_get_section($course_id){
    $page_data['sections'] = $this->crud_model->get_section('course', $course_id)->result_array();
    return $this->load->view('backend/admin/ajax_get_section', $page_data);
  }
  public function ajax_sort_section() {
    $section_json = $this->input->post('itemJSON');
    $this->crud_model->sort_section($section_json);
  }
  public function ajax_sort_lesson() {
    $lesson_json = $this->input->post('itemJSON');
    $this->crud_model->sort_lesson($lesson_json);
  }
  public function ajax_sort_question() {
    $question_json = $this->input->post('itemJSON');
    $this->crud_model->sort_question($question_json);
  }
}