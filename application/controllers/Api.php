<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('OTP_FIXED_MODE', false); // ✅ Live mode — real random OTP sent via SMS

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require_once FCPATH . 'vendor/autoload.php';

class Api extends CI_Controller
{
    /*-----------------------------------------------------------------------
    | Config
    |-----------------------------------------------------------------------*/
    private $jwt_secret = 'b7c1f3e9a2d64c58f19a8e73d0bcb52f8edc6b31a9f71e48d9a7e2f3c1a5b8e9';
    private $jwt_expiry = 365 * 24 * 60 * 60; // 1 year

    /*-----------------------------------------------------------------------
    | Boot
    |-----------------------------------------------------------------------*/
    public function __construct()
    {
        parent::__construct();

        $this->load->model('General_model');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Content-Type: application/json; charset=UTF-8');

        // Handle browser preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /*=======================================================================
    | PRIVATE HELPERS
    |=======================================================================*/

    /**
     * Send unified JSON response and stop execution.
     */
    private function send_response(bool $status, string $message, $data = null, int $http_code = 200): void
    {
        http_response_code($http_code);
        echo json_encode([
            'status'  => $status,
            'code'    => $http_code,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Validate Bearer JWT from Authorization header.
     * Returns the decoded token data object.
     * Stops execution with 401 if token is missing/invalid.
     */
    private function validate_token(): object
    {
        $header = $this->input->get_request_header('Authorization', true);

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $this->send_response(false, 'Authorization header missing. Please login first.', null, 401);
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwt_secret, 'HS256'));
        } catch (Exception $e) {
            $this->send_response(false, 'Token is invalid or expired. Please login again.', null, 401);
        }

        if (empty($decoded->data->id)) {
            $this->send_response(false, 'Token data is invalid.', null, 401);
        }

        return $decoded->data;
    }

    /**
     * Generate a signed JWT token for a user.
     */
    private function generate_token($user): string
    {
        $id     = is_array($user) ? $user['id']     : $user->id;
        $name   = is_array($user) ? $user['name']   : $user->name;
        $email  = is_array($user) ? $user['email']  : $user->email;
        $mobile = is_array($user) ? ($user['mobile'] ?? '') : ($user->mobile ?? '');
        $role   = is_array($user) ? $user['role']   : $user->role;

        $payload = [
            'iss'  => base_url(),
            'iat'  => time(),
            'exp'  => time() + $this->jwt_expiry,
            'data' => compact('id', 'name', 'email', 'mobile', 'role'),
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    /**
     * Read raw JSON body. Returns array.
     */
    private function get_json_body(): array
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        return is_array($raw) ? $raw : [];
    }

    /**
     * Read one field: JSON body → POST → GET (priority order).
     */
    private function get_field(string $key, string $default = ''): string
    {
        $body = $this->get_json_body();
        if (isset($body[$key])) return trim((string) $body[$key]);

        $post = $this->input->post($key);
        if ($post !== false && $post !== null) return trim((string) $post);

        $get = $this->input->get($key);
        if ($get !== false && $get !== null) return trim((string) $get);

        return $default;
    }

    /*=======================================================================
    | OTP HELPERS  — DB-based (copied from working reference, no session)
    |   Requires table: user_login_otps (mobile, otp, expires_at)
    |=======================================================================*/

    /**
     * Send OTP via SMS gateway (mobicomm.dove-sms.com).
     * Copied exactly from reference project.
     * In OTP_FIXED_MODE the HTTP call is skipped — OTP is always '123456'.
     */
    private function send_otp_via_sms(string $mobileNo, string $otp): bool
    {
        if (OTP_FIXED_MODE) {
            log_message('info', '[DEV] OTP for ' . $mobileNo . ' is: ' . $otp);
            return true;
        }

        $message = "Hi $mobileNo\n\nYour Verification OTP is $otp Do not share this OTP with anyone for security reasons.\n\nRegards\nOMKARENT";

        $params = [
            'user'     => 'Fitcketsp',
            'key'      => '81a6b2f99cXX',
            'mobile'   => '91' . $mobileNo,
            'message'  => $message,
            'senderid' => 'OENTER',
            'accusage' => '1',
            'entityid' => '1401487200000053882',
            'tempid'   => '1407168611506367587',
        ];

        $url = 'http://mobicomm.dove-sms.com/submitsms.jsp?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            log_message('error', 'OTP SMS cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        log_message('info', "OTP sent to $mobileNo. Response: $response");

        return true;
    }

    /*=======================================================================
    | IMAGE URL HELPERS
    |=======================================================================*/

    private function get_category_image_url(?string $filename): string
    {
        return !empty($filename) ? base_url('uploads/categories/' . $filename) : '';
    }

    private function get_product_image_url(?string $filename): string
    {
        return !empty($filename) ? base_url('uploads/products/' . $filename) : '';
    }

    private function get_user_image_url(?string $filename): string
    {
        return !empty($filename) ? base_url('uploads/users/' . $filename) : '';
    }

    /*=======================================================================
    | AUTH ENDPOINTS
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | SEND OTP  (Mobile Login)
    | POST /api/send_otp
    | Body: { "mobile": "9876543210" }
    | → Finds user by mobile, stores OTP against user_id in DB, sends SMS
    |-----------------------------------------------------------------------*/
    public function send_otp(): void
    {
        // ── Read input (JSON body or POST form-data) ──────────────────────
        $input_data = $this->get_json_body();
        if (!empty($input_data)) {
            $_POST = $input_data;
        }

        $mobile = trim($this->input->post('mobile') ?? '');

        // ── Validate ──────────────────────────────────────────────────────
        if (empty($mobile)) {
            $this->send_response(false, 'Mobile number is required.', null, 400);
        }

        if (!is_numeric($mobile) || strlen($mobile) !== 10) {
            $this->send_response(false, 'Please enter a valid 10-digit mobile number.', null, 400);
        }

        // ── Check user exists ─────────────────────────────────────────────
        $user = $this->db->get_where('users', ['mobile' => $mobile])->row();

        if (!$user) {
            $this->send_response(false, 'This mobile number is not registered. Please register first.', null, 404);
        }

        if ((int) $user->is_active !== 1) {
            $this->send_response(false, 'Your account is inactive. Please contact support.', null, 403);
        }

        // ── Generate OTP ──────────────────────────────────────────────────
        $otp = OTP_FIXED_MODE ? '123456' : (string) rand(100000, 999999);

        // ── Clear old OTPs for this user_id ───────────────────────────────
        $this->db->where('user_id', (int) $user->id)->delete('user_login_otps');

        // ── Insert new OTP against user_id (not mobile) ───────────────────
        $this->db->insert('user_login_otps', [
            'user_id'    => (int) $user->id,
            'otp'        => $otp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
        ]);

        // ── Send SMS ──────────────────────────────────────────────────────
        $this->send_otp_via_sms($mobile, $otp);

        $this->send_response(true, 'OTP sent successfully to your mobile number.', [
            'masked_mobile' => '*******' . substr($mobile, -4),
            'expires_in'    => '5 minutes',
        ]);
    }

    /*-----------------------------------------------------------------------
    | VERIFY OTP  (Mobile Login)
    | POST /api/verify_otp
    | Body: { "mobile": "9876543210", "otp": "123456" }
    | → Finds user by mobile → checks OTP by user_id → returns JWT token
    |-----------------------------------------------------------------------*/
    public function verify_otp(): void
    {
        // ── Read input ────────────────────────────────────────────────────
        $input_data = $this->get_json_body();
        if (!empty($input_data)) {
            $_POST = $input_data;
        }

        $mobile      = trim($this->input->post('mobile') ?? '');
        $entered_otp = trim($this->input->post('otp')    ?? '');

        // ── Validate ──────────────────────────────────────────────────────
        if (empty($mobile) || empty($entered_otp)) {
            $this->send_response(false, 'Both mobile number and OTP are required.', null, 400);
        }

        // ── Sanitise mobile ───────────────────────────────────────────────
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        $mobile = substr($mobile, -10);

        // ── Find user by mobile first ─────────────────────────────────────
        $user = $this->db->get_where('users', [
            'mobile'    => $mobile,
            'is_active' => 1,
        ])->row();

        if (!$user) {
            $this->send_response(false, 'User not found or account is inactive.', null, 404);
        }

        // ── Look up OTP by user_id (not mobile) ───────────────────────────
        $otp_row = $this->db
            ->where('user_id', (int) $user->id)
            ->where('otp', $entered_otp)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->get('user_login_otps')
            ->row();

        if (!$otp_row) {
            $this->send_response(false, 'OTP is incorrect or has expired. Please request a new OTP.', null, 400);
        }

        // ── Generate JWT ──────────────────────────────────────────────────
        $token = $this->generate_token($user);

        // ── Cleanup used OTP row ──────────────────────────────────────────
        $this->db->where('id', $otp_row->id)->delete('user_login_otps');

        $this->send_response(true, 'OTP verified. Login successful.', [
            'token' => $token,
            'user'  => [
                'id'        => (int) $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'mobile'    => $user->mobile,
                'shop_name' => $user->shop_name ?? '',
                'image'     => $this->get_user_image_url($user->image ?? ''),
                'role'      => (int) $user->role,
            ],
        ]);
    }

    /*-----------------------------------------------------------------------
    | REGISTER USER
    | POST /api/register_user
    | Body: { "name", "email", "mobile", "password", "shop_name" }
    | → Creates a new user account and returns JWT token
    |-----------------------------------------------------------------------*/
    public function register_user(): void
    {
        $body      = $this->get_json_body();
        $name      = trim($body['name']      ?? $this->input->post('name')      ?? '');
        $email     = trim($body['email']     ?? $this->input->post('email')     ?? '');
        $mobile    = trim($body['mobile']    ?? $this->input->post('mobile')    ?? '');
        $password  =      $body['password']  ?? $this->input->post('password')  ?? '';
        $shop_name = trim($body['shop_name'] ?? $this->input->post('shop_name') ?? '');

        // ── Required field validation ─────────────────────────────────────
        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            $this->send_response(false, 'name, email, mobile and password are all required.', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->send_response(false, 'Please enter a valid email address.', null, 400);
        }

        if (!is_numeric($mobile) || strlen($mobile) !== 10) {
            $this->send_response(false, 'Mobile number must be exactly 10 digits.', null, 400);
        }

        if (strlen($password) < 6) {
            $this->send_response(false, 'Password must be at least 6 characters long.', null, 400);
        }

        // ── Duplicate checks ──────────────────────────────────────────────
        if ($this->db->get_where('users', ['email' => $email])->row()) {
            $this->send_response(false, 'This email address is already registered.', null, 400);
        }

        if ($this->db->get_where('users', ['mobile' => $mobile])->row()) {
            $this->send_response(false, 'This mobile number is already registered.', null, 400);
        }

        // ── Insert new user ───────────────────────────────────────────────
        $this->db->insert('users', [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'shop_name'  => $shop_name,
            'role'       => 0,         // 0 = regular user, 1 = admin
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $new_user_id = $this->db->insert_id();

        if (!$new_user_id) {
            $this->send_response(false, 'Registration failed. Please try again.', null, 500);
        }

        // ── Return user WITHOUT token (user must login via OTP) ───────────
        $this->send_response(true, 'Registration successful. Please login using OTP sent to your mobile.', [
            'user' => [
                'id'        => (int) $new_user_id,
                'name'      => $name,
                'email'     => $email,
                'mobile'    => $mobile,
                'shop_name' => $shop_name,
                'role'      => 0,
            ],
        ], 201);
    }


    /*=======================================================================
    | USER PROFILE ENDPOINTS
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | GET MY PROFILE
    | GET /api/get_my_profile  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_my_profile(): void
    {
        $token_data = $this->validate_token();

        $user = $this->db->get_where('users', [
            'id'        => $token_data->id,
            'is_active' => 1,
        ])->row();

        if (!$user) {
            $this->send_response(false, 'User account not found.', null, 404);
        }

        $this->send_response(true, 'Profile fetched successfully.', [
            'id'         => (int) $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'mobile'     => $user->mobile ?? '',
            'shop_name'  => $user->shop_name ?? '',
            'image'      => $this->get_user_image_url($user->image ?? ''),
            'address'    => $user->address ?? '',
            'role'       => (int) $user->role,
            'is_active'  => (int) $user->is_active,
            'created_at' => $user->created_at,
        ]);
    }

    /*-----------------------------------------------------------------------
    | UPDATE MY PROFILE
    | POST /api/update_my_profile  [Auth required]
    | Body: { "name", "email", "shop_name", "address" }
    | → Supports optional image upload via multipart/form-data
    |-----------------------------------------------------------------------*/
    public function update_my_profile(): void
    {
        $token_data = $this->validate_token();

        $body      = $this->get_json_body();
        $name      = trim($body['name']      ?? $this->input->post('name')      ?? '');
        $email     = trim($body['email']     ?? $this->input->post('email')     ?? '');
        $shop_name = trim($body['shop_name'] ?? $this->input->post('shop_name') ?? '');
        $address   = trim($body['address']   ?? $this->input->post('address')   ?? '');

        if (empty($name) || empty($email)) {
            $this->send_response(false, 'name and email are required fields.', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->send_response(false, 'Please enter a valid email address.', null, 400);
        }

        // Check email not taken by someone else
        $email_taken = $this->db
            ->where('email', $email)
            ->where('id !=', $token_data->id)
            ->get('users')
            ->row();

        if ($email_taken) {
            $this->send_response(false, 'This email is already used by another account.', null, 400);
        }

        $update_data = [
            'name'       => $name,
            'email'      => $email,
            'shop_name'  => $shop_name,
            'address'    => $address,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Optional profile image upload
        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/users/', 0777, true);
            $upload_config = [
                'upload_path'   => './uploads/users/',
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 2048,
                'file_name'     => 'user_' . $token_data->id . '_' . time(),
            ];
            $this->load->library('upload', $upload_config);
            if ($this->upload->do_upload('image')) {
                $update_data['image'] = $this->upload->data('file_name');
            }
        }

        $this->db->where('id', $token_data->id)->update('users', $update_data);

        $updated_user = $this->db->get_where('users', ['id' => $token_data->id])->row();

        $this->send_response(true, 'Profile updated successfully.', [
            'id'        => (int) $updated_user->id,
            'name'      => $updated_user->name,
            'email'     => $updated_user->email,
            'mobile'    => $updated_user->mobile ?? '',
            'shop_name' => $updated_user->shop_name ?? '',
            'image'     => $this->get_user_image_url($updated_user->image ?? ''),
            'address'   => $updated_user->address ?? '',
        ]);
    }

    /*=======================================================================
    | CATEGORY ENDPOINTS
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | GET CATEGORY LIST
    | GET /api/get_category_list  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_category_list(): void
    {
        // $this->validate_token(); ← REMOVED

        $rows = $this->db
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('categories')
            ->result();

        $category_list = array_map(function ($cat) {
            return [
                'id'    => (int) $cat->id,
                'name'  => $cat->name,
                'image' => $this->get_category_image_url($cat->image ?? ''),
            ];
        }, $rows);

        $this->send_response(true, 'Category list fetched successfully.', [
            'total_categories' => count($category_list),
            'categories'       => $category_list,
        ]);
    }

    /*-----------------------------------------------------------------------
    | GET CATEGORY DETAIL
    | GET /api/get_category_detail/{id}  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_category_detail(int $category_id = 0): void
    {
        // $this->validate_token(); ← REMOVED

        if ($category_id <= 0) {
            $this->send_response(false, 'A valid category ID is required.', null, 400);
        }

        $category = $this->db->get_where('categories', [
            'id'        => $category_id,
            'is_active' => 1,
        ])->row();

        if (!$category) {
            $this->send_response(false, 'Category not found.', null, 404);
        }

        $total_products_in_category = $this->db->where([
            'category_id' => $category_id,
            'is_active'   => 1,
        ])->count_all_results('products');

        $this->send_response(true, 'Category detail fetched successfully.', [
            'id'                         => (int) $category->id,
            'name'                       => $category->name,
            'image'                      => $this->get_category_image_url($category->image ?? ''),
            'is_active'                  => (int) $category->is_active,
            'total_products_in_category' => $total_products_in_category,
            'created_at'                 => $category->created_at,
            'updated_at'                 => $category->updated_at,
        ]);
    }

    /*=======================================================================
    | PRODUCT ENDPOINTS
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | GET PRODUCTS BY CATEGORY
    | GET /api/get_products_by_category/{id}  [Auth required]  ?page=1
    |-----------------------------------------------------------------------*/
    public function get_products_by_category(int $category_id = 0): void
    {
        // $this->validate_token(); ← REMOVED

        if ($category_id <= 0) {
            $this->send_response(false, 'A valid category ID is required.', null, 400);
        }

        $category = $this->db->get_where('categories', [
            'id'        => $category_id,
            'is_active' => 1,
        ])->row();

        if (!$category) {
            $this->send_response(false, 'Category not found.', null, 404);
        }

        $current_page   = max(1, (int) ($this->input->get('page') ?? 1));
        $items_per_page = 20;
        $offset         = ($current_page - 1) * $items_per_page;

        $total_products = $this->db->where([
            'category_id' => $category_id,
            'is_active'   => 1,
        ])->count_all_results('products');

        $product_rows = $this->db
            ->select('id, name, price, mrp, image, stock')
            ->where(['category_id' => $category_id, 'is_active' => 1])
            ->order_by('name', 'ASC')
            ->limit($items_per_page, $offset)
            ->get('products')
            ->result();

        $product_list = array_map(function ($p) {
            return [
                'id'       => (int) $p->id,
                'name'     => $p->name,
                'price'    => (float) $p->price,
                'mrp'      => (float) $p->mrp,
                'image'    => $this->get_product_image_url($p->image ?? ''),
                'in_stock' => ((int) $p->stock > 0),
            ];
        }, $product_rows);

        $this->send_response(true, 'Products for this category fetched successfully.', [
            'category'       => ['id' => (int) $category->id, 'name' => $category->name],
            'total_products' => $total_products,
            'current_page'   => $current_page,
            'items_per_page' => $items_per_page,
            'total_pages'    => (int) ceil($total_products / $items_per_page),
            'products'       => $product_list,
        ]);
    }

    /*-----------------------------------------------------------------------
    | GET PRODUCT LIST
    | GET /api/get_product_list  [Auth required]
    | Query params: ?page=1 &category_id=2 &search=keyword
    |-----------------------------------------------------------------------*/
    public function get_product_list(): void
    {
        // $this->validate_token(); ← REMOVED

        $current_page   = max(1, (int) ($this->input->get('page') ?? 1));
        $items_per_page = 20;
        $offset         = ($current_page - 1) * $items_per_page;
        $filter_cat_id  = (int) ($this->input->get('category_id') ?? 0);
        $search_keyword = trim($this->input->get('search') ?? '');

        $this->db
            ->select('p.id, p.name, p.price, p.mrp, p.image, p.stock,
                  c.id AS category_id, c.name AS category_name')
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.is_active', 1);

        if ($filter_cat_id > 0) {
            $this->db->where('p.category_id', $filter_cat_id);
        }

        if (!empty($search_keyword)) {
            $this->db->group_start()
                ->like('p.name', $search_keyword)
                ->or_like('c.name', $search_keyword)
                ->group_end();
        }

        $total_products = $this->db->count_all_results('', false);

        $this->db->order_by('p.name', 'ASC')->limit($items_per_page, $offset);
        $product_rows = $this->db->get()->result();

        $product_list = array_map(function ($p) {
            return [
                'id'            => (int) $p->id,
                'name'          => $p->name,
                'price'         => (float) $p->price,
                'mrp'           => (float) $p->mrp,
                'image'         => $this->get_product_image_url($p->image ?? ''),
                'in_stock'      => ((int) $p->stock > 0),
                'category_id'   => (int) $p->category_id,
                'category_name' => $p->category_name ?? '',
            ];
        }, $product_rows);

        $this->send_response(true, 'Product list fetched successfully.', [
            'total_products' => $total_products,
            'current_page'   => $current_page,
            'items_per_page' => $items_per_page,
            'total_pages'    => (int) ceil($total_products / $items_per_page),
            'products'       => $product_list,
        ]);
    }

    /*-----------------------------------------------------------------------
    | GET PRODUCT DETAIL
    | GET /api/get_product_detail/{id}  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_product_detail(int $product_id = 0): void
    {
        // $this->validate_token(); ← REMOVED

        if ($product_id <= 0) {
            $this->send_response(false, 'A valid product ID is required.', null, 400);
        }

        $this->db
            ->select('p.*, c.name AS category_name')
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.id', $product_id)
            ->where('p.is_active', 1);

        $product = $this->db->get()->row();

        if (!$product) {
            $this->send_response(false, 'Product not found.', null, 404);
        }

        $discount_percentage = 0;
        if ($product->mrp > 0 && $product->price < $product->mrp) {
            $discount_percentage = round((($product->mrp - $product->price) / $product->mrp) * 100);
        }

        $this->send_response(true, 'Product detail fetched successfully.', [
            'id'                  => (int) $product->id,
            'name'                => $product->name,
            'description'         => $product->description ?? '',
            'price'               => (float) $product->price,
            'mrp'                 => (float) $product->mrp,
            'discount_percentage' => $discount_percentage,
            'image'               => $this->get_product_image_url($product->image ?? ''),
            'stock_quantity'      => (int) $product->stock,
            'in_stock'            => ((int) $product->stock > 0),
            'category_id'         => (int) $product->category_id,
            'category_name'       => $product->category_name ?? '',
            'is_active'           => (int) $product->is_active,
            'created_at'          => $product->created_at,
            'updated_at'          => $product->updated_at,
        ]);
    }

    public function logout_user(): void
    {
        $this->validate_token(); // confirms token is valid

        $this->send_response(true, 'You have been logged out.');
    }
}
