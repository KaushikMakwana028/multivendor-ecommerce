<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home_banners extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('admin_id')) {
            redirect('admin');
        }
        $this->load->helper(['form', 'url', 'file']);
        $this->load->library(['form_validation', 'upload', 'pagination']);
    }

    // Listing
    public function index()
    {
        $search = trim($this->input->get('search', true) ?? '');
        $status = $this->input->get('status', true);
        $type   = trim($this->input->get('type', true) ?? '');
        $sort   = trim($this->input->get('sort', true) ?? 'newest');

        $this->db->select('home_banners.*');
        $this->db->from('home_banners');

        if ($search !== '') {
            $this->db->like('title', $search);
        }

        if ($status !== null && $status !== '') {
            $this->db->where('is_active', (int)$status);
        }

        if ($type !== '') {
            $this->db->where('banner_type', strtolower($type));
        }

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

        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / 10);
        $page          = (int)($this->input->get('page', true) ?: 1);
        $offset        = ($page - 1) * 10;

        $this->db->limit(10, $offset);

        $data['banners']       = $this->db->get()->result_array();
        $data['pagination']    = $this->generate_pagination($page, $total_pages);
        $data['total_records'] = $total_records;
        $data['page_title']    = 'Home Banners';

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

    // AJAX: Get banners with filters and pagination
    public function get_banners()
    {
        $search = trim($this->input->get_post('search', true) ?? '');
        $status = $this->input->get_post('status', true);
        $type   = strtolower(trim($this->input->get_post('type', true) ?? ''));
        $sort   = trim($this->input->get_post('sort', true) ?? 'newest');
        $page   = (int)($this->input->get_post('page', true) ?: 1);
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $this->db->select('home_banners.*');
        $this->db->from('home_banners');

        if ($search !== '') {
            $this->db->like('title', $search);
        }

        if ($status !== null && $status !== '') {
            $this->db->where('is_active', (int)$status);
        }

        if ($type !== '') {
            $this->db->where('banner_type', $type);
        }

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
        $banners = $this->db->get()->result_array();

        $html       = $this->generate_banners_html($banners, $offset);
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

    private function generate_banners_html($banners, $offset = 0)
    {
        if (empty($banners)) {
            return '
                <tr>
                    <td colspan="10" style="text-align:center; padding:50px; color:#666;">
                        <i class="fas fa-images" style="font-size:42px; margin-bottom:14px; display:block; opacity:0.4;"></i>
                        <p style="font-size:14px; color:#999; margin:0;">No banners found.</p>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($banners as $i => $banner) {
            $serial = $offset + $i + 1;

            // Image
            if (!empty($banner['image'])) {
                $img_html = '<img src="' . base_url('uploads/banners/' . $banner['image']) . '" style="width:100px;height:60px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);" alt="' . htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') . '">';
            } else {
                $img_html = '<div style="width:100px;height:60px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>';
            }

            // Subtitle & button
            $sub_html = '';
            if (!empty($banner['subtitle'])) {
                $sub_text = mb_strlen($banner['subtitle']) > 50 ? mb_substr($banner['subtitle'], 0, 50) . '...' : $banner['subtitle'];
                $sub_html .= '<div style="font-size:11px;color:#999;margin-top:3px;">' . htmlspecialchars($sub_text, ENT_QUOTES, 'UTF-8') . '</div>';
            }
            if (!empty($banner['button_text'])) {
                $sub_html .= '<div style="font-size:10px;color:var(--primary-red);margin-top:3px;"><i class="fas fa-link"></i> ' . htmlspecialchars($banner['button_text'], ENT_QUOTES, 'UTF-8') . '</div>';
            }

            // Type badge
            $type_badges = [
                'home'     => ['class' => 'badge-home', 'icon' => 'home'],
                'offer'    => ['class' => 'badge-offer', 'icon' => 'tags'],
                'category' => ['class' => 'badge-category', 'icon' => 'th-large'],
                'product'  => ['class' => 'badge-product', 'icon' => 'box']
            ];
            $type_badge = $type_badges[$banner['banner_type']] ?? $type_badges['home'];
            $type_html = '<span class="banner-type-badge ' . $type_badge['class'] . '"><i class="fas fa-' . $type_badge['icon'] . '"></i> ' . ucfirst($banner['banner_type']) . '</span>';

            // Start & End dates
            $start_date_html = !empty($banner['start_date']) ? '<div style="font-size:12px;color:#ccc;">' . date('d M Y', strtotime($banner['start_date'])) . '</div>' : '<span style="color:#666;">-</span>';

            $end_date_html = '<span style="color:#666;">-</span>';
            if (!empty($banner['end_date'])) {
                $end_date_html = '<div style="font-size:12px;color:#ccc;">' . date('d M Y', strtotime($banner['end_date'])) . '</div>';
                if ($banner['end_date'] < date('Y-m-d')) {
                    $end_date_html .= '<span style="font-size:10px;color:#e57373;">Expired</span>';
                }
            }

            // Status
            $status_html = $banner['is_active']
                ? '<span class="badge-active">Active</span>'
                : '<span class="badge-inactive">Inactive</span>';

            // Created
            $created_html = '<div style="font-size:12px;color:#ccc;">' . date('d M Y', strtotime($banner['created_at'])) . '</div><div style="font-size:11px;color:#666;">' . date('h:i A', strtotime($banner['created_at'])) . '</div>';

            // Actions
            $actions_html = '
                <div style="display:flex;gap:5px;">
                    <a href="' . site_url('home_banners/edit/' . $banner['id']) . '" class="action-btn edit" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <a href="' . site_url('home_banners/change_status/' . $banner['id']) . '" class="action-btn ' . ($banner['is_active'] ? 'delete' : 'view') . '" title="' . ($banner['is_active'] ? 'Deactivate' : 'Activate') . '">
                        <i class="fas fa-' . ($banner['is_active'] ? 'eye-slash' : 'eye') . '"></i>
                    </a>
                    <a href="' . site_url('home_banners/delete/' . $banner['id']) . '" class="action-btn delete" title="Delete" onclick="return confirm(\'Are you sure you want to delete this banner? This will also delete the uploaded image.\')">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            ';

            $html .= '
                <tr>
                    <td style="color:#666;">' . $serial . '</td>
                    <td>' . $img_html . '</td>
                    <td><div style="font-weight:600;">' . htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') . '</div>' . $sub_html . '</td>
                    <td>' . $type_html . '</td>
                    <td><span style="background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;font-weight:600;font-size:13px;">' . $banner['display_order'] . '</span></td>
                    <td>' . $start_date_html . '</td>
                    <td>' . $end_date_html . '</td>
                    <td>' . $status_html . '</td>
                    <td>' . $created_html . '</td>
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
}
