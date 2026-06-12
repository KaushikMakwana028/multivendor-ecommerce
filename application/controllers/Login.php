<?php defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('General_model');
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('url', 'form'));
    }

    /*
    |--------------------------------------------------------------------------
    | Check Login
    |--------------------------------------------------------------------------
    */
    public static function check_login()
    {
        $CI = &get_instance();

        if (!$CI->session->userdata('admin_id')) {
            redirect('login');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect If Already Logged In
    |--------------------------------------------------------------------------
    */
    private function redirect_if_logged()
    {
        if ($this->session->userdata('admin_id')) {
            redirect('dashboard');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $this->redirect_if_logged();

        if ($this->input->post()) {
            $this->form_validation->set_rules(
                'email',
                'Email',
                'required|valid_email'
            );

            $this->form_validation->set_rules(
                'password',
                'Password',
                'required'
            );

            if ($this->form_validation->run() == FALSE) {
                $data['error'] = validation_errors();

                $this->load->view('login_view', $data);
                return;
            }

            $email    = trim($this->input->post('email', TRUE));
            $password = $this->input->post('password');

            $user = $this->General_model->getOne(
                'users',
                array(
                    'email'     => $email,
                    'is_active' => 1
                )
            );

            if ($user && password_verify($password, $user->password)) {
                $session_data = array(
                    'admin_id'    => $user->id,
                    'admin_name'  => $user->name,
                    'admin_email' => $user->email,
                    'admin_role'  => $user->role,
                    'shop_name'   => $user->shop_name,
                    'admin_image' => $user->image,
                    'logged_in'   => TRUE
                );

                $this->session->set_userdata($session_data);

                redirect('dashboard');
            } else {
                $data['error'] = 'Invalid Email or Password';

                $this->load->view('login_view', $data);
            }
        } else {
            $this->load->view('login_view');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */
    public function register()
    {
        $this->redirect_if_logged();

        if ($this->input->post()) {
            $this->form_validation->set_rules(
                'name',
                'Name',
                'required|min_length[2]|max_length[100]'
            );

            $this->form_validation->set_rules(
                'shop_name',
                'Shop Name',
                'required|min_length[2]|max_length[150]'
            );

            $this->form_validation->set_rules(
                'email',
                'Email',
                'required|valid_email'
            );

            $this->form_validation->set_rules(
                'mobile',
                'Mobile Number',
                'required|numeric|min_length[10]|max_length[15]'
            );

            $this->form_validation->set_rules(
                'password',
                'Password',
                'required|min_length[6]'
            );

            $this->form_validation->set_rules(
                'confirm_password',
                'Confirm Password',
                'required|matches[password]'
            );

            if ($this->form_validation->run() == FALSE) {
                $data['error'] = validation_errors();

                $this->load->view('register_view', $data);
                return;
            }

            $email = trim($this->input->post('email', TRUE));

            $exists = $this->General_model->getOne(
                'users',
                array(
                    'email' => $email
                )
            );

            if ($exists) {
                $data['error'] = 'Email already exists';

                $this->load->view('register_view', $data);
                return;
            }

            $insert_data = array(

                'name'       => $this->input->post('name', TRUE),
                'shop_name'  => $this->input->post('shop_name', TRUE),
                'email'      => $email,
                'mobile'     => $this->input->post('mobile', TRUE),
                'password'   => password_hash(
                    $this->input->post('password'),
                    PASSWORD_DEFAULT
                ),
                'role'       => 1,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $insert_id = $this->General_model->insert(
                'users',
                $insert_data
            );

            if ($insert_id) {
                $this->session->set_flashdata(
                    'success',
                    'Registration Successful. Please Login.'
                );

                redirect('login');
            } else {
                $data['error'] = 'Registration Failed';

                $this->load->view('register_view', $data);
            }
        } else {
            $this->load->view('register_view');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */
    public function logout()
    {
        $this->session->unset_userdata('admin_id');
        $this->session->unset_userdata('admin_name');
        $this->session->unset_userdata('admin_email');
        $this->session->unset_userdata('admin_role');
        $this->session->unset_userdata('shop_name');

        $this->session->sess_destroy();

        redirect('login');
    }
}
