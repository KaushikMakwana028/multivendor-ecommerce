<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_pages extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Check if admin is logged in
        if (!$this->session->userdata('admin_id')) {
            redirect('admin');
        }

        $this->load->model('General_model');
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'form_validation']);
    }

    /**
     * List all policy pages
     */
    public function index(): void
    {
        $data['pages'] = $this->db->order_by('id', 'ASC')->get('policy_pages')->result_array();
        $data['page_title'] = 'Policy Pages';

        $this->load->view('includes/header', $data);
        $this->load->view('admin_pages/list', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Show edit form
     */
    public function edit($id): void
    {
        $page = $this->General_model->getById('policy_pages', $id);
        if (!$page) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('admin/pages');
        }

        $data['page'] = $page;
        $data['page_title'] = 'Edit ' . $page->title;

        $this->load->view('includes/header', $data);
        $this->load->view('admin_pages/edit', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Process page update
     */
    public function update($id): void
    {
        $page = $this->General_model->getById('policy_pages', $id);
        if (!$page) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('admin/pages');
        }

        $this->form_validation->set_rules('title', 'Page Title', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('content', 'Content', 'required');

        if ($this->form_validation->run() == FALSE) {
            $data['page'] = $page;
            $data['page_title'] = 'Edit ' . $page->title;
            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('admin_pages/edit', $data);
            $this->load->view('includes/footer');
        } else {
            $update_data = [
                'title'      => $this->input->post('title', TRUE),
                'content'    => $this->input->post('content'), // Keep raw HTML from editor
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->General_model->update('policy_pages', ['id' => $id], $update_data);

            $this->session->set_flashdata('success', 'Page updated successfully.');
            redirect('admin/pages');
        }
    }

    /**
     * Show & edit Help & Support settings
     */
    public function help_support(): void
    {
        $settings = $this->db->get_where('help_support', ['id' => 1])->row();
        
        // If settings don't exist, create default row
        if (!$settings) {
            $default_data = [
                'id'              => 1,
                'phone_number'    => '',
                'email'           => '',
                'whatsapp_number' => '',
                'telegram_link'   => '',
                'instagram_link'  => '',
                'facebook_link'   => '',
                'youtube_link'    => ''
            ];
            $this->db->insert('help_support', $default_data);
            $settings = (object) $default_data;
        }

        // If phone_number or email in help_support is empty, fetch them from the admin profile
        if (empty($settings->phone_number) || empty($settings->email)) {
            $admin_id = $this->session->userdata('admin_id');
            $admin = $this->db->get_where('users', ['id' => $admin_id])->row();
            if ($admin) {
                if (empty($settings->phone_number)) {
                    $settings->phone_number = $admin->mobile ?? '';
                }
                if (empty($settings->email)) {
                    $settings->email = $admin->email ?? '';
                }
            }
        }

        $data['settings']   = $settings;
        $data['page_title'] = 'Help & Support';

        $this->load->view('includes/header', $data);
        $this->load->view('admin_pages/help_support', $data);
        $this->load->view('includes/footer');
    }

    /**
     * Process Help & Support update
     */
    public function update_help_support(): void
    {
        $this->form_validation->set_rules('phone_number', 'Phone Number', 'trim|max_length[50]');
        $this->form_validation->set_rules('email', 'Email Address', 'trim|max_length[100]');
        $this->form_validation->set_rules('whatsapp_number', 'WhatsApp Number', 'trim|max_length[50]');
        $this->form_validation->set_rules('telegram_link', 'Telegram Link', 'trim|max_length[255]');
        $this->form_validation->set_rules('instagram_link', 'Instagram Link', 'trim|max_length[255]');
        $this->form_validation->set_rules('facebook_link', 'Facebook Link', 'trim|max_length[255]');
        $this->form_validation->set_rules('youtube_link', 'YouTube Link', 'trim|max_length[255]');

        if ($this->form_validation->run() == FALSE) {
            $data['settings'] = (object) [
                'phone_number'    => $this->input->post('phone_number'),
                'email'           => $this->input->post('email'),
                'whatsapp_number' => $this->input->post('whatsapp_number'),
                'telegram_link'   => $this->input->post('telegram_link'),
                'instagram_link'  => $this->input->post('instagram_link'),
                'facebook_link'   => $this->input->post('facebook_link'),
                'youtube_link'    => $this->input->post('youtube_link'),
            ];
            $data['page_title'] = 'Help & Support';
            $data['error']      = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('admin_pages/help_support', $data);
            $this->load->view('includes/footer');
        } else {
            $admin_id = $this->session->userdata('admin_id');
            $admin = $this->db->get_where('users', ['id' => $admin_id])->row();

            $phone_input = $this->input->post('phone_number', TRUE);
            $email_input = $this->input->post('email', TRUE);

            if (empty($phone_input)) {
                $phone_input = null;
            } elseif ($admin && $phone_input === $admin->mobile) {
                $phone_input = null;
            }

            if (empty($email_input)) {
                $email_input = null;
            } elseif ($admin && $email_input === $admin->email) {
                $email_input = null;
            }

            $update_data = [
                'phone_number'    => $phone_input,
                'email'           => $email_input,
                'whatsapp_number' => $this->input->post('whatsapp_number', TRUE),
                'telegram_link'   => $this->input->post('telegram_link', TRUE),
                'instagram_link'  => $this->input->post('instagram_link', TRUE),
                'facebook_link'   => $this->input->post('facebook_link', TRUE),
                'youtube_link'    => $this->input->post('youtube_link', TRUE),
            ];

            $this->db->update('help_support', $update_data, ['id' => 1]);

            $this->session->set_flashdata('success', 'Help & Support settings updated successfully.');
            redirect('admin/pages/help-support');
        }
    }
}
