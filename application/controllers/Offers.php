<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('admin_id')) redirect('admin/login');
        $this->load->helper(['form', 'url', 'file']);
        $this->load->library(['form_validation', 'upload','pagination']);
    }

    public function index() {
        $search = $this->input->get('search');
        $status = $this->input->get('status');
        $type = $this->input->get('type');
        $featured = $this->input->get('featured');
        $sort = $this->input->get('sort', true) ?: 'newest';

        $this->db->from('offers');
        if ($search) $this->db->like('title', $search)->or_like('coupon_code', $search);
        if ($status !== null && $status !== '') $this->db->where('is_active', $status);
        if ($type) $this->db->where('offer_type', $type);
        if ($featured !== null && $featured !== '') $this->db->where('is_featured', $featured);

        $total = $this->db->count_all_results('', false);
        
        switch ($sort) {
            case 'oldest': $this->db->order_by('created_at', 'ASC'); break;
            case 'display_order': $this->db->order_by('display_order', 'ASC'); break;
            default: $this->db->order_by('created_at', 'DESC');
        }

        $config = ['base_url' => site_url('admin/offers'), 'total_rows' => $total, 'per_page' => 10, 'uri_segment' => 3, 'reuse_query_string' => true];
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ?: 0;
        $this->db->limit(10, $page);

        $data['offers'] = $this->db->get()->result_array();
        $data['pagination'] = $this->pagination->create_links();
        $data['page_title'] = 'Offers Management';

        $this->load->view('includes/header', $data);
        $this->load->view('offer_list', $data);
        $this->load->view('includes/footer');
    }

    public function create() {
        $data['categories'] = $this->db->where('is_active', 1)->get('categories')->result_array();
        $data['products'] = $this->db->where('is_active', 1)->get('products')->result_array();
        $data['page_title'] = 'Add Offer';

        $this->load->view('includes/header', $data);
       
        $this->load->view('offer_form', $data);
        $this->load->view('includes/footer');
    }

    public function store() {
        $this->form_validation->set_rules('title', 'Offer Title', 'required');
        $this->form_validation->set_rules('offer_type', 'Offer Type', 'required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|callback_validate_end_date');
        $this->form_validation->set_rules('coupon_code', 'Coupon Code', 'is_unique[offers.coupon_code]');
        $this->form_validation->set_rules('display_order', 'Display Order', 'numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
            return;
        }

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $config = ['upload_path' => './uploads/offers/', 'allowed_types' => 'jpg|jpeg|png|webp', 'max_size' => 2048, 'file_name' => time() . '_' . basename($_FILES['image']['name'])];
            !is_dir($config['upload_path']) && mkdir($config['upload_path'], 0777, true);
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('image')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                $this->create();
                return;
            }
            $image = $this->upload->data('file_name');
        }

        $data = [
            'title' => $this->input->post('title', true),
            'subtitle' => $this->input->post('subtitle', true),
            'description' => $this->input->post('description', true),
            'offer_type' => $this->input->post('offer_type', true),
            'discount_type' => $this->input->post('discount_type', true),
            'discount_value' => $this->input->post('discount_value') ?: null,
            'minimum_order_amount' => $this->input->post('minimum_order_amount') ?: 0,
            'maximum_discount' => $this->input->post('maximum_discount') ?: null,
            'coupon_code' => $this->input->post('coupon_code', true),
            'image' => $image,
            'applicable_on' => $this->input->post('applicable_on', true),
            'category_id' => $this->input->post('category_id') ?: null,
            'product_id' => $this->input->post('product_id') ?: null,
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'display_order' => $this->input->post('display_order') ?: 0,
            'is_featured' => $this->input->post('is_featured') ? 1 : 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'created_by' => $this->session->userdata('admin_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('offers', $data);
        $this->session->set_flashdata('success', 'Offer added successfully.');
        redirect('offers');
    }

    public function edit($id) {
        $data['offer'] = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$data['offer']) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        $data['categories'] = $this->db->where('is_active', 1)->get('categories')->result_array();
        $data['products'] = $this->db->where('is_active', 1)->get('products')->result_array();
        $data['page_title'] = 'Edit Offer';

        $this->load->view('includes/header', $data);
        $this->load->view('offer_form', $data);
        $this->load->view('includes/footer');
    }

    public function update($id) {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }

        $this->form_validation->set_rules('title', 'Offer Title', 'required');
        $this->form_validation->set_rules('offer_type', 'Offer Type', 'required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|callback_validate_end_date');
        $this->form_validation->set_rules('coupon_code', 'Coupon Code', 'is_unique[offers.coupon_code,id,' . $id . ']');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
            return;
        }

        $data = [
            'title' => $this->input->post('title', true),
            'subtitle' => $this->input->post('subtitle', true),
            'description' => $this->input->post('description', true),
            'offer_type' => $this->input->post('offer_type', true),
            'discount_type' => $this->input->post('discount_type', true),
            'discount_value' => $this->input->post('discount_value') ?: null,
            'minimum_order_amount' => $this->input->post('minimum_order_amount') ?: 0,
            'maximum_discount' => $this->input->post('maximum_discount') ?: null,
            'coupon_code' => $this->input->post('coupon_code', true),
            'applicable_on' => $this->input->post('applicable_on', true),
            'category_id' => $this->input->post('category_id') ?: null,
            'product_id' => $this->input->post('product_id') ?: null,
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'display_order' => $this->input->post('display_order') ?: 0,
            'is_featured' => $this->input->post('is_featured') ? 1 : 0,
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($_FILES['image']['name'])) {
            $config = ['upload_path' => './uploads/offers/', 'allowed_types' => 'jpg|jpeg|png|webp', 'max_size' => 2048, 'file_name' => time() . '_' . basename($_FILES['image']['name'])];
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('image')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                $this->edit($id);
                return;
            }
            if (file_exists('./uploads/offers/' . $offer['image'])) unlink('./uploads/offers/' . $offer['image']);
            $data['image'] = $this->upload->data('file_name');
        }

        $this->db->where('id', $id)->update('offers', $data);
        $this->session->set_flashdata('success', 'Offer updated successfully.');
        redirect('offers');
    }

    public function delete($id) {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        if (!empty($offer['image']) && file_exists('./uploads/offers/' . $offer['image'])) unlink('./uploads/offers/' . $offer['image']);
        $this->db->delete('offers', ['id' => $id]);
        $this->session->set_flashdata('success', 'Offer deleted successfully.');
        redirect('offers');
    }

    public function change_status($id) {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        $this->db->where('id', $id)->update('offers', ['is_active' => $offer['is_active'] == 1 ? 0 : 1]);
        $this->session->set_flashdata('success', 'Offer status updated.');
        redirect('offers');
    }

    public function toggle_featured($id) {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        $this->db->where('id', $id)->update('offers', ['is_featured' => $offer['is_featured'] == 1 ? 0 : 1]);
        $this->session->set_flashdata('success', 'Offer featured status updated.');
        redirect('offers');
    }

    public function validate_end_date($end_date) {
        $start_date = $this->input->post('start_date');
        if (strtotime($end_date) < strtotime($start_date)) {
            $this->form_validation->set_message('validate_end_date', 'End date cannot be before start date.');
            return false;
        }
        return true;
    }
}