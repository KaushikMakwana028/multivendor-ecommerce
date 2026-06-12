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
            'upload'
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

        $data['page_title'] = 'Categories';

        $data['categories'] = $this->General_model->getAll(
            'categories',
            array(
                'user_id' => $user_id
            )
        );

        $this->load->view('includes/header', $data);
        $this->load->view('category_view', $data);
        $this->load->view('includes/footer', $data);
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
