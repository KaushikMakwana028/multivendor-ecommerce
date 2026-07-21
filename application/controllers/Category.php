<?php defined('BASEPATH') or exit('No direct script access allowed');

class Category extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->file(APPPATH . 'controllers/Login.php');
        Login::check_login();

        $this->load->model('General_model');
        $this->load->library(array(
            'session',
            'form_validation',
            'upload',
            'pagination'
        ));

        $this->load->helper(array(
            'url',
            'form'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Category List
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user_id = $this->session->userdata('admin_id');

        $search = trim($this->input->get('search', true) ?? '');
        $status = $this->input->get('status', true);
        $sort   = trim($this->input->get('sort', true) ?? 'newest');
        $page   = (int)($this->input->get('page', true) ?: 1);
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $this->db->from('categories');
        $this->db->where('user_id', $user_id);

        if ($search !== '') {
            $this->db->like('name', $search);
        }

        if ($status !== null && $status !== '') {
            $this->db->where('is_active', (int)$status);
        }

        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

        switch ($sort) {
            case 'oldest':
                $this->db->order_by('created_at', 'ASC');
                break;
            case 'name_asc':
                $this->db->order_by('name', 'ASC');
                break;
            default:
                $this->db->order_by('created_at', 'DESC');
        }

        $this->db->limit($limit, $offset);

        $data['categories']    = $this->db->get()->result();
        $data['pagination']    = $this->generate_pagination($page, $total_pages);
        $data['total_records'] = $total_records;
        $data['page_title']    = 'Categories';

        $this->load->view('includes/header', $data);
        $this->load->view('category_view', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: Get Categories with filters and pagination
    |--------------------------------------------------------------------------
    */
    public function get_categories()
    {
        $user_id = $this->session->userdata('admin_id');

        $search = trim($this->input->get_post('search', true) ?? '');
        $status = $this->input->get_post('status', true);
        $sort   = trim($this->input->get_post('sort', true) ?? 'newest');
        $page   = (int)($this->input->get_post('page', true) ?: 1);
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $this->db->from('categories');
        $this->db->where('user_id', $user_id);

        if ($search !== '') {
            $this->db->like('name', $search);
        }

        if ($status !== null && $status !== '') {
            $this->db->where('is_active', (int)$status);
        }

        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

        switch ($sort) {
            case 'oldest':
                $this->db->order_by('created_at', 'ASC');
                break;
            case 'name_asc':
                $this->db->order_by('name', 'ASC');
                break;
            default:
                $this->db->order_by('created_at', 'DESC');
        }

        $this->db->limit($limit, $offset);
        $categories = $this->db->get()->result();

        $html       = $this->generate_categories_html($categories, $offset);
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

    private function generate_categories_html($categories, $offset = 0)
    {
        if (empty($categories)) {
            return '
                <tr>
                    <td colspan="6" style="text-align:center; padding:50px; color:#666;">
                        <i class="fas fa-list" style="font-size:42px; margin-bottom:14px; display:block; opacity:0.4;"></i>
                        <p style="font-size:14px; color:#999; margin:0;">No categories found.</p>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($categories as $i => $cat) {
            $serial = $offset + $i + 1;

            $img_html = !empty($cat->image)
                ? '<img src="' . base_url('uploads/categories/' . $cat->image) . '" style="width:42px;height:42px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">'
                : '<div style="width:42px;height:42px;background:var(--light-gray);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);color:#555;"><i class="fas fa-image"></i></div>';

            $status_html = $cat->is_active
                ? '<span class="badge-active">Active</span>'
                : '<span class="badge-inactive">Inactive</span>';

            $created_html = !empty($cat->created_at)
                ? date('d M Y', strtotime($cat->created_at))
                : '-';

            $actions_html = '
                <a href="' . site_url('category/edit/' . $cat->id) . '" class="action-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                <a href="' . site_url('category/delete/' . $cat->id) . '" class="action-btn delete" title="Delete" onclick="return confirm(\'Delete this category?\')"><i class="fas fa-trash-alt"></i></a>
            ';

            $html .= '
                <tr>
                    <td style="color:#666;">' . $serial . '</td>
                    <td>' . $img_html . '</td>
                    <td style="font-weight:600;">' . htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8') . '</td>
                    <td>' . $status_html . '</td>
                    <td style="color:#666;font-size:12px;">' . $created_html . '</td>
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

    /*
    |--------------------------------------------------------------------------
    | Add Category Form
    |--------------------------------------------------------------------------
    */
    public function add()
    {
        $data['page_title'] = 'Add Category';

        $this->load->view('includes/header', $data);
        $this->load->view('category_add', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Category
    |--------------------------------------------------------------------------
    */
    public function store()
    {
        $this->form_validation->set_rules(
            'name',
            'Category Name',
            'required|min_length[2]|max_length[150]'
        );

        if ($this->form_validation->run() == FALSE) {
            $data['page_title'] = 'Add Category';
            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('category_add', $data);
            $this->load->view('includes/footer', $data);

            return;
        }

        $user_id = $this->session->userdata('admin_id');

        $image = '';

        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/categories/', 0777, TRUE);

            $config['upload_path']   = './uploads/categories/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp|gif';
            $config['max_size']      = 2048;
            $config['file_name']     = 'cat_' . time();

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                $image = $this->upload->data('file_name');
            }
        }

        $insert = array(
            'user_id'    => $user_id,
            'name'       => $this->input->post('name', TRUE),
            'image'      => $image,
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->General_model->insert(
            'categories',
            $insert
        );

        $this->session->set_flashdata(
            'success',
            'Category Added Successfully.'
        );

        redirect('category');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Category
    |--------------------------------------------------------------------------
    */
    public function edit($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $category = $this->General_model->getOne(
            'categories',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$category) {
            show_404();
        }

        $data['page_title'] = 'Edit Category';
        $data['category']   = $category;

        $this->load->view('includes/header', $data);
        $this->load->view('category_edit', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */
    public function update($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $category = $this->General_model->getOne(
            'categories',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$category) {
            show_404();
        }

        $this->form_validation->set_rules(
            'name',
            'Category Name',
            'required|min_length[2]|max_length[150]'
        );

        if ($this->form_validation->run() == FALSE) {
            $data['page_title'] = 'Edit Category';
            $data['category'] = $category;
            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('category_edit', $data);
            $this->load->view('includes/footer', $data);

            return;
        }

        $image = $category->image;

        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/categories/', 0777, TRUE);

            $config['upload_path']   = './uploads/categories/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp|gif';
            $config['max_size']      = 2048;
            $config['file_name']     = 'cat_' . time();

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                if (!empty($category->image)) {
                    @unlink(
                        './uploads/categories/' .
                            $category->image
                    );
                }

                $image = $this->upload->data('file_name');
            }
        }

        $update = array(
            'name'       => $this->input->post('name', TRUE),
            'image'      => $image,
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->General_model->update(
            'categories',
            array(
                'id' => $id,
                'user_id' => $user_id
            ),
            $update
        );

        $this->session->set_flashdata(
            'success',
            'Category Updated Successfully.'
        );

        redirect('category');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */
    public function delete($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $category = $this->General_model->getOne(
            'categories',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$category) {
            show_404();
        }

        if (!empty($category->image)) {
            @unlink(
                './uploads/categories/' .
                    $category->image
            );
        }

        $this->General_model->delete(
            'categories',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        $this->session->set_flashdata(
            'success',
            'Category Deleted Successfully.'
        );

        redirect('category');
    }
}
