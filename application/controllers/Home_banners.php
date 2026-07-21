<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_banners extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('admin_id')) {
            redirect('admin/login');
        }
        $this->load->helper(['form', 'url', 'file']);
        $this->load->library(['form_validation', 'upload', 'pagination']);
    }

    // Listing
    public function index()
    {
        $search = $this->input->get('search');
        $status = $this->input->get('status');
        $type = $this->input->get('type');
        $sort = $this->input->get('sort', true) ?: 'newest';

        $this->db->select('home_banners.*');
        $this->db->from('home_banners');

        // Search
        if ($search) {
            $this->db->like('title', $search);
        }

        // Filter by status
        if ($status !== null && $status !== '') {
            $this->db->where('is_active', $status);
        }

        // Filter by type
        if ($type) {
            $this->db->where('banner_type', $type);
        }

        // Sorting
        switch ($sort) {
            case 'oldest':
                $this->db->order_by('created_at', 'ASC');
                break;
            case 'display_order':
                $this->db->order_by('display_order', 'ASC');
                break;
            default:
                $this->db->order_by('created_at', 'DESC');
        }

        // Pagination
        $config['base_url'] = site_url('admin/home_banners/index');
        $config['total_rows'] = $this->db->count_all_results('', false);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = true;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->db->limit($config['per_page'], $page);

        $data['banners'] = $this->db->get()->result_array();
        $data['pagination'] = $this->pagination->create_links();
        $data['page_title'] = 'Home Banners';

        $this->load->view('includes/header', $data);
        $this->load->view('banner_view', $data);
        $this->load->view('includes/footer');
    }

    // Add Banner
    public function create()
    {
        $data['page_title'] = 'Add Banner';
        
        $this->load->view('includes/header', $data);
       
        $this->load->view('banner_form', $data);
        $this->load->view('includes/footer');
    }

    // Store Banner
    public function store()
    {
        $this->form_validation->set_rules('title', 'Banner Title', 'required|trim');
        $this->form_validation->set_rules('display_order', 'Display Order', 'numeric');
        $this->form_validation->set_rules('end_date', 'End Date', 'callback_check_end_date');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
            return;
        }

        // Image upload
        if (empty($_FILES['image']['name'])) {
            $this->session->set_flashdata('error', 'Banner image is required.');
            $this->create();
            return;
        }

        $config['upload_path'] = './uploads/banners/';
        $config['allowed_types'] = 'jpg|jpeg|png|webp';
        $config['max_size'] = 2048; // 2MB
        $config['file_name'] = time() . '_' . str_replace(' ', '_', $_FILES['image']['name']);

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('image')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            $this->create();
            return;
        }

        $upload_data = $this->upload->data();

        $data = [
            'title' => $this->input->post('title', true),
            'subtitle' => $this->input->post('subtitle', true),
            'image' => $upload_data['file_name'],
            'button_text' => $this->input->post('button_text', true),
            'button_link' => $this->input->post('button_link', true),
            'banner_type' => $this->input->post('banner_type', true),
            'display_order' => $this->input->post('display_order', true) ?: 0,
            'start_date' => $this->input->post('start_date') ?: null,
            'end_date' => $this->input->post('end_date') ?: null,
            'is_active' => $this->input->post('is_active', true) ? 1 : 0,
            'created_by' => $this->session->userdata('admin_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('home_banners', $data);

        $this->session->set_flashdata('success', 'Banner added successfully.');
        redirect('home_banners');
    }

    // Edit Banner
    public function edit($id)
    {
        $data['banner'] = $this->db->get_where('home_banners', ['id' => $id])->row_array();

        if (!$data['banner']) {
            $this->session->set_flashdata('error', 'Banner not found.');
            redirect('admin/home_banners');
        }

        $data['page_title'] = 'Edit Banner';

        $this->load->view('includes/header', $data);
        $this->load->view('banner_form', $data);
        $this->load->view('includes/footer');
    }

    // Update Banner
    public function update($id)
    {
        $banner = $this->db->get_where('home_banners', ['id' => $id])->row_array();

        if (!$banner) {
            $this->session->set_flashdata('error', 'Banner not found.');
            redirect('home_banners');
        }

        $this->form_validation->set_rules('title', 'Banner Title', 'required|trim');
        $this->form_validation->set_rules('display_order', 'Display Order', 'numeric');
        $this->form_validation->set_rules('end_date', 'End Date', 'callback_check_end_date');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
            return;
        }

        $data = [
            'title' => $this->input->post('title', true),
            'subtitle' => $this->input->post('subtitle', true),
            'button_text' => $this->input->post('button_text', true),
            'button_link' => $this->input->post('button_link', true),
            'banner_type' => $this->input->post('banner_type', true),
            'display_order' => $this->input->post('display_order', true) ?: 0,
            'start_date' => $this->input->post('start_date') ?: null,
            'end_date' => $this->input->post('end_date') ?: null,
            'is_active' => $this->input->post('is_active', true) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads/banners/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp';
            $config['max_size'] = 2048;
            $config['file_name'] = time() . '_' . str_replace(' ', '_', $_FILES['image']['name']);

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('image')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                $this->edit($id);
                return;
            }

            // Delete old image
            if (file_exists('./uploads/banners/' . $banner['image'])) {
                unlink('./uploads/banners/' . $banner['image']);
            }

            $upload_data = $this->upload->data();
            $data['image'] = $upload_data['file_name'];
        }

        $this->db->where('id', $id)->update('home_banners', $data);

        $this->session->set_flashdata('success', 'Banner updated successfully.');
        redirect('home_banners');
    }

    // Delete Banner
    public function delete($id)
    {
        $banner = $this->db->get_where('home_banners', ['id' => $id])->row_array();

        if (!$banner) {
            $this->session->set_flashdata('error', 'Banner not found.');
            redirect('home_banners');
        }

        // Delete image
        if (file_exists('./uploads/banners/' . $banner['image'])) {
            unlink('./uploads/banners/' . $banner['image']);
        }

        $this->db->where('id', $id)->delete('home_banners');

        $this->session->set_flashdata('success', 'Banner deleted successfully.');
        redirect('home_banners');
    }

    // Change Status
    public function change_status($id)
    {
        $banner = $this->db->get_where('home_banners', ['id' => $id])->row_array();

        if (!$banner) {
            $this->session->set_flashdata('error', 'Banner not found.');
            redirect('home_banners');
        }

        $new_status = $banner['is_active'] == 1 ? 0 : 1;

        $this->db->where('id', $id)->update('home_banners', [
            'is_active' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->session->set_flashdata('success', 'Banner status updated successfully.');
        redirect('home_banners');
    }

    // Validation callback for end date
    public function check_end_date($end_date)
    {
        $start_date = $this->input->post('start_date');

        if ($end_date && $start_date && strtotime($end_date) < strtotime($start_date)) {
            $this->form_validation->set_message('check_end_date', 'End date cannot be less than start date.');
            return false;
        }

        return true;
    }
}