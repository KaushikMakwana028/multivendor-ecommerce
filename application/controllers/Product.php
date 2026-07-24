<?php defined('BASEPATH') or exit('No direct script access allowed');

class Product extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->file(APPPATH . 'controllers/Login.php');
        Login::check_login();
        $this->load->model('General_model');
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $user_id = $this->session->userdata('admin_id');
        $data['categories'] = $this->db->where('user_id', $user_id)->get('categories')->result_array();
        $data['page_title'] = 'Products';

        $this->load->view('includes/header', $data);
        $this->load->view('product_view', $data);
        $this->load->view('includes/footer', $data);
    }

    public function ajax_list()
    {
        $user_id = $this->session->userdata('admin_id');

        $page = (int)($this->input->get('page') ?? 1);
        $search = $this->input->get('search') ?? '';
        $category_id = $this->input->get('category_id') ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $this->db->select('p.*, c.name as category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.user_id', $user_id);

        if ($search) {
            $this->db->like('p.name', $search);
        }

        if ($category_id) {
            $this->db->where('p.category_id', $category_id);
        }

        $total = $this->db->count_all_results('', false);

        $this->db->order_by('p.id', 'DESC');
        $this->db->limit($limit, $offset);
        $products = $this->db->get()->result_array();

        $total_pages = ceil($total / $limit);

        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total
            ]
        ]);
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
        $this->form_validation->set_rules('category_id', 'Category', 'required');
        $this->form_validation->set_rules('name', 'Product Name', 'required|min_length[2]|max_length[200]');
        $this->form_validation->set_rules('mrp', 'MRP', 'required|numeric');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('stock', 'Stock', 'required|integer');
        $this->form_validation->set_rules(
            'gst_percent',
            'GST',
            'required|integer|greater_than_equal_to[1]|less_than_equal_to[24]'
        );
        $this->form_validation->set_rules('hsn_code', 'HSN Code', 'trim|max_length[50]');

        if ($this->form_validation->run() == FALSE) {
            $user_id = $this->session->userdata('admin_id');

            $data['categories'] = $this->General_model->getAll(
                'categories',
                array('user_id' => $user_id, 'is_active' => 1)
            );

            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('product_add', $data);
            $this->load->view('includes/footer', $data);
            return;
        }

        $user_id = $this->session->userdata('admin_id');

        $uploaded_images = $this->_upload_multiple_images('image');

        $primary_image = !empty($uploaded_images) ? $uploaded_images[0] : '';

        $insert = array(
            'user_id'     => $user_id,
            'category_id' => $this->input->post('category_id'),
            'name'        => $this->input->post('name', TRUE),
            'image'       => $primary_image,
            'description' => $this->input->post('description', TRUE),
            'mrp'         => $this->input->post('mrp'),
            'price'       => $this->input->post('price'),
            'gst_percent' => $this->input->post('gst_percent'),
            'hsn_code'    => $this->input->post('hsn_code', TRUE),
            'stock'       => $this->input->post('stock'),
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->General_model->insert('products', $insert);

        $product_id = $this->db->insert_id();

        // Remaining uploaded images (index 1 onwards) go to gallery
        if (count($uploaded_images) > 1) {
            for ($i = 1; $i < count($uploaded_images); $i++) {
                $this->General_model->insert('product_images', array(
                    'product_id' => $product_id,
                    'image'      => $uploaded_images[$i],
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
        }

        $this->session->set_flashdata('success', 'Product Added Successfully.');
        redirect('product');
    }

    public function edit($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');

        $product = $this->General_model->getOne(
            'products',
            array('id' => $id, 'user_id' => $user_id)
        );

        if (!$product) {
            show_404();
        }

        $data['product'] = $product;

        $data['categories'] = $this->General_model->getAll(
            'categories',
            array('user_id' => $user_id, 'is_active' => 1)
        );

        // gallery images (excluding primary, which lives on products.image)
        $data['product_images'] = $this->General_model->getAll(
            'product_images',
            array('product_id' => $id)
        );

        $this->load->view('includes/header', $data);
        $this->load->view('product_edit', $data);
        $this->load->view('includes/footer', $data);
    }

    public function update($id = 0)
    {
        $user_id = $this->session->userdata('admin_id');
        $product = $this->General_model->getOne(
            'products',
            array('id' => $id, 'user_id' => $user_id)
        );

        if (!$product) {
            show_404();
        }

        $image = $product->image;

        // 1. Remove primary image if requested (demote it to gallery instead of deleting from disk)
        if ($this->input->post('remove_primary') && !empty($product->image)) {
            $exists = $this->General_model->exists('product_images', [
                'product_id' => $id,
                'image'      => $product->image
            ]);
            if (!$exists) {
                $this->General_model->insert('product_images', array(
                    'product_id' => $id,
                    'image'      => $product->image,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
            $image = '';
        }
        $this->form_validation->set_rules('category_id', 'Category', 'required');
        $this->form_validation->set_rules('name', 'Product Name', 'required|min_length[2]|max_length[200]');
        $this->form_validation->set_rules('mrp', 'MRP', 'required|numeric');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('gst_percent', 'GST', 'required|integer|greater_than_equal_to[1]|less_than_equal_to[24]');
        $this->form_validation->set_rules('hsn_code', 'HSN Code', 'trim|max_length[50]');
        $this->form_validation->set_rules('stock', 'Stock', 'required|integer');

        if ($this->form_validation->run() == FALSE) {

            $data['product'] = $product;

            $data['categories'] = $this->General_model->getAll(
                'categories',
                array(
                    'user_id' => $user_id,
                    'is_active' => 1
                )
            );

            $data['product_images'] = $this->General_model->getAll(
                'product_images',
                array('product_id' => $id)
            );

            $data['error'] = validation_errors();

            $this->load->view('includes/header', $data);
            $this->load->view('product_edit', $data);
            $this->load->view('includes/footer');

            return;
        }


        // 2. Delete selected gallery images
        $delete_ids = $this->input->post('delete_images');
        if (!is_array($delete_ids)) {
            $delete_ids = [];
        }

        // 2.5 Check if user set a gallery image as primary
        $set_primary_id = $this->input->post('set_primary_image_id');
        if (!empty($set_primary_id)) {
            $gallery_img = $this->General_model->getOne(
                'product_images',
                array('id' => $set_primary_id, 'product_id' => $id)
            );

            if ($gallery_img) {
                $old_primary = $image;
                $new_primary = $gallery_img->image;

                $image = $new_primary;

                if (!empty($old_primary)) {
                    $this->General_model->update(
                        'product_images',
                        array('id' => $set_primary_id, 'product_id' => $id),
                        array('image' => $old_primary)
                    );

                    // Exclude from deletion
                    $delete_ids = array_diff($delete_ids, array($set_primary_id));
                } else {
                    $this->General_model->delete(
                        'product_images',
                        array('id' => $set_primary_id, 'product_id' => $id)
                    );
                }
            }
        }

        if (!empty($delete_ids) && is_array($delete_ids)) {
            foreach ($delete_ids as $img_id) {
                $img_row = $this->General_model->getOne(
                    'product_images',
                    array('id' => $img_id, 'product_id' => $id)
                );

                if ($img_row) {
                    @unlink('./uploads/products/' . $img_row->image);

                    $this->General_model->delete(
                        'product_images',
                        array('id' => $img_id, 'product_id' => $id)
                    );
                }
            }
        }

        // 3. Upload any new images
        $uploaded_images = $this->_upload_multiple_images('image');

        if (!empty($uploaded_images)) {

            // If there is currently no primary image, first new upload becomes primary
            if (empty($image)) {
                $image = $uploaded_images[0];
                array_shift($uploaded_images); // remove it from the array so it isn't also added to gallery
            }

            // Remaining new uploads go to gallery
            foreach ($uploaded_images as $new_img) {
                $this->General_model->insert('product_images', array(
                    'product_id' => $id,
                    'image'      => $new_img,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }
        }

        $update = array(
            'category_id' => $this->input->post('category_id'),
            'name'        => $this->input->post('name', TRUE),
            'image'       => $image,
            'description' => $this->input->post('description', TRUE),
            'mrp'         => $this->input->post('mrp'),
            'price'       => $this->input->post('price'),
            'gst_percent' => $this->input->post('gst_percent'),
            'hsn_code'    => $this->input->post('hsn_code', TRUE),
            'stock'       => $this->input->post('stock'),
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->General_model->update(
            'products',
            array('id' => $id, 'user_id' => $user_id),
            $update
        );

        $this->session->set_flashdata('success', 'Product Updated Successfully.');
        redirect('product');
    }

    /**
     * Helper: uploads multiple files from a given $_FILES[] field name
     * and returns an array of saved filenames.
     */
    private function _upload_multiple_images($field_name)
    {
        $uploaded_images = array();

        if (empty($_FILES[$field_name]['name'][0])) {
            return $uploaded_images;
        }

        @mkdir('./uploads/products/', 0777, TRUE);

        $files = $_FILES[$field_name];
        $file_count = count($files['name']);

        for ($i = 0; $i < $file_count; $i++) {

            if (empty($files['name'][$i])) {
                continue;
            }

            $_FILES['single_image']['name']     = $files['name'][$i];
            $_FILES['single_image']['type']     = $files['type'][$i];
            $_FILES['single_image']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['single_image']['error']    = $files['error'][$i];
            $_FILES['single_image']['size']     = $files['size'][$i];

            $config['upload_path']   = './uploads/products/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size']      = 2048;
            $config['file_name']     = 'prod_' . time() . '_' . $i . '_' . rand(1000, 9999);

            $this->upload->initialize($config, TRUE);

            if ($this->upload->do_upload('single_image')) {
                $uploaded_images[] = $this->upload->data('file_name');
            }
        }

        return $uploaded_images;
    }
    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */
    // public function edit($id = 0)
    // {
    //     $user_id = $this->session->userdata('admin_id');

    //     $product = $this->General_model->getOne(
    //         'products',
    //         array(
    //             'id' => $id,
    //             'user_id' => $user_id
    //         )
    //     );

    //     if (!$product) {
    //         show_404();
    //     }

    //     $data['product'] = $product;

    //     $data['categories'] = $this->General_model->getAll(
    //         'categories',
    //         array(
    //             'user_id' => $user_id,
    //             'is_active' => 1
    //         )
    //     );

    //     $data['page_title'] = 'Edit Product';

    //     $this->load->view('includes/header', $data);
    //     $this->load->view('product_edit', $data);
    //     $this->load->view('includes/footer', $data);
    // }

    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */
    // public function update($id = 0)
    // {
    //     $user_id = $this->session->userdata('admin_id');

    //     $product = $this->General_model->getOne(
    //         'products',
    //         array(
    //             'id' => $id,
    //             'user_id' => $user_id
    //         )
    //     );

    //     if (!$product) {
    //         show_404();
    //     }

    //     $image = $product->image;

    //     if (!empty($_FILES['image']['name'])) {
    //         @mkdir('./uploads/products/', 0777, TRUE);

    //         $config['upload_path']   = './uploads/products/';
    //         $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
    //         $config['max_size']      = 2048;
    //         $config['file_name']     = 'prod_' . time();

    //         $this->upload->initialize($config);

    //         if ($this->upload->do_upload('image')) {
    //             if (!empty($product->image)) {
    //                 @unlink(
    //                     './uploads/products/' .
    //                         $product->image
    //                 );
    //             }

    //             $image = $this->upload->data('file_name');
    //         }
    //     }

    //     $update = array(

    //         'category_id' => $this->input->post('category_id'),

    //         'name'        => $this->input->post('name', TRUE),

    //         'image'       => $image,

    //         'description' => $this->input->post('description', TRUE),

    //         'mrp'         => $this->input->post('mrp'),

    //         'price'       => $this->input->post('price'),

    //         'stock'       => $this->input->post('stock'),

    //         'is_active'   => $this->input->post('is_active') ? 1 : 0,

    //         'updated_at'  => date('Y-m-d H:i:s')
    //     );

    //     $this->General_model->update(
    //         'products',
    //         array(
    //             'id' => $id,
    //             'user_id' => $user_id
    //         ),
    //         $update
    //     );

    //     $this->session->set_flashdata(
    //         'success',
    //         'Product Updated Successfully.'
    //     );

    //     redirect('product');
    // }

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
