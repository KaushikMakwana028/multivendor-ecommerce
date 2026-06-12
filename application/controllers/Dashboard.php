<?php defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->file(APPPATH . 'controllers/Login.php');
        Login::check_login();
        $this->load->model('General_model');
        $this->load->library(array('session', 'form_validation', 'upload'));
        $this->load->helper(array('url', 'form'));
    }

    public function index()
    {
        $user_id = $this->session->userdata('admin_id');

        /*
        |--------------------------------------------------------------------------
        | Dashboard Counts
        |--------------------------------------------------------------------------
        */
        $data['total_categories'] = $this->General_model->getCount(
            'categories',
            array(
                'user_id' => $user_id
            )
        );

        $data['total_products'] = $this->General_model->getCount(
            'products',
            array(
                'user_id' => $user_id
            )
        );

        $data['active_products'] = $this->General_model->getCount(
            'products',
            array(
                'user_id'   => $user_id,
                'is_active' => 1
            )
        );

        $data['inactive_products'] = $this->General_model->getCount(
            'products',
            array(
                'user_id'   => $user_id,
                'is_active' => 0
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Recent Products
        |--------------------------------------------------------------------------
        */
        $this->db->select('
            p.id,
            p.name,
            p.image,
            p.mrp,
            p.price,
            p.stock,
            p.is_active,
            c.name as category_name
        ');

        $this->db->from('products p');

        $this->db->join(
            'categories c',
            'c.id = p.category_id',
            'left'
        );

        $this->db->where(
            'p.user_id',
            $user_id
        );

        $this->db->order_by(
            'p.id',
            'DESC'
        );

        $this->db->limit(5);

        $data['recent_products'] = $this->db
            ->get()
            ->result_array();

        /*
        |--------------------------------------------------------------------------
        | Page Title
        |--------------------------------------------------------------------------
        */
        $data['page_title'] = 'Dashboard';

        /*
        |--------------------------------------------------------------------------
        | Load View
        |--------------------------------------------------------------------------
        */
        $this->load->view('includes/header', $data);
        $this->load->view('dashboard_view', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit/View User Profile
    |--------------------------------------------------------------------------
    */
    public function profile()
    {
        $user_id = $this->session->userdata('admin_id');
        $user = $this->General_model->getOne('users', array('id' => $user_id));

        if (!$user) {
            redirect('login');
        }

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

            if ($this->input->post('password')) {
                $this->form_validation->set_rules(
                    'password',
                    'Password',
                    'min_length[6]'
                );

                $this->form_validation->set_rules(
                    'confirm_password',
                    'Confirm Password',
                    'matches[password]'
                );
            }

            if ($this->form_validation->run() == TRUE) {
                // Email uniqueness check excluding current user
                $email = trim($this->input->post('email', TRUE));
                $email_exists = $this->General_model->getOne('users', array(
                    'email' => $email,
                    'id !=' => $user_id
                ));

                if ($email_exists) {
                    $data['error'] = 'Email is already used by another account';
                } else {
                    $image = $user->image;

                    if (!empty($_FILES['image']['name'])) {
                        @mkdir('./uploads/profile/', 0777, TRUE);

                        $config['upload_path']   = './uploads/profile/';
                        $config['allowed_types'] = 'jpg|jpeg|png|webp';
                        $config['max_size']      = 2048;
                        $config['file_name']     = 'profile_' . $user_id . '_' . time();

                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('image')) {
                            if (!empty($user->image) && file_exists('./uploads/profile/' . $user->image)) {
                                @unlink('./uploads/profile/' . $user->image);
                            }
                            $image = $this->upload->data('file_name');
                        }
                    }

                    $update_data = array(
                        'name'       => $this->input->post('name', TRUE),
                        'shop_name'  => $this->input->post('shop_name', TRUE),
                        'email'      => $email,
                        'mobile'     => $this->input->post('mobile', TRUE),
                        'address'    => $this->input->post('address', TRUE),
                        'image'      => $image,
                        'updated_at' => date('Y-m-d H:i:s')
                    );

                    if ($this->input->post('password')) {
                        $update_data['password'] = password_hash(
                            $this->input->post('password'),
                            PASSWORD_DEFAULT
                        );
                    }

                    $this->General_model->update('users', array('id' => $user_id), $update_data);

                    // Update session
                    $this->session->set_userdata('admin_name', $update_data['name']);
                    $this->session->set_userdata('admin_email', $update_data['email']);
                    $this->session->set_userdata('shop_name', $update_data['shop_name']);
                    $this->session->set_userdata('admin_image', $image);

                    $this->session->set_flashdata('success', 'Profile Updated Successfully.');
                    redirect('profile');
                }
            } else {
                $data['error'] = validation_errors();
            }
        }

        $data['user'] = $this->General_model->getOne('users', array('id' => $user_id));
        $data['page_title'] = 'Profile';

        $this->load->view('includes/header', $data);
        $this->load->view('profile_view', $data);
        $this->load->view('includes/footer', $data);
    }
}
