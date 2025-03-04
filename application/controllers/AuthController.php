<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{

  public function __construct()
  {
    parent::__construct(); // Initialize parent constructor to load core properties
    $this->load->helper('url');
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->library('email');
    $this->load->model('AuthModel');
  }

  public function teacherRegistrationIndex()
  {
    $data = [
      'countries' => $this->AuthModel->country(),
      'states' => $this->AuthModel->getStates(),
    ];

    // Teacher registration logic here
    $this->load->view('globals/web/header');
    $this->load->view('web/teacher-registration', $data);
    $this->load->view('globals/web/footer');
  }

  public function teacherRegistrationStore()
  {
    $email = $this->input->post('email');

    $accountExist = $this->WebModel->validate_email($email);
    if ($accountExist) {
      $this->session->set_flashdata('error', 'Email ID is already in Use.');
      return  redirect(base_url('/teacher-registration'));
    }

    $teacher_code = $this->randomPass(10);


    $data = [
      'fullname' => $this->input->post('fullname'),
      'mobile' => $this->input->post('phone'),
      'teacher_code' => $teacher_code,
      'email' => $email,
      'pin' => $this->input->post('pin'),
      'address' => $this->input->post('address'),
      'principal_name' => $this->input->post('principalName'),
      'country' => $country,
      'city' => $this->input->post('district'),
      'state' => $this->input->post('state'),
      'board_name' => $this->input->post('board'),
      'user_type' => 'Teacher',
      'status' => 0,
      'stu_limit' => 30,
      'school_name' => $this->input->post('schoolName'),
      'password' => $this->input->post('password'),
      // 'session_start' => $start_session,
      // 'session_end' => $end_session,
    ];

    $userCreation = $this->AuthModel->addWebUser($data);

    $emailData = [
      'user' => $this->input->post('email'),
      'password' => $this->input->post('password'),
      'fullname' => $this->input->post('name'),
      'logo' => $this->AuthModel->content_row('Logo'),
      'mobile1' => $this->AuthModel->content_row('Mobile1'),
    ];
    $config = array(
      'charset' => 'utf-8',
      'wordwrap' => TRUE,
      'mailtype' => 'html'
    );


    $this->email->initialize($config);
    $this->email->to($this->input->post('email'));
    $this->email->from('info@brightshinedigital.in', 'Brightshine Digital');
    $this->email->cc('mayank@epochstudio.net, info@brightshinedigital.in');
    $this->email->subject('Your Credentials for Brightshine Digital Web Support');
    $this->email->message($this->load->view('web/email_template', $emailData, true));
    $this->email->send();
    if (!$userCreation) {
      $this->session->set_flashdata('error', 'Something is wrong! Your are not registered');
      return redirect(base_url('/teacher-registration'));
    } else {
      $this->session->set_flashdata('success', 'You are Successfully registered with us, Please Check your registered email for your account credentials...');

      $res = $this->AuthModel->validate_web($this->input->post('email'), $this->input->post('password'), '0');
      return redirect(base_url('/book-selection'));
    }
  }

  public function studentRegistrationIndex()
  {
    // Student registration logic here
  }

  public function bookSelection()
  {
    $this->load->view('globals/web/header');
    $this->load->view('web/book-selection');
    $this->load->view('globals/web/footer');
  }

  function randomPass($numchar)
  {
    $word = "a,b,c,d,e,f,g,h,i,j,k,l,m,1,2,3,4,5,6,7,8,9,0";
    $array = explode(",", $word);
    shuffle($array);
    $newstring = implode($array);
    return substr($newstring, 0, $numchar);
  }
}
