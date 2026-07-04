<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function privacy_policy(): void
    {
        $this->load->view('pages/privacy_policy', [
            'page_title' => 'Privacy Policy',
            'api_url'    => site_url('api/privacy_policy'),
        ]);
    }

    public function terms_conditions(): void
    {
        $this->load->view('pages/terms_conditions', [
            'page_title' => 'Terms & Conditions',
            'api_url'    => site_url('api/terms_conditions'),
        ]);
    }

    public function refund_policy(): void
    {
        $this->load->view('pages/refund_policy', [
            'page_title' => 'Refund Policy',
            'api_url'    => site_url('api/refund_policy'),
        ]);
    }

    public function delete_account(): void
    {
        $this->load->view('pages/delete_account');
    }
}
