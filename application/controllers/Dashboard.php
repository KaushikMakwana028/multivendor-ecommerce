<?php defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->file(APPPATH . 'controllers/Login.php');
        Login::check_login();
        $this->load->model('General_model');
        $this->load->library(['session']);
        $this->load->helper(['url']);
    }

    public function index()
    {
        $user_id = $this->session->userdata('admin_id');

        // REVENUE STATS
        $total_rev = $this->db
            ->select('SUM(total_amount) as total')
            ->where('payment_status', 'paid')
            ->get('orders')->row();
        $data['total_revenue'] = (float)($total_rev->total ?? 0);

        $today_rev = $this->db
            ->select('SUM(total_amount) as total')
            ->where('payment_status', 'paid')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->get('orders')->row();
        $data['today_revenue'] = (float)($today_rev->total ?? 0);

        $month_rev = $this->db
            ->select('SUM(total_amount) as total')
            ->where('payment_status', 'paid')
            ->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))
            ->get('orders')->row();
        $data['month_revenue'] = (float)($month_rev->total ?? 0);

        // ORDER STATS
        $data['total_orders'] = $this->db->count_all_results('orders');
        $data['pending_orders'] = $this->db->where('status', 'pending')->count_all_results('orders');
        $data['processing_orders'] = $this->db->where_in('status', ['processing', 'confirmed', 'shipped'])->count_all_results('orders');
        $data['completed_orders'] = $this->db->where('status', 'delivered')->count_all_results('orders');

        // PRODUCT STATS
        $data['total_products'] = $this->db->count_all_results('products');
        $data['active_products'] = $this->db->where('is_active', 1)->count_all_results('products');
        $data['low_stock_count'] = $this->db->where('stock <', 100)->count_all_results('products');
        $data['out_of_stock'] = $this->db->where('stock', 0)->count_all_results('products');

        // CUSTOMER STATS
        $data['total_customers'] = $this->db->where('role', 0)->count_all_results('users');

        // RECENT ORDERS
        $this->db->select('orders.*, users.name as customer_name, users.mobile');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
        $this->db->order_by('orders.id', 'DESC');
        $this->db->limit(8);
        $data['recent_orders'] = $this->db->get()->result_array();

        // LOW STOCK PRODUCTS
        $this->db->select('id, name, stock, image');
        $this->db->where('stock <', 100);
        $this->db->order_by('stock', 'ASC');
        $this->db->limit(5);
        $data['low_stock_products'] = $this->db->get('products')->result_array();

        // REVENUE CHART DATA (Last 7 Days)
        $revenue_chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $rev_row = $this->db
                ->select('SUM(total_amount) as total')
                ->where('payment_status', 'paid')
                ->where('DATE(created_at)', $date)
                ->get('orders')->row();
            
            $revenue_chart[] = [
                'date' => date('M d', strtotime($date)),
                'amount' => (float)($rev_row->total ?? 0)
            ];
        }
        $data['revenue_chart'] = json_encode($revenue_chart);

        // ORDER STATUS CHART
        $data['order_status_chart'] = json_encode([
            'pending' => $data['pending_orders'],
            'processing' => $data['processing_orders'],
            'completed' => $data['completed_orders']
        ]);

        $data['page_title'] = 'Dashboard';

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
            redirect('admin');
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
