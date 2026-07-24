<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Offers extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('admin_id')) redirect('admin');
        $this->load->helper(['form', 'url', 'file']);
        $this->load->library(['form_validation', 'upload', 'pagination']);
    }

    public function index()
    {
        $search   = trim($this->input->get('search', true) ?? '');
        $status   = $this->input->get('status', true);
        $type     = trim($this->input->get('type', true) ?? '');
        $featured = $this->input->get('featured', true);
        $sort     = trim($this->input->get('sort', true) ?? 'newest');
        $page     = (int)($this->input->get('page', true) ?: 1);
        $limit    = 10;
        $offset   = ($page - 1) * $limit;

        $this->db->from('offers');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('coupon_code', $search);
            $this->db->group_end();
        }
        if ($status !== null && $status !== '') $this->db->where('is_active', (int)$status);
        if ($type !== '') $this->db->where('offer_type', strtolower($type));
        if ($featured !== null && $featured !== '') $this->db->where('is_featured', (int)$featured);

        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

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

        $this->db->limit($limit, $offset);

        $data['offers']        = $this->db->get()->result_array();
        $data['pagination']    = $this->generate_pagination($page, $total_pages);
        $data['total_records'] = $total_records;
        $data['page_title']    = 'Offers Management';

        $this->load->view('includes/header', $data);
        $this->load->view('offer_list', $data);
        $this->load->view('includes/footer');
    }

    // AJAX: Get offers with filters and pagination
    public function get_offers()
    {
        $search   = trim($this->input->get_post('search', true) ?? '');
        $status   = $this->input->get_post('status', true);
        $type     = trim($this->input->get_post('type', true) ?? '');
        $featured = $this->input->get_post('featured', true);
        $sort     = trim($this->input->get_post('sort', true) ?? 'newest');
        $page     = (int)($this->input->get_post('page', true) ?: 1);
        $limit    = 10;
        $offset   = ($page - 1) * $limit;

        $this->db->from('offers');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('coupon_code', $search);
            $this->db->group_end();
        }
        if ($status !== null && $status !== '') $this->db->where('is_active', (int)$status);
        if ($type !== '') $this->db->where('offer_type', strtolower($type));
        if ($featured !== null && $featured !== '') $this->db->where('is_featured', (int)$featured);

        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

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

        $this->db->limit($limit, $offset);
        $offers = $this->db->get()->result_array();

        $html       = $this->generate_offers_html($offers, $offset);
        $pagination = $this->generate_pagination($page, $total_pages);

        echo json_encode([
            'status'        => true,
            'html'          => $html,
            'pagination'    => $pagination,
            'total_records' => $total_records,
            'current_page'  => $page,
            'total_pages'   => $total_pages,
            'csrf_hash'     => $this->security->get_csrf_hash()
        ]);
    }

    private function generate_offers_html($offers, $offset = 0)
    {
        if (empty($offers)) {
            return '
                <tr>
                    <td colspan="11" style="text-align:center; padding:50px; color:#666;">
                        <i class="fas fa-tag" style="font-size:42px; margin-bottom:14px; display:block; opacity:0.4;"></i>
                        <p style="font-size:14px; color:#999; margin:0;">No offers found.</p>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($offers as $i => $offer) {
            $serial = $offset + $i + 1;

            $img_html = !empty($offer['image'])
                ? '<img src="' . base_url('uploads/offers/' . $offer['image']) . '" style="width:80px;height:50px;border-radius:6px;object-fit:cover;border:1px solid var(--border-color);">'
                : '<div style="width:80px;height:50px;background:var(--light-gray);border-radius:6px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>';

            $coupon_html = !empty($offer['coupon_code'])
                ? '<span style="background:rgba(255,255,255,0.1);padding:4px 8px;border-radius:4px;">' . htmlspecialchars($offer['coupon_code'], ENT_QUOTES, 'UTF-8') . '</span>'
                : '-';

            $discount_html = ($offer['discount_type'] == 'percentage')
                ? '<strong>' . $offer['discount_value'] . '%</strong> off'
                : '<strong>₹' . number_format($offer['discount_value'], 2) . '</strong>';

            $end_date_html = '<small>' . date('d M Y', strtotime($offer['end_date'])) . '</small>';
            if ($offer['end_date'] < date('Y-m-d')) {
                $end_date_html .= '<br><span style="color:#ff9800;font-size:10px;">Expired</span>';
            }

            $status_html = $offer['is_active']
                ? '<span class="badge-active">Active</span>'
                : '<span class="badge-inactive">Inactive</span>';

            $featured_html = $offer['is_featured']
                ? '<span style="background:rgba(76,175,80,0.15);color:#81c784;padding:4px 10px;border-radius:4px;font-size:11px;">Featured</span>'
                : '';

            $actions_html = '
                <div style="display:flex;gap:5px;">
                    <a href="' . site_url('offers/edit/' . $offer['id']) . '" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                    <a href="' . site_url('offers/change_status/' . $offer['id']) . '" class="action-btn ' . ($offer['is_active'] ? 'delete' : 'view') . '" title="' . ($offer['is_active'] ? 'Deactivate' : 'Activate') . '"><i class="fas fa-' . ($offer['is_active'] ? 'eye-slash' : 'eye') . '"></i></a>
                    <a href="' . site_url('offers/toggle_featured/' . $offer['id']) . '" class="action-btn ' . ($offer['is_featured'] ? 'delete' : 'view') . '" title="' . ($offer['is_featured'] ? 'Unfeature' : 'Feature') . '"><i class="fas fa-' . ($offer['is_featured'] ? 'star' : 'star') . '"></i></a>
                    <a href="' . site_url('offers/delete/' . $offer['id']) . '" class="action-btn delete" title="Delete" onclick="return confirm(\'Delete this offer?\')"><i class="fas fa-trash-alt"></i></a>
                </div>
            ';

            $html .= '
                <tr>
                    <td style="color:#666;">' . $serial . '</td>
                    <td>' . $img_html . '</td>
                    <td><strong>' . htmlspecialchars($offer['title'], ENT_QUOTES, 'UTF-8') . '</strong><br><small style="color:#666;">' . htmlspecialchars(mb_substr($offer['subtitle'] ?? '', 0, 30), ENT_QUOTES, 'UTF-8') . '</small></td>
                    <td>' . $coupon_html . '</td>
                    <td><span class="offer-badge" style="background:rgba(33,150,243,0.15);color:#42a5f5;padding:4px 10px;border-radius:4px;font-size:11px;">' . ucfirst($offer['offer_type']) . '</span></td>
                    <td>' . $discount_html . '</td>
                    <td><small>' . ucfirst($offer['applicable_on']) . '</small></td>
                    <td>' . $end_date_html . '</td>
                    <td>' . $status_html . '</td>
                    <td>' . $featured_html . '</td>
                    <td>' . $actions_html . '</td>
                </tr>
            ';
        }
        return $html;
    }

    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html  = '<div class="custom-pagination">';
        $html .= '<div class="pagination-container">';

        if ($current_page > 1) {
            $html .= '<button class="pagination-btn" data-page="' . ($current_page - 1) . '" title="Previous"><i class="fas fa-chevron-left"></i></button>';
        } else {
            $html .= '<button class="pagination-btn disabled" disabled><i class="fas fa-chevron-left"></i></button>';
        }

        $start_page = max(1, $current_page - 1);
        $end_page   = min($total_pages, $start_page + 2);

        if ($end_page - $start_page < 2) {
            $start_page = max(1, $end_page - 2);
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $current_page ? 'active' : '';
            $html .= '<button class="pagination-btn ' . $active . '" data-page="' . $i . '">' . $i . '</button>';
        }

        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $html .= '<span class="pagination-dots">...</span>';
            }
            $html .= '<button class="pagination-btn" data-page="' . $total_pages . '">' . $total_pages . '</button>';
        }

        if ($current_page < $total_pages) {
            $html .= '<button class="pagination-btn" data-page="' . ($current_page + 1) . '" title="Next"><i class="fas fa-chevron-right"></i></button>';
        } else {
            $html .= '<button class="pagination-btn disabled" disabled><i class="fas fa-chevron-right"></i></button>';
        }

        $html .= '</div></div>';
        return $html;
    }

    public function create()
    {
        $data['categories'] = $this->db->where('is_active', 1)->get('categories')->result_array();
        $data['products'] = $this->db->where('is_active', 1)->get('products')->result_array();
        $data['page_title'] = 'Add Offer';

        $this->load->view('includes/header', $data);

        $this->load->view('offer_form', $data);
        $this->load->view('includes/footer');
    }

    public function store()
    {
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

    public function edit($id)
    {
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

    public function update($id)
    {
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

    public function delete($id)
    {
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

    public function change_status($id)
    {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        $this->db->where('id', $id)->update('offers', ['is_active' => $offer['is_active'] == 1 ? 0 : 1]);
        $this->session->set_flashdata('success', 'Offer status updated.');
        redirect('offers');
    }

    public function toggle_featured($id)
    {
        $offer = $this->db->get_where('offers', ['id' => $id])->row_array();
        if (!$offer) {
            $this->session->set_flashdata('error', 'Offer not found.');
            redirect('offers');
        }
        $this->db->where('id', $id)->update('offers', ['is_featured' => $offer['is_featured'] == 1 ? 0 : 1]);
        $this->session->set_flashdata('success', 'Offer featured status updated.');
        redirect('offers');
    }

    public function validate_end_date($end_date)
    {
        $start_date = $this->input->post('start_date');
        if (strtotime($end_date) < strtotime($start_date)) {
            $this->form_validation->set_message('validate_end_date', 'End date cannot be before start date.');
            return false;
        }
        return true;
    }
}
