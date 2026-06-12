<?php defined('BASEPATH') or exit('No direct script access allowed');

class Product extends CI_Controller
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
    | Product List
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user_id = $this->session->userdata('admin_id');

        $this->db->select('
            p.*,
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

        $data['products'] = $this->db
            ->get()
            ->result_array();

        $data['page_title'] = 'Products';

        $this->load->view('includes/header', $data);
        $this->load->view('product_view', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Add Product Form
    |--------------------------------------------------------------------------
    */
    public function add()
    {
        $user_id = $this->session->userdata('admin_id');

        $data['categories'] = $this->General_model->getAll(
            'categories',
            array(
                'user_id' => $user_id,
                'is_active' => 1
            )
        );

        $data['page_title'] = 'Add Product';

        $this->load->view('includes/header', $data);
        $this->load->view('product_add', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Product
    |--------------------------------------------------------------------------
    */
    public function store()
    {
        $this->form_validation->set_rules(
            'category_id',
            'Category',
            'required'
        );

        $this->form_validation->set_rules(
            'name',
            'Product Name',
            'required|min_length[2]|max_length[200]'
        );

        $this->form_validation->set_rules(
            'mrp',
            'MRP',
            'required|numeric'
        );

        $this->form_validation->set_rules(
            'price',
            'Price',
            'required|numeric'
        );

        $this->form_validation->set_rules(
            'stock',
            'Stock',
            'required|integer'
        );

        if ($this->form_validation->run() == FALSE) {
            $user_id = $this->session->userdata('admin_id');

            $data['categories'] = $this->General_model->getAll(
                'categories',
                array(
                    'user_id' => $user_id,
                    'is_active' => 1
                )
            );

            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('product_add', $data);
            $this->load->view('includes/footer', $data);

            return;
        }

        $user_id = $this->session->userdata('admin_id');

        $image = '';

        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/products/', 0777, TRUE);

            $config['upload_path']   = './uploads/products/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size']      = 2048;
            $config['file_name']     = 'prod_' . time();

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                $image = $this->upload->data('file_name');
            }
        }

        $insert = array(

            'user_id'     => $user_id,
            'category_id' => $this->input->post('category_id'),

            'name'        => $this->input->post('name', TRUE),
            'image'       => $image,

            'description' => $this->input->post('description', TRUE),

            'mrp'         => $this->input->post('mrp'),
            'price'       => $this->input->post('price'),

            'stock'       => $this->input->post('stock'),

            'is_active'   => $this->input->post('is_active') ? 1 : 0,

            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->General_model->insert(
            'products',
            $insert
        );

        $this->session->set_flashdata(
            'success',
            'Product Added Successfully.'
        );

        redirect('product');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */
    public function edit($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $product = $this->General_model->getOne(
            'products',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$product) {
            show_404();
        }

        $data['product'] = $product;

        $data['categories'] = $this->General_model->getAll(
            'categories',
            array(
                'user_id' => $user_id,
                'is_active' => 1
            )
        );

        $data['page_title'] = 'Edit Product';

        $this->load->view('includes/header', $data);
        $this->load->view('product_edit', $data);
        $this->load->view('includes/footer', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */
    public function update($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $product = $this->General_model->getOne(
            'products',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$product) {
            show_404();
        }

        $image = $product->image;

        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/products/', 0777, TRUE);

            $config['upload_path']   = './uploads/products/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size']      = 2048;
            $config['file_name']     = 'prod_' . time();

            $this->upload->initialize($config);

            if ($this->upload->do_upload('image')) {
                if (!empty($product->image)) {
                    @unlink(
                        './uploads/products/' .
                            $product->image
                    );
                }

                $image = $this->upload->data('file_name');
            }
        }

        $update = array(

            'category_id' => $this->input->post('category_id'),

            'name'        => $this->input->post('name', TRUE),

            'image'       => $image,

            'description' => $this->input->post('description', TRUE),

            'mrp'         => $this->input->post('mrp'),

            'price'       => $this->input->post('price'),

            'stock'       => $this->input->post('stock'),

            'is_active'   => $this->input->post('is_active') ? 1 : 0,

            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->General_model->update(
            'products',
            array(
                'id' => $id,
                'user_id' => $user_id
            ),
            $update
        );

        $this->session->set_flashdata(
            'success',
            'Product Updated Successfully.'
        );

        redirect('product');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */
    public function delete($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $product = $this->General_model->getOne(
            'products',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        if (!$product) {
            show_404();
        }

        if (!empty($product->image)) {
            @unlink(
                './uploads/products/' .
                    $product->image
            );
        }

        $this->General_model->delete(
            'products',
            array(
                'id' => $id,
                'user_id' => $user_id
            )
        );

        $this->session->set_flashdata(
            'success',
            'Product Deleted Successfully.'
        );

        redirect('product');
    }
}
