<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('OTP_FIXED_MODE', true);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

require_once FCPATH . 'vendor/autoload.php';

class Api extends CI_Controller
{
    private $jwt_secret = 'b7c1f3e9a2d64c58f19a8e73d0bcb52f8edc6b31a9f71e48d9a7e2f3c1a5b8e9';
    private $jwt_expiry = 365 * 24 * 60 * 60; // 1 year
    private $request_data = null;

    /*=======================================================================
    | CONSTRUCTOR
    |=======================================================================*/

    public function __construct()
    {
        parent::__construct();

        $this->load->model('General_model');
        $this->load->library(['form_validation']);
        $this->load->helper(['url', 'form']);
        $this->config->load('razorpay');
        $this->config->load('shiprocket');

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
    | CORE / SHARED HELPERS
    |=======================================================================*/

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

    private function require_method(string $method): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
            $this->send_response(false, 'Method not allowed. Please use ' . strtoupper($method) . '.', null, 405);
        }
    }

    private function request_data(): array
    {
        if ($this->request_data !== null) {
            return $this->request_data;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
            $this->request_data = $this->input->get() ?: [];
            return $this->request_data;
        }

        $json = json_decode($this->input->raw_input_stream, true);
        $this->request_data = array_merge(
            $this->input->post() ?: [],
            is_array($json) ? $json : []
        );

        return $this->request_data;
    }

    /**
     * Read one field: JSON body → POST → GET (priority order).
     */
    private function input_value(string $key, string $default = ''): string
    {
        $data = $this->request_data();
        return array_key_exists($key, $data) ? trim((string) $data[$key]) : $default;
    }

    private function get_category_image_url(?string $filename): string
    {
        return !empty($filename) ? base_url('uploads/categories/' . $filename) : '';
    }

    private function get_product_image_url(?string $filename): string
    {
        if (empty($filename)) {
            return '';
        }

        // Extract only filename (removes any path if present)
        $filename = basename($filename);

        return base_url('uploads/products/' . $filename);
    }

    private function get_user_image_url(?string $filename): string
    {
        return !empty($filename) ? base_url('uploads/users/' . $filename) : '';
    }

    /*=======================================================================
    | AUTH / TOKEN HELPERS
    |=======================================================================*/

    private function validate_token(bool $with_meta = false): object
    {
        $header = $this->input->get_request_header('Authorization', true);

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $this->send_response(false, 'Authorization header missing. Please login first.', null, 401);
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
        } catch (Exception $e) {
            $this->send_response(false, 'Token is invalid or expired. Please login again.', null, 401);
        }

        if (
            $this->db->table_exists('token_blacklist') &&
            $this->db
            ->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->count_all_results('token_blacklist') > 0
        ) {
            $this->send_response(false, 'Token has been logged out. Please login again.', null, 401);
        }

        if (empty($decoded->data->id)) {
            $this->send_response(false, 'Token data is invalid.', null, 401);
        }

        if ($with_meta) {
            $decoded->token = $token;
            return $decoded;
        }

        return $decoded->data;
    }

    private function require_token_user_id(): int
    {
        $token_data = $this->validate_token();
        $user_id = (int) ($token_data->id ?? 0);

        if ($user_id <= 0) {
            $this->send_response(false, 'Token data is invalid.', null, 401);
        }

        $user = $this->db->get_where('users', [
            'id'        => $user_id,
            'is_active' => 1,
        ])->row();

        if (!$user) {
            $this->send_response(false, 'User account not found or inactive.', null, 401);
        }

        return $user_id;
    }

    private function generate_token($user): string
    {
        $user = (object) $user;

        $payload = [
            'iss'  => base_url(),
            'iat'  => time(),
            'exp'  => time() + $this->jwt_expiry,
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile ?? '',
                'role'   => $user->role,
            ],
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

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
    | AUTH ENDPOINTS (Register / OTP / Logout)
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | REGISTER USER
    | POST /api/register_user
    | Body: { "name", "email", "mobile" }
    |-----------------------------------------------------------------------*/
    // public function register_user(): void
    // {
    //     $this->require_method('POST');

    //     $name      = $this->input_value('name');
    //     $email     = $this->input_value('email');
    //     $mobile    = $this->input_value('mobile');

    //     if (empty($name) || empty($email) || empty($mobile)) {
    //         $this->send_response(false, 'name, email and mobile are all required.', null, 400);
    //     }

    //     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //         $this->send_response(false, 'Please enter a valid email address.', null, 400);
    //     }

    //     if (!is_numeric($mobile) || strlen($mobile) !== 10) {
    //         $this->send_response(false, 'Mobile number must be exactly 10 digits.', null, 400);
    //     }

    //     if ($this->db->get_where('users', ['email' => $email])->row()) {
    //         $this->send_response(false, 'This email address is already registered.', null, 400);
    //     }

    //     if ($this->db->get_where('users', ['mobile' => $mobile])->row()) {
    //         $this->send_response(false, 'This mobile number is already registered.', null, 400);
    //     }

    //     $this->db->insert('users', [
    //         'name'       => $name,
    //         'email'      => $email,
    //         'mobile'     => $mobile,
    //         'password'   => '',
    //         'shop_name'  => '',
    //         'role'       => 0,
    //         'is_active'  => 1,
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'updated_at' => date('Y-m-d H:i:s'),
    //     ]);

    //     $new_user_id = $this->db->insert_id();

    //     if (!$new_user_id) {
    //         $this->send_response(false, 'Registration failed. Please try again.', null, 500);
    //     }

    //     $this->send_response(true, 'Registration successful. Please login using OTP sent to your mobile.', [
    //         'user' => [
    //             'id'        => (int) $new_user_id,
    //             'name'      => $name,
    //             'email'     => $email,
    //             'mobile'    => $mobile,
    //             'role'      => 0,
    //         ],
    //     ], 201);
    // }
    /*-----------------------------------------------------------------------
| SEND REGISTRATION OTP
| POST /api/register_send_otp
| Body: { "name", "email", "mobile" }
|-----------------------------------------------------------------------*/
    public function register_user(): void
    {
        $this->require_method('POST');

        $name   = $this->input_value('name');
        $email  = $this->input_value('email');
        $mobile = $this->input_value('mobile');

        // Validate
        if (empty($name) || empty($email) || empty($mobile)) {
            $this->send_response(false, 'Name, email and mobile are all required.', null, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->send_response(false, 'Please enter a valid email address.', null, 400);
        }

        if (!is_numeric($mobile) || strlen($mobile) !== 10) {
            $this->send_response(false, 'Mobile number must be exactly 10 digits.', null, 400);
        }

        // Check duplicates
        if ($this->db->get_where('users', ['email' => $email])->row()) {
            $this->send_response(false, 'This email address is already registered.', null, 400);
        }

        if ($this->db->get_where('users', ['mobile' => $mobile])->row()) {
            $this->send_response(false, 'This mobile number is already registered.', null, 400);
        }

        // Generate OTP
        $otp = OTP_FIXED_MODE ? '123456' : (string) rand(100000, 999999);

        // Clear old registration OTPs for this mobile
        $this->db->where('mobile', $mobile)->delete('user_registration_otps');

        // Store OTP with user data
        $this->db->insert('user_registration_otps', [
            'mobile'     => $mobile,
            'otp'        => $otp,
            'user_data'  => json_encode(compact('name', 'email', 'mobile')),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
        ]);

        // Send SMS (disabled for testing)
        // $this->send_otp_via_sms($mobile, $otp);

        $this->send_response(true, 'OTP sent successfully to your mobile number.', [
            'masked_mobile' => '*******' . substr($mobile, -4),
            'expires_in'    => '5 minutes',
        ]);
    }

    /*-----------------------------------------------------------------------
| VERIFY REGISTRATION OTP
| POST /api/register_verify_otp
| Body: { "mobile", "otp" }
|-----------------------------------------------------------------------*/
    public function register_verify_otp(): void
    {
        $this->require_method('POST');

        $mobile      = $this->input_value('mobile');
        $entered_otp = $this->input_value('otp');

        // Validate
        if (empty($mobile) || empty($entered_otp)) {
            $this->send_response(false, 'Both mobile number and OTP are required.', null, 400);
        }

        // Sanitise mobile
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        $mobile = substr($mobile, -10);

        // Find OTP
        $otp_row = $this->db
            ->where('mobile', $mobile)
            ->where('otp', $entered_otp)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->get('user_registration_otps')
            ->row();

        if (!$otp_row) {
            $this->send_response(false, 'OTP is incorrect or has expired. Please request a new OTP.', null, 400);
        }

        // Decode user data
        $user_data = json_decode($otp_row->user_data, true);

        // Create user
        $this->db->insert('users', [
            'name'       => $user_data['name'],
            'email'      => $user_data['email'],
            'mobile'     => $user_data['mobile'],
            'password'   => '',
            'shop_name'  => '',
            'role'       => 0,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $new_user_id = $this->db->insert_id();

        if (!$new_user_id) {
            $this->send_response(false, 'Registration failed. Please try again.', null, 500);
        }

        // Cleanup used OTP
        $this->db->where('id', $otp_row->id)->delete('user_registration_otps');

        // Get full user object for token
        $user = $this->db->get_where('users', ['id' => $new_user_id])->row();

        // Generate JWT
        $token = $this->generate_token($user);

        $this->send_response(true, 'Registration successful. You are now logged in.', [
            'token' => $token,
            'user'  => [
                'id'        => (int) $new_user_id,
                'name'      => $user_data['name'],
                'email'     => $user_data['email'],
                'mobile'    => $user_data['mobile'],

                'image'     => $this->get_user_image_url(''),
            ],
        ], 201);
    }
    /*-----------------------------------------------------------------------
    | SEND OTP
    | POST /api/send_otp
    | Body: { "mobile" }
    |-----------------------------------------------------------------------*/
    public function send_otp(): void
    {
        $this->require_method('POST');

        // ── Read input (JSON body or POST form-data) ──────────────────────
        $mobile = $this->input_value('mobile');

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
        // SMS sending is disabled during testing to avoid reducing OTP limits.
        // Enable this line again when you want live OTP SMS delivery.
        // $this->send_otp_via_sms($mobile, $otp);

        $this->send_response(true, 'OTP sent successfully to your mobile number.', [
            'masked_mobile' => '*******' . substr($mobile, -4),
            'expires_in'    => '5 minutes',
        ]);
    }

    /*-----------------------------------------------------------------------
    | VERIFY OTP (Login)
    | POST /api/verify_otp
    | Body: { "mobile", "otp" }
    |-----------------------------------------------------------------------*/
    public function verify_otp(): void
    {
        $this->require_method('POST');

        // ── Read input ────────────────────────────────────────────────────
        $mobile      = $this->input_value('mobile');
        $entered_otp = $this->input_value('otp');

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


    public function get_home_data(): void
    {
        $user_id = $this->require_token_user_id();
        // Get featured banners
        $banners = $this->db
            ->where('is_active', 1)
            ->where('banner_type', 'home')
            ->where('(start_date IS NULL OR start_date <= CURDATE())', null, false)
            ->where('(end_date IS NULL OR end_date >= CURDATE())', null, false)
            ->order_by('display_order', 'ASC')
            ->limit(5)
            ->get('home_banners')
            ->result_array();

        $banners = array_map(function ($b) {
            return [
                'id' => $b['id'],
                'title' => $b['title'],
                'image' => base_url('uploads/banners/' . $b['image']),
                'button_text' => $b['button_text'],
                'button_link' => $b['button_link'],
            ];
        }, $banners);

        // Get random 5 categories with products
        $categories = $this->db
            ->where('is_active', 1)
            ->order_by('RAND()')
            ->limit(5)
            ->get('categories')
            ->result_array();

        $categories_data = [];
        foreach ($categories as $cat) {
            $products = $this->db
                ->select('id, name, image, price, mrp, stock')
                ->where('category_id', $cat['id'])
                ->where('is_active', 1)
                ->order_by('RAND()')
                ->limit(6)
                ->get('products')
                ->result_array();

            $products = array_map(function ($p) {
                return [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'image' => base_url('uploads/products/' . $p['image']),
                    'price' => (float) $p['price'],
                    'mrp' => (float) $p['mrp'],
                    'stock' => (int) $p['stock'],
                ];
            }, $products);

            $categories_data[] = [
                'id' => $cat['id'],
                'name' => $cat['name'],
                'products' => $products
            ];
        }

        // Get featured offers
        $offers = $this->db
            ->where('is_active', 1)
            ->where('is_featured', 1)
            ->where('start_date <=', date('Y-m-d'))
            ->where('end_date >=', date('Y-m-d'))
            ->order_by('display_order', 'ASC')
            ->limit(3)
            ->get('offers')
            ->result_array();

        $offers = array_map(function ($o) {
            return [
                'id' => $o['id'],
                'title' => $o['title'],
                'offer_type' => $o['offer_type'],
                'discount_value' => (float) $o['discount_value'],
                'discount_type' => $o['discount_type'],
                'coupon_code' => $o['coupon_code'],
                'image' => base_url('uploads/offers/' . $o['image']),
            ];
        }, $offers);

        // Get best sellers (top 8 products by sales)
        $best_sellers = $this->db
            ->select('p.id, p.name, p.image, p.price, p.mrp, COUNT(oi.id) as sales_count')
            ->from('products p')
            ->join('order_items oi', 'oi.product_id = p.id', 'left')
            ->where('p.is_active', 1)
            ->group_by('p.id')
            ->order_by('sales_count', 'DESC')
            ->limit(8)
            ->get()
            ->result_array();

        $best_sellers = array_map(function ($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'image' => base_url('uploads/products/' . $p['image']),
                'price' => (float) $p['price'],
                'mrp' => (float) $p['mrp'],
            ];
        }, $best_sellers);

        $this->send_response(true, 'Home data fetched successfully.', [
            'banners' => $banners,
            'featured_offers' => $offers,
            'categories_with_products' => $categories_data,
            'best_sellers' => $best_sellers,
        ]);
    }


    // public function get_my_profile(): void
    // {
    //     $token_data = $this->validate_token();

    //     $user = $this->db->get_where('users', [
    //         'id'        => $token_data->id,
    //         'is_active' => 1,
    //     ])->row();

    //     if (!$user) {
    //         $this->send_response(false, 'User account not found.', null, 404);
    //     }

    //     $this->send_response(true, 'Profile fetched successfully.', [
    //         'id'         => (int) $user->id,
    //         'name'       => $user->name,
    //         'email'      => $user->email,
    //         'mobile'     => $user->mobile ?? '',
    //         'shop_name'  => $user->shop_name ?? '',
    //         'image'      => $this->get_user_image_url($user->image ?? ''),
    //         'address'    => $user->address ?? '',
    //         'role'       => (int) $user->role,
    //         'is_active'  => (int) $user->is_active,
    //         'created_at' => $user->created_at,
    //     ]);
    // }

    /*-----------------------------------------------------------------------
    | UPDATE MY PROFILE
    | POST /api/update_my_profile  [Auth required]
    | Body: { "name", "email", "shop_name", "address" }
    | → Supports optional image upload via multipart/form-data
    |-----------------------------------------------------------------------*/
    public function update_my_profile(): void
    {
        $this->require_method('POST');
        $token_data = $this->validate_token();
        $request = $this->request_data();
        $update_data = [];

        if (array_key_exists('name', $request)) {
            $update_data['name'] = $this->input_value('name');
        }

        if (array_key_exists('email', $request)) {
            $update_data['email'] = $this->input_value('email');
        }

        if (array_key_exists('mobile', $request)) {
            $mobile = preg_replace('/[^0-9]/', '', $this->input_value('mobile'));
            $update_data['mobile'] = substr($mobile, -10);
        }

        if (array_key_exists('shop_name', $request)) {
            $update_data['shop_name'] = $this->input_value('shop_name');
        }

        if (array_key_exists('gst_number', $request)) {
            $update_data['gst_number'] = $this->input_value('gst_number');
        }

        if (array_key_exists('address', $request)) {
            $update_data['address'] = $this->input_value('address');
        }

        if (!empty($_FILES['image']['name'])) {
            @mkdir('./uploads/users/', 0777, true);
            $this->load->library('upload', [
                'upload_path'   => './uploads/users/',
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 2048,
                'file_name'     => 'user_' . $token_data->id . '_' . time(),
            ]);
            if ($this->upload->do_upload('image')) {
                $update_data['image'] = $this->upload->data('file_name');
            }
        }

        if (empty($update_data)) {
            $this->send_response(false, 'No profile data provided for update.', null, 400);
        }

        $update_data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $token_data->id)->update('users', $update_data);

        $u = $this->db->get_where('users', ['id' => $token_data->id])->row();

        $this->send_response(true, 'Profile updated successfully.', [
            'id'         => (int) $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'mobile'     => $u->mobile     ?? '',
            'shop_name'  => $u->shop_name  ?? '',
            'gst_number' => $u->gst_number ?? '',
            'image'      => $this->get_user_image_url($u->image ?? ''),
            'address'    => $u->address    ?? '',
        ]);
    }
    public function get_my_profile(): void
    {
        $this->require_method('GET');

        $token_data = $this->validate_token();

        $user = $this->db->get_where('users', ['id' => $token_data->id])->row();

        if (!$user) {
            $this->send_response(false, 'User not found.', null, 404);
        }

        $this->send_response(true, 'Profile fetched successfully.', [
            'id'         => (int) $user->id,
            'name'       => $user->name       ?? '',
            'email'      => $user->email      ?? '',
            'mobile'     => $user->mobile     ?? '',
            'shop_name'  => $user->shop_name  ?? '',
            'gst_number' => $user->gst_number ?? '',
            'image'      => !empty($user->image) ? base_url('uploads/users/' . $user->image) : '',
            'address'    => $user->address    ?? '',
        ]);
    }
    public function track_order()
    {
        $this->require_method('GET');
        $user_id = $this->require_token_user_id();
        $order_id = (int) $this->input->get('order_id');

        $order = $this->db->get_where('orders', ['id' => $order_id, 'user_id' => $user_id])->row();
        if (!$order) {
            $this->send_response(false, 'Order not found.', null, 404);
        }

        // Return directly from DB — no need to call Shiprocket live every time
        $this->send_response(true, 'Order tracking fetched', [
            'order_number'     => $order->order_number,
            'status'            => $order->status,
            'awb_code'          => $order->awb_code,
            'courier_name'      => $order->courier_name,
            'tracking_status'   => $order->tracking_status,
            'pickup_scheduled'  => (bool) $order->pickup_scheduled,
        ]);
    }

    public function delete_account(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();

        $this->db->trans_begin();

        if ($this->db->table_exists('cart_items')) {
            $this->db->where('user_id', $user_id)->delete('cart_items');
        }

        if ($this->db->table_exists('user_addresses')) {
            $this->db->where('user_id', $user_id)->delete('user_addresses');
        }

        $this->db->where('id', $user_id)->update('users', [
            'is_active'  => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->send_response(false, 'Failed to delete account. Please try again.', null, 500);
        }

        $this->db->trans_commit();

        $this->send_response(true, 'Account deleted successfully.');
    }


    public function get_category_list(): void
    {
        $this->validate_token();
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
        $this->validate_token();

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
        $this->validate_token();

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
            ->select('id, name, price, mrp, image, stock, hsn_code')
            ->where(['category_id' => $category_id, 'is_active' => 1])
            ->order_by('name', 'ASC')
            ->limit($items_per_page, $offset)
            ->get('products')
            ->result();

        // Fetch gallery images
        $product_ids = array_map(function ($p) {
            return (int) $p->id;
        }, $product_rows);

        $gallery_by_product = [];

        if (!empty($product_ids)) {
            $gallery_rows = $this->db
                ->select('id, product_id, image')
                ->from('product_images')
                ->where_in('product_id', $product_ids)
                ->order_by('id', 'ASC')
                ->get()
                ->result();

            foreach ($gallery_rows as $g) {
                $gallery_by_product[$g->product_id][] = [
                    'id'    => (int) $g->id,
                    'image' => $this->get_product_image_url($g->image ?? ''),
                ];
            }
        }

        $product_list = array_map(function ($p) use ($gallery_by_product) {
            $images = [];

            if (!empty($p->image)) {
                $images[] = [
                    'id'    => null,
                    'image' => $this->get_product_image_url($p->image ?? ''),
                ];
            }

            if (!empty($gallery_by_product[$p->id])) {
                $images = array_merge($images, $gallery_by_product[$p->id]);
            }

            return [
                'id'       => (int) $p->id,
                'name'     => $p->name,
                'price'    => (float) $p->price,
                'mrp'      => (float) $p->mrp,
                'image'    => $this->get_product_image_url($p->image ?? ''),
                'images'   => $images,
                'in_stock' => ((int) $p->stock > 0),
                'hsn_code' => $p->hsn_code ?? '',
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
        $this->validate_token();

        $current_page   = max(1, (int) ($this->input->get('page') ?? 1));
        $items_per_page = 20;
        $offset         = ($current_page - 1) * $items_per_page;
        $filter_cat_id  = (int) ($this->input->get('category_id') ?? 0);
        $search_keyword = trim($this->input->get('search') ?? '');
        $sort_by        = trim($this->input->get('sort') ?? 'name'); // name, price_low_high, price_high_low

        $this->db
            ->select('p.id, p.name, p.price, p.mrp, p.image, p.stock, p.hsn_code,
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

        // Apply sorting
        switch ($sort_by) {
            case 'price_low_high':
                $this->db->order_by('p.price', 'ASC');
                break;
            case 'price_high_low':
                $this->db->order_by('p.price', 'DESC');
                break;
            default:
                $this->db->order_by('p.name', 'ASC');
        }

        $this->db->limit($items_per_page, $offset);
        $product_rows = $this->db->get()->result();

        // Fetch gallery images
        $product_ids = array_map(function ($p) {
            return (int) $p->id;
        }, $product_rows);

        $gallery_by_product = [];

        if (!empty($product_ids)) {
            $gallery_rows = $this->db
                ->select('id, product_id, image')
                ->from('product_images')
                ->where_in('product_id', $product_ids)
                ->order_by('id', 'ASC')
                ->get()
                ->result();

            foreach ($gallery_rows as $g) {
                $gallery_by_product[$g->product_id][] = [
                    'id'    => (int) $g->id,
                    'image' => $this->get_product_image_url($g->image ?? ''),
                ];
            }
        }

        $product_list = array_map(function ($p) use ($gallery_by_product) {
            $images = [];

            if (!empty($p->image)) {
                $images[] = [
                    'id'    => null,
                    'image' => $this->get_product_image_url($p->image ?? ''),
                ];
            }

            if (!empty($gallery_by_product[$p->id])) {
                $images = array_merge($images, $gallery_by_product[$p->id]);
            }

            return [
                'id'            => (int) $p->id,
                'name'          => $p->name,
                'price'         => (float) $p->price,
                'mrp'           => (float) $p->mrp,
                'image'         => $this->get_product_image_url($p->image ?? ''),
                'images'        => $images,
                'in_stock'      => ((int) $p->stock > 0),
                'category_id'   => (int) $p->category_id,
                'category_name' => $p->category_name ?? '',
                'hsn_code'      => $p->hsn_code ?? '',
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
        $this->validate_token();

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

        // Fetch gallery images for this product
        $gallery_rows = $this->db
            ->select('id, image')
            ->from('product_images')
            ->where('product_id', $product_id)
            ->order_by('id', 'ASC')
            ->get()
            ->result();

        $images = [];

        // Primary image always first
        if (!empty($product->image)) {
            $images[] = [
                'id'    => null,
                'image' => $this->get_product_image_url($product->image ?? ''),
            ];
        }

        // Append gallery images
        foreach ($gallery_rows as $g) {
            $images[] = [
                'id'    => (int) $g->id,
                'image' => $this->get_product_image_url($g->image ?? ''),
            ];
        }

        $this->send_response(true, 'Product detail fetched successfully.', [
            'id'                  => (int) $product->id,
            'name'                => $product->name,
            'description'         => $product->description ?? '',
            'price'               => (float) $product->price,
            'mrp'                 => (float) $product->mrp,
            'discount_percentage' => $discount_percentage,
            'image'               => $this->get_product_image_url($product->image ?? ''), // kept for backward compatibility
            'images'              => $images, // NEW: full gallery (primary + additional)
            'stock_quantity'      => (int) $product->stock,
            'in_stock'            => ((int) $product->stock > 0),
            'category_id'         => (int) $product->category_id,
            'category_name'       => $product->category_name ?? '',
            'is_active'           => (int) $product->is_active,
            'hsn_code'            => $product->hsn_code ?? '',
            'created_at'          => $product->created_at,
            'updated_at'          => $product->updated_at,
        ]);
    }

    /*=======================================================================
    | CART ENDPOINTS
    |=======================================================================*/

    private function ensure_cart_table(): void
    {
        if (!$this->db->table_exists('cart_items')) {
            $this->send_response(false, 'cart_items table is missing. Please create it first.', null, 500);
        }
    }

    private function get_active_product(int $product_id)
    {
        if ($product_id <= 0) {
            $this->send_response(false, 'A valid product ID is required.', null, 400);
        }

        $product = $this->db->get_where('products', [
            'id'        => $product_id,
            'is_active' => 1,
        ])->row();

        if (!$product) {
            $this->send_response(false, 'Product not found or inactive.', null, 404);
        }

        return $product;
    }

    private function get_cart_row(int $user_id, int $cart_id = 0, int $product_id = 0)
    {
        if ($cart_id > 0) {
            return $this->db->get_where('cart_items', [
                'id'      => $cart_id,
                'user_id' => $user_id,
            ])->row();
        }

        if ($product_id > 0) {
            return $this->db->get_where('cart_items', [
                'user_id'    => $user_id,
                'product_id' => $product_id,
            ])->row();
        }

        $this->send_response(false, 'cart_id or product_id is required.', null, 400);
    }

    private function get_cart_summary(int $user_id): array
    {
        $rows = $this->db
            ->select('ci.id AS cart_id, ci.product_id, ci.quantity, ci.created_at, ci.updated_at,
    p.name, p.price, p.mrp, p.image, p.stock, p.is_active, p.gst_percent,
    c.id AS category_id, c.name AS category_name')
            ->from('cart_items ci')
            ->join('products p', 'p.id = ci.product_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('ci.user_id', $user_id)
            ->order_by('ci.id', 'DESC')
            ->get()
            ->result();

        $items = [];
        $total_items = 0;
        $subtotal = 0.0;
        $total_mrp = 0.0;
        $total_gst = 0.0;

        foreach ($rows as $row) {
            $quantity = (int) $row->quantity;
            $price = (float) ($row->price ?? 0);
            $mrp = (float) ($row->mrp ?? 0);
            $gst_percent = (float)($row->gst_percent ?? 0);

            $line_total  = $price * $quantity;          // before GST
            $gst_amount  = ($line_total * $gst_percent) / 100;
            $line_mrp_total = $mrp * $quantity;
            $available = ((int) ($row->is_active ?? 0) === 1) && ((int) ($row->stock ?? 0) > 0);

            $total_items += $quantity;
            $subtotal += $line_total;
            // $total_gst += $line_gst;
            $total_gst += $gst_amount;
            $total_mrp += $line_mrp_total;

            $items[] = [
                'cart_id'        => (int) $row->cart_id,
                'product_id'     => (int) $row->product_id,
                'name'           => $row->name ?? '',
                'image'          => $row->image ?? '',  // ✅ CHANGED - Filename only, no URL conversion
                'category_id'    => (int) ($row->category_id ?? 0),
                'category_name'  => $row->category_name ?? '',
                'price'          => $price,
                'gst_percent' => $gst_percent,
                'gst_amount'  => $gst_amount,
                'mrp'            => $mrp,
                'quantity'       => $quantity,
                'stock_quantity' => (int) ($row->stock ?? 0),
                'is_available'   => $available,
                'line_total'     => $line_total,
                'created_at'     => $row->created_at,
                'updated_at'     => $row->updated_at,
            ];
        }

        return [
            'total_cart_items' => count($items),
            'total_quantity'   => $total_items,
            'subtotal'      => round($subtotal, 2),
            'total_gst'     => round($total_gst, 2),
            'grand_total'   => round($subtotal + $total_gst, 2),
            'total_mrp'        => $total_mrp,
            'discount'         => max(0, $total_mrp - $subtotal),
            'items'            => $items,
        ];
    }

    /*-----------------------------------------------------------------------
    | ADD TO CART
    | POST /api/add_to_cart  [Auth required]
    | Body: { "product_id", "quantity" }
    |-----------------------------------------------------------------------*/
    public function add_to_cart(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();

        $product_id = (int) $this->input_value('product_id');
        $quantity = (int) $this->input_value('quantity', '1');

        if ($quantity <= 0) {
            $this->send_response(false, 'Quantity must be greater than zero.', null, 400);
        }

        $product = $this->get_active_product($product_id);

        if ((int) $product->stock <= 0) {
            $this->send_response(false, 'Product is out of stock.', null, 400);
        }

        $existing = $this->db->get_where('cart_items', [
            'user_id'    => $user_id,
            'product_id' => $product_id,
        ])->row();

        $new_quantity = $quantity + ($existing ? (int) $existing->quantity : 0);

        if ($new_quantity > (int) $product->stock) {
            $this->send_response(false, 'Requested quantity is greater than available stock.', null, 400);
        }

        if ($existing) {
            $this->db->where('id', $existing->id)->update('cart_items', [
                'quantity'   => $new_quantity,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->insert('cart_items', [
                'user_id'    => $user_id,
                'product_id' => $product_id,
                'quantity'   => $quantity,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->send_response(true, 'Product added to cart successfully.', $this->get_cart_summary($user_id));
    }

    /*-----------------------------------------------------------------------
    | GET CART
    | GET /api/get_cart  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_cart(): void
    {
        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();

        $cart = $this->get_cart_summary($user_id);

        // ✅ Convert image filenames to full URLs
        foreach ($cart['items'] as &$item) {
            $item['image'] = $this->get_product_image_url($item['image']);
        }

        $this->send_response(true, 'Cart fetched successfully.', $cart);
    }
    /*-----------------------------------------------------------------------
    | UPDATE CART QUANTITY
    | POST /api/update_cart_quantity  [Auth required]
    | Body: { "cart_id" or "product_id", "quantity" }
    |-----------------------------------------------------------------------*/
    public function update_cart_quantity(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();

        $cart_id = (int) $this->input_value('cart_id');
        $product_id = (int) $this->input_value('product_id');
        $quantity = (int) $this->input_value('quantity');

        if ($quantity < 0) {
            $this->send_response(false, 'Quantity cannot be negative.', null, 400);
        }

        $cart_item = $this->get_cart_row($user_id, $cart_id, $product_id);

        if (!$cart_item) {
            $this->send_response(false, 'Cart item not found.', null, 404);
        }

        if ($quantity === 0) {
            $this->db->where('id', $cart_item->id)->delete('cart_items');
            $this->send_response(true, 'Product removed from cart successfully.', $this->get_cart_summary($user_id));
        }

        $product = $this->get_active_product((int) $cart_item->product_id);

        if ($quantity > (int) $product->stock) {
            $this->send_response(false, 'Requested quantity is greater than available stock.', null, 400);
        }

        $this->db->where('id', $cart_item->id)->update('cart_items', [
            'quantity'   => $quantity,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->send_response(true, 'Cart quantity updated successfully.', $this->get_cart_summary($user_id));
    }

    /*-----------------------------------------------------------------------
    | REMOVE FROM CART
    | POST /api/remove_from_cart  [Auth required]
    | Body: { "cart_id" or "product_id" }
    |-----------------------------------------------------------------------*/
    public function remove_from_cart(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();

        $cart_id = (int) $this->input_value('cart_id');
        $product_id = (int) $this->input_value('product_id');
        $cart_item = $this->get_cart_row($user_id, $cart_id, $product_id);

        if (!$cart_item) {
            $this->send_response(false, 'Cart item not found.', null, 404);
        }

        $this->db->where('id', $cart_item->id)->delete('cart_items');

        $this->send_response(true, 'Product removed from cart successfully.', $this->get_cart_summary($user_id));
    }

    /*-----------------------------------------------------------------------
    | CLEAR CART
    | POST /api/clear_cart  [Auth required]
    |-----------------------------------------------------------------------*/
    public function clear_cart(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();

        $this->db->where('user_id', $user_id)->delete('cart_items');

        $this->send_response(true, 'Cart cleared successfully.', $this->get_cart_summary($user_id));
    }

    /*=======================================================================
    | ADDRESS ENDPOINTS
    |=======================================================================*/

    private function ensure_address_table(): void
    {
        if (!$this->db->table_exists('user_addresses')) {
            $this->send_response(false, 'user_addresses table is missing. Please create it first.', null, 500);
        }
    }

    private function format_address($address): array
    {
        return [
            'id'            => (int) $address->id,
            'full_name'     => $address->full_name ?? '',
            'mobile'        => $address->mobile ?? '',
            'address_line1' => $address->address_line1 ?? '',
            'address_line2' => $address->address_line2 ?? '',
            'landmark'      => $address->landmark ?? '',
            'city'          => $address->city ?? '',
            'state'         => $address->state ?? '',
            'pincode'       => $address->pincode ?? '',
            'country'       => $address->country ?? 'India',
            'is_default'    => (int) ($address->is_default ?? 0),
            'created_at'    => $address->created_at ?? null,
            'updated_at'    => $address->updated_at ?? null,
        ];
    }

    /*-----------------------------------------------------------------------
    | GET ADDRESSES
    | GET /api/get_addresses  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_addresses(): void
    {
        $user_id = $this->require_token_user_id();
        $this->ensure_address_table();

        $rows = $this->db
            ->where('user_id', $user_id)
            ->order_by('is_default', 'DESC')
            ->order_by('id', 'DESC')
            ->get('user_addresses')
            ->result();

        $addresses = array_map([$this, 'format_address'], $rows);

        $this->send_response(true, 'Addresses fetched successfully.', [
            'total_addresses' => count($addresses),
            'addresses'       => $addresses,
        ]);
    }

    /*-----------------------------------------------------------------------
    | SAVE ADDRESS
    | POST /api/save_address  [Auth required]
    |-----------------------------------------------------------------------*/
    public function save_address(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_address_table();

        $full_name = $this->input_value('full_name');
        $mobile = preg_replace('/[^0-9]/', '', $this->input_value('mobile'));
        $mobile = substr($mobile, -10);
        $address_line1 = $this->input_value('address_line1');
        $city = $this->input_value('city');
        $state = $this->input_value('state');
        $pincode = $this->input_value('pincode');
        $is_default = (int) $this->input_value('is_default', '0');

        if ($full_name === '' || $mobile === '' || $address_line1 === '' || $city === '' || $state === '' || $pincode === '') {
            $this->send_response(false, 'full_name, mobile, address_line1, city, state and pincode are required.', null, 400);
        }

        if (!is_numeric($mobile) || strlen($mobile) !== 10) {
            $this->send_response(false, 'Mobile number must be exactly 10 digits.', null, 400);
        }

        if ($is_default === 1) {
            $this->db->where('user_id', $user_id)->update('user_addresses', ['is_default' => 0]);
        }

        $this->db->insert('user_addresses', [
            'user_id'       => $user_id,
            'full_name'     => $full_name,
            'mobile'        => $mobile,
            'address_line1' => $address_line1,
            'address_line2' => $this->input_value('address_line2'),
            'landmark'      => $this->input_value('landmark'),
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $pincode,
            'country'       => $this->input_value('country', 'India'),
            'is_default'    => $is_default,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $address_id = $this->db->insert_id();
        $address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();

        $this->send_response(true, 'Address saved successfully.', $this->format_address($address), 201);
    }

    /*-----------------------------------------------------------------------
    | UPDATE ADDRESS
    | POST /api/update_address  [Auth required]
    | Body: { "address_id", ...fields }
    |-----------------------------------------------------------------------*/
    public function update_address(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_address_table();

        $address_id = (int) $this->input_value('address_id');

        if ($address_id <= 0) {
            $this->send_response(false, 'address_id is required.', null, 400);
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            $this->send_response(false, 'Address not found.', null, 404);
        }

        $request = $this->request_data();
        $update_data = [];

        foreach (['full_name', 'address_line1', 'address_line2', 'landmark', 'city', 'state', 'pincode', 'country'] as $field) {
            if (array_key_exists($field, $request)) {
                $update_data[$field] = $this->input_value($field);
            }
        }

        if (array_key_exists('mobile', $request)) {
            $mobile = preg_replace('/[^0-9]/', '', $this->input_value('mobile'));
            $mobile = substr($mobile, -10);
            if (!is_numeric($mobile) || strlen($mobile) !== 10) {
                $this->send_response(false, 'Mobile number must be exactly 10 digits.', null, 400);
            }
            $update_data['mobile'] = $mobile;
        }

        if (array_key_exists('is_default', $request)) {
            $update_data['is_default'] = (int) $this->input_value('is_default', '0');
            if ((int) $update_data['is_default'] === 1) {
                $this->db
                    ->where('user_id', $user_id)
                    ->where('id !=', $address_id)
                    ->update('user_addresses', ['is_default' => 0]);
            }
        }

        if (empty($update_data)) {
            $this->send_response(false, 'No address data provided for update.', null, 400);
        }

        $update_data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where(['id' => $address_id, 'user_id' => $user_id])->update('user_addresses', $update_data);
        $updated_address = $this->db->get_where('user_addresses', ['id' => $address_id, 'user_id' => $user_id])->row();

        $this->send_response(true, 'Address updated successfully.', $this->format_address($updated_address));
    }

    /*-----------------------------------------------------------------------
    | DELETE ADDRESS
    | POST /api/delete_address  [Auth required]
    | Body: { "address_id" }
    |-----------------------------------------------------------------------*/
    public function delete_address(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_address_table();

        $address_id = (int) $this->input_value('address_id');

        if ($address_id <= 0) {
            $this->send_response(false, 'address_id is required.', null, 400);
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            $this->send_response(false, 'Address not found.', null, 404);
        }

        $this->db->where(['id' => $address_id, 'user_id' => $user_id])->delete('user_addresses');

        $this->send_response(true, 'Address deleted successfully.');
    }

    /*=======================================================================
    | ORDER ENDPOINTS
    |=======================================================================*/

    private function ensure_orders_table(): void
    {
        if (!$this->db->table_exists('orders') || !$this->db->table_exists('order_items')) {
            $this->send_response(false, 'orders/order_items table is missing. Please create it first.', null, 500);
        }
    }

    private function insert_order_items(int $order_id, array $items): void
    {
        foreach ($items as $item) {

            $this->db->insert('order_items', [
                'order_id'      => $order_id,
                'product_id'    => $item['product_id'],
                'product_name'  => $item['name'],
                'product_image' => $item['image'],
                'price'         => $item['price'],
                'gst_percent'   => $item['gst_percent'],
                'gst_amount'    => $item['gst_amount'],
                'quantity'      => $item['quantity'],
                'subtotal'      => $item['line_total'], // Price × Qty (before GST)
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function reduce_stock(array $items): void
    {
        foreach ($items as $item) {
            $this->db->set('stock', 'stock - ' . (int) $item['quantity'], false)
                ->where('id', $item['product_id'])
                ->update('products');
        }
    }

    private function restore_stock_for_order(int $order_id): void
    {
        $items = $this->db->where('order_id', $order_id)->get('order_items')->result();
        foreach ($items as $item) {
            $this->db->set('stock', 'stock + ' . (int) $item->quantity, false)
                ->where('id', $item->product_id)
                ->update('products');
        }
    }

    private function insert_status_history(int $order_id, string $status, string $remarks, string $changed_by): void
    {
        $this->db->insert('order_status_history', [
            'order_id'   => $order_id,
            'status'     => $status,
            'remarks'    => $remarks,
            'changed_by' => $changed_by,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function create_razorpay_order(array $payload, string $key_id, string $key_secret): array
    {
        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $key_id . ':' . $key_secret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response    = curl_exec($ch);
        $curl_error  = curl_error($ch);
        $status_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['status' => false, 'message' => $curl_error ?: 'Unable to connect to Razorpay.'];
        }

        $decoded = json_decode($response, true);

        if ($status_code < 200 || $status_code >= 300 || !is_array($decoded)) {
            $message = 'Unable to create Razorpay order.';
            if (is_array($decoded) && !empty($decoded['error']['description'])) {
                $message = (string) $decoded['error']['description'];
            }
            return ['status' => false, 'message' => $message];
        }

        return ['status' => true, 'data' => $decoded];
    }

    private function format_order_full(int $order_id, int $user_id)
    {
        // Get order details
        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id,
        ])->row_array();

        if (!$order) {
            return null;
        }

        // Get order items
        $items = $this->db
            ->select('
id,
product_id,
product_name,
product_image,
price,
gst_percent,
gst_amount,
quantity,
subtotal
')->where('order_id', $order_id)
            ->get('order_items')
            ->result_array();

        // ✅ Convert image filenames to URLs for each item
        foreach ($items as &$item) {
            $item['product_image'] = $this->get_product_image_url($item['product_image']);
            $item['id'] = (int) $item['id'];
            $item['product_id'] = (int) $item['product_id'];
            $item['price'] = (float) $item['price'];
            $item['gst_percent'] = (float) $item['gst_percent'];
            $item['gst_amount'] = (float) $item['gst_amount'];
            $item['quantity'] = (int) $item['quantity'];
            $item['subtotal'] = (float) $item['subtotal'];
            $item['total_amount'] = round($item['subtotal'] + $item['gst_amount'], 2);
        }

        // Get delivery address
        $delivery_address = $this->db->get_where('user_addresses', [
            'id' => $order['address_id'],
        ])->row_array();

        // Get status history
        $status_history = $this->db
            ->where('order_id', $order_id)
            ->order_by('created_at', 'DESC')
            ->get('order_status_history')
            ->result_array();

        return [
            'order_id'           => (int) $order['id'],
            'order_number'       => $order['order_number'],
            'status'             => $order['status'],
            'payment_method'     => $order['payment_method'],
            'payment_status'     => $order['payment_status'],
            'subtotal'           => (float) $order['subtotal'],
            'gst_amount'         => (float) $order['gst_amount'],
            'delivery_charge'    => (float) $order['delivery_charge'],
            'expected_delivery' => $order['expected_delivery_date'] ?? null,
            'discount'           => (float) $order['discount'],
            'total_amount'       => (float) $order['total_amount'],
            'total_items'        => (int) $order['total_items'],
            'notes'              => $order['notes'] ?? '',
            'cancel_reason'      => $order['cancel_reason'] ?? null,
            'cancelled_by'       => $order['cancelled_by'] ?? null,
            'created_at'         => $order['created_at'],
            'updated_at'         => $order['updated_at'],

            // NEW — Shiprocket fields
            'awb_code'           => $order['awb_code'] ?? null,
            'courier_name'       => $order['courier_name'] ?? null,
            'tracking_status'    => $order['tracking_status'] ?? null,
            'pickup_scheduled'   => (bool) ($order['pickup_scheduled'] ?? false),
            'invoice_url' => $order['invoice_url'] ?? null,
            // 'label_url'   => $order['label_url'] ?? null,
            'items'              => $items,
            'delivery_address'   => $delivery_address,
            'status_history'     => $status_history,
        ];
    }

    public function place_order(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_cart_table();
        $this->ensure_address_table();
        $this->ensure_orders_table();

        $address_id     = (int) $this->input_value('address_id');
        $payment_method = strtolower($this->input_value('payment_method', 'cod'));
        $notes          = $this->input_value('notes');
        $delivery_charge          = $this->input_value('delivery_charge');


        if ($address_id <= 0) {
            $this->send_response(false, 'Please select a delivery address.', null, 400);
        }

        $address = $this->db->get_where('user_addresses', [
            'id'      => $address_id,
            'user_id' => $user_id,
        ])->row();

        if (!$address) {
            $this->send_response(false, 'Delivery address not found.', null, 404);
        }

        if (!in_array($payment_method, ['cod', 'online'], true)) {
            $payment_method = 'cod';
        }

        $cart_summary = $this->get_cart_summary($user_id);

        if (empty($cart_summary['items'])) {
            $this->send_response(false, 'Your cart is empty.', null, 400);
        }

        foreach ($cart_summary['items'] as $item) {
            if (!$item['is_available']) {
                $this->send_response(false, $item['name'] . ' is currently unavailable.', null, 400);
            }
            if ($item['quantity'] > $item['stock_quantity']) {
                $this->send_response(false, 'Insufficient stock for ' . $item['name'] . '. Available: ' . $item['stock_quantity'], null, 400);
            }
        }

        $subtotal        = $cart_summary['subtotal'];
        $total_gst = $cart_summary['total_gst'];
        // $delivery_charge = 0.00;
        $discount        = 0.00;
        $total_amount = $subtotal + $total_gst + $delivery_charge - $discount;
        // $total_amount = 1.00;
        $order_number    = 'GMB' . date('Ymd') . strtoupper(substr(uniqid(), -6));

        /* ---------------- ONLINE PAYMENT ---------------- */
        if ($payment_method === 'online') {
            $key_id     = trim((string) config_item('razorpay_key_id'));
            $key_secret = trim((string) config_item('razorpay_key_secret'));
            $currency   = trim((string) config_item('razorpay_currency')) ?: 'INR';

            if ($key_id === '' || $key_secret === '') {
                $this->send_response(false, 'Online payment is temporarily unavailable. Please choose Cash on Delivery.', null, 500);
            }

            $gateway = $this->create_razorpay_order([
                'amount'   => (int) round($total_amount * 100),
                'currency' => $currency,
                'receipt'  => 'order_' . $user_id . '_' . time(),
                'notes'    => ['user_id' => (string) $user_id],
            ], $key_id, $key_secret);

            if (empty($gateway['status'])) {
                $this->send_response(false, $gateway['message'] ?? 'Unable to create payment order.', null, 502);
            }

            $this->db->trans_begin();

            $this->db->insert('orders', [
                'user_id'           => $user_id,
                'address_id'        => $address_id,
                'order_number'      => $order_number,
                'subtotal'    => $subtotal,
                'gst_amount'  => $total_gst,
                'delivery_charge'   => $delivery_charge,
                'discount'          => $discount,
                'total_amount'      => $total_amount,
                'total_items'       => $cart_summary['total_quantity'],
                'payment_method'    => 'online',
                'payment_status'    => 'pending',
                'razorpay_order_id' => $gateway['data']['id'],
                'status'            => 'pending',
                'notes'             => $notes,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $order_id = $this->db->insert_id();
            $this->insert_order_items($order_id, $cart_summary['items']);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $this->send_response(false, 'Failed to initiate order. Please try again.', null, 500);
            }

            $this->db->trans_commit();

            $this->send_response(true, 'Razorpay order created. Complete the payment to confirm.', [
                'order_id'          => $order_id,
                'order_number'      => $order_number,
                'amount'            => $total_amount,
                'currency'          => $currency,
                'key_id'            => $key_id,
                'gst_amount'  => $total_gst,

                'delivery_charge' => $delivery_charge,


                // Actual Razorpay Order ID
                'razorpay_order_id' => $gateway['data']['id'],

                // Dummy values for Postman testing
                // 'test_verify_payload' => [
                //     'razorpay_order_id'   => $gateway['data']['id'],
                //     'razorpay_payment_id' => 'pay_T9N71ItLv8bR0s',
                //     'razorpay_signature'  => hash_hmac(
                //         'sha256',
                //         $gateway['data']['id'] . '|pay_T9N71ItLv8bR0s',
                //         $key_secret
                //     ),
                // ],
            ]);

            /* ---------------- COD ---------------- */
            $this->db->trans_begin();

            $this->db->insert('orders', [
                'user_id'         => $user_id,
                'address_id'      => $address_id,
                'order_number'    => $order_number,
                'subtotal'    => $subtotal,
                'gst_amount'  => $total_gst,
                'delivery_charge' => $delivery_charge,
                'discount'        => $discount,
                'total_amount'    => $total_amount,
                'total_items'     => $cart_summary['total_quantity'],
                'payment_method'  => 'cod',
                'payment_status'  => 'pending',
                'status'          => 'pending',
                'notes'           => $notes,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            $order_id = $this->db->insert_id();
            $this->insert_order_items($order_id, $cart_summary['items']);
            $this->reduce_stock($cart_summary['items']);
            $this->insert_status_history($order_id, 'pending', 'Order placed successfully', 'system');
            $this->db->where('user_id', $user_id)->delete('cart_items');

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $this->send_response(false, 'Failed to place order. Please try again.', null, 500);
            }

            $this->db->trans_commit();

            $this->send_response(true, 'Order placed successfully.', $this->format_order_full($order_id, $user_id), 201);
        }
    }

    public function verify_order_payment(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_orders_table();

        $razorpay_order_id   = $this->input_value('razorpay_order_id');
        $razorpay_payment_id = $this->input_value('razorpay_payment_id');
        $razorpay_signature  = $this->input_value('razorpay_signature');

        if ($razorpay_order_id === '' || $razorpay_payment_id === '' || $razorpay_signature === '') {
            $this->send_response(false, 'Missing payment verification details.', null, 400);
        }

        $order = $this->db->get_where('orders', [
            'user_id'           => $user_id,
            'razorpay_order_id' => $razorpay_order_id,
        ])->row();

        if (!$order) {
            $this->send_response(false, 'Order not found for verification.', null, 404);
        }

        $key_secret          = trim((string) config_item('razorpay_key_secret'));
        $expected_signature  = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);

        if (!hash_equals($expected_signature, $razorpay_signature)) {
            $this->db->where('id', $order->id)->update('orders', [
                'payment_status' => 'failed',
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            $this->send_response(false, 'Payment verification failed.', null, 400);
        }

        $this->db->trans_begin();

        $this->db->where('id', $order->id)->update('orders', [
            'payment_status'      => 'paid',
            'status'              => 'confirmed',
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature'  => $razorpay_signature,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        $items = $this->db->where('order_id', $order->id)->get('order_items')->result();
        foreach ($items as $item) {
            $this->db->set('stock', 'stock - ' . (int) $item->quantity, false)
                ->where('id', $item->product_id)
                ->update('products');
        }

        $this->insert_status_history($order->id, 'confirmed', 'Payment received successfully', 'system');
        $this->db->where('user_id', $user_id)->delete('cart_items');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->send_response(false, 'Payment received, but order could not be confirmed. Please contact support.', null, 500);
        }

        $this->db->trans_commit();
        $this->load->library('shiprocket');
        $this->shiprocket->create_order($order->id);
        $this->send_response(true, 'Payment verified. Order confirmed.', $this->format_order_full($order->id, $user_id));
    }


    public function check_delivery_charge()
    {
        $this->require_method('POST');
        $pincode = $this->input_value('pincode');

        if (!$pincode) {
            $this->send_response(false, 'Pincode is required.', null, 400);
        }

        $this->load->library('shiprocket');
        $result = $this->shiprocket->check_serviceability('360002', $pincode);
        // $this->send_response(true, 'Delivery charge fetched', $result);
        $this->send_response(true, 'debug', ['token_ok' => !empty($token), 'raw' => $result]);
    }
    public function shiprocket_webhook(): void
    {
        // echo "<pre>";
        // print_r(getallheaders());

        // echo "\n\nSERVER:\n";
        // print_r($_SERVER);

        // exit;
        // Verify token from header
        $received_token = $this->input->get_request_header('x-api-key', true);
        $expected_token  = trim((string) config_item('shiprocket_webhook_token'));
        log_message('error', 'WEBHOOK - received token: [' . var_export($received_token, true) . ']');
        log_message('error', 'WEBHOOK - expected token: [' . var_export($expected_token, true) . ']');
        log_message('error', 'WEBHOOK - all headers: ' . json_encode($this->input->request_headers()));

        if (empty($received_token) || $received_token !== $expected_token) {
            http_response_code(401);
            echo json_encode(['status' => false, 'message' => 'Unauthorized']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        log_message('error', 'SHIPROCKET WEBHOOK PAYLOAD: ' . json_encode($payload));

        if (empty($payload['awb']) || empty($payload['current_status'])) {
            echo json_encode(['status' => false, 'message' => 'Invalid payload']);
            return;
        }

        $this->db->where('awb_code', $payload['awb'])->update('orders', [
            'tracking_status' => $payload['current_status'],
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // Optional: auto-update order status to 'delivered' when Shiprocket confirms delivery
        if (stripos($payload['current_status'], 'delivered') !== false) {
            $order = $this->db->get_where('orders', ['awb_code' => $payload['awb']])->row();
            if ($order) {
                $this->db->where('id', $order->id)->update('orders', ['status' => 'delivered']);
                $this->db->insert('order_status_history', [
                    'order_id'   => $order->id,
                    'status'     => 'delivered',
                    'comment'    => 'Delivered (auto-updated via Shiprocket webhook)',
                    'updated_by' => 'system',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo json_encode(['status' => true]);
    }
    public function get_orders(): void
    {
        $user_id = $this->require_token_user_id();
        $this->ensure_orders_table();

        $current_page   = max(1, (int) ($this->input->get('page') ?? 1));
        $items_per_page = 10;
        $offset         = ($current_page - 1) * $items_per_page;
        $status_filter  = trim($this->input->get('status') ?? '');
        $valid_statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'];

        $this->db->where('user_id', $user_id);
        if ($status_filter !== '' && in_array($status_filter, $valid_statuses, true)) {
            $this->db->where('status', $status_filter);
        }
        $total_orders = $this->db->count_all_results('orders');

        $this->db->where('user_id', $user_id);
        if ($status_filter !== '' && in_array($status_filter, $valid_statuses, true)) {
            $this->db->where('status', $status_filter);
        }
        $orders = $this->db->order_by('id', 'DESC')->limit($items_per_page, $offset)->get('orders')->result();

        $order_list = array_map(function ($order) {
            $first_item = $this->db->where('order_id', $order->id)->limit(1)->get('order_items')->row();

            // ✅ Convert filename to URL using helper
            $image_url = $first_item && !empty($first_item->product_image)
                ? $this->get_product_image_url($first_item->product_image)
                : '';

            return [
                'order_id'          => (int) $order->id,
                'order_number'      => $order->order_number,
                'status'            => $order->status,
                'payment_method'    => $order->payment_method,
                'payment_status'    => $order->payment_status,
                'total_amount'      => (float) $order->total_amount,
                'total_items'       => (int) $order->total_items,
                'first_item_name'   => $first_item->product_name ?? '',
                'first_item_image'  => $image_url,
                'created_at'        => $order->created_at,
            ];
        }, $orders);

        $this->send_response(true, 'Orders fetched successfully.', [
            'total_orders'   => $total_orders,
            'current_page'   => $current_page,
            'items_per_page' => $items_per_page,
            'total_pages'    => (int) ceil($total_orders / $items_per_page),
            'orders'         => $order_list,
        ]);
    }

    /*-----------------------------------------------------------------------
    | GET ORDER DETAILS
    | GET /api/get_order_details/{order_id}  [Auth required]
    |-----------------------------------------------------------------------*/
    public function get_order_details(int $order_id = 0): void
    {
        $user_id = $this->require_token_user_id();
        $this->ensure_orders_table();

        if ($order_id <= 0) {
            $this->send_response(false, 'A valid order ID is required.', null, 400);
        }

        $order = $this->format_order_full($order_id, $user_id);

        if (!$order) {
            $this->send_response(false, 'Order not found.', null, 404);
        }

        $this->send_response(true, 'Order details fetched successfully.', $order);
    }

    /*-----------------------------------------------------------------------
    | CANCEL ORDER
    | POST /api/cancel_order  [Auth required]
    | Body: { "order_id", "reason" }
    |-----------------------------------------------------------------------*/
    public function cancel_order(): void
    {
        $this->require_method('POST');

        $user_id = $this->require_token_user_id();
        $this->ensure_orders_table();

        $order_id = (int) $this->input_value('order_id');
        $reason   = $this->input_value('reason');

        if ($order_id <= 0) {
            $this->send_response(false, 'A valid order ID is required.', null, 400);
        }

        $order = $this->db->get_where('orders', [
            'id'      => $order_id,
            'user_id' => $user_id,
        ])->row();

        if (!$order) {
            $this->send_response(false, 'Order not found.', null, 404);
        }

        $cancellable_statuses = ['pending', 'confirmed'];
        if (!in_array($order->status, $cancellable_statuses, true)) {
            $this->send_response(false, 'Order cannot be cancelled. Current status: ' . $order->status, null, 400);
        }

        $this->db->trans_begin();

        $this->db->where('id', $order_id)->update('orders', [
            'status'        => 'cancelled',
            'cancelled_by'  => 'user',
            'cancel_reason' => $reason,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // Only restore stock if it was actually deducted (COD deducts at placement,
        // online deducts only after payment is verified/paid).
        if ($order->payment_method === 'cod' || $order->payment_status === 'paid') {
            $this->restore_stock_for_order($order_id);
        }

        $this->insert_status_history($order_id, 'cancelled', $reason !== '' ? $reason : 'Cancelled by user', 'user');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->send_response(false, 'Failed to cancel order. Please try again.', null, 500);
        }

        $this->db->trans_commit();

        $this->send_response(true, 'Order cancelled successfully.', [
            'order_id'     => $order_id,
            'order_number' => $order->order_number,
            'status'       => 'cancelled',
        ]);
    }

    /*=======================================================================
    | STATIC CONTENT ENDPOINTS
    |=======================================================================*/

    /*-----------------------------------------------------------------------
    | PRIVACY POLICY
    | GET /api/privacy_policy
    |-----------------------------------------------------------------------*/
    public function privacy_policy(): void
    {
        $page = $this->General_model->getOne('policy_pages', ['slug' => 'privacy_policy']);

        if ($page) {
            $title = $page->title;
            $content = $page->content;
            $last_updated = date('Y-m-d', strtotime($page->updated_at));
        } else {
            $title = 'Privacy Policy';
            $content = '
                <h2>1. Introduction</h2>
            <p>Ghanshyam Murtibhandar ("we", "us", "our") values the trust you place in us when you use our mobile application and website to browse and purchase idols, murtis, puja it                ems, and other religious products. This Privacy Policy explains what information we collect, how we use it, and the choices you have regarding your data.</p>

            <h2>2. Information We Collect</h2>
            <ul>
                <li><strong>Account Information:</strong> Full name, email address, mobile number, shop name (if applicable), and profile image.</li>
                <li><strong>Delivery Information:</strong> Shipping address(es), landmark, city, state, pincode, and country for order delivery.</li>
                <li><strong>Order & Transaction Information:</strong> Products viewed, added to cart, purchased, order history, quantities, and pricing.</li>
                <li><strong>Verification Information:</strong> Mobile number and OTP used to verify your identity during login.</li>
                <li><strong>Device & Usage Information:</strong> Device type, operating system, IP address, and app usage patterns, collected automatically for security and performance monitoring.</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <ul>
                <li>To create and manage your account and verify your identity via OTP.</li>
                <li>To process orders, calculate pricing, manage your cart, and arrange delivery of murtis and puja items to your saved address.</li>
                <li>To communicate order confirmations, delivery updates, and important account or service notifications.</li>
                <li>To respond to customer support queries and resolve complaints regarding orders, products, or account access.</li>
                <li>To detect fraudulent activity, prevent unauthorized access, and maintain the overall security of our platform.</li>
                <li>To improve our catalogue, app performance, and user experience based on usage patterns.</li>
            </ul>

            <h2>4. Information Sharing</h2>
            <p>We do not sell or rent your personal information to any third party. We may share limited information only in the following situations:</p>
            <ul>
                <li><strong>Delivery Partners:</strong> Your name, mobile number, and delivery address are shared with logistics/delivery partners solely to fulfil your order.</li>
                <li><strong>Service Providers:</strong> Trusted providers who help us with hosting, SMS/OTP delivery, and technical support, bound by confidentiality obligations.</li>
                <li><strong>Legal Requirements:</strong> If required by applicable law, regulation, or a valid legal request from government authorities.</li>
            </ul>

            <h2>5. Data Security</h2>
            <p>We use industry-standard practices to protect your information, including secure authentication (JWT-based sessions), OTP-based login verification, and restricted access to personal data. Login sessions can be invalidated at any time through logout, and inactive or deleted accounts are handled as described below.</p>

            <h2>6. Data Retention</h2>
            <p>We retain your account and order information for as long as your account remains active. If you request account deletion, your profile is deactivated and associated cart and address data is removed from active use, while limited transaction records may be retained as required for accounting or legal purposes.</p>

            <h2>7. Your Rights</h2>
            <ul>
                <li><strong>Access & Update:</strong> You can view and update your profile, email, mobile number, shop name, and address directly within the app.</li>
                <li><strong>Delete Account:</strong> You can request permanent deactivation of your account and removal of your saved addresses and cart data at any time.</li>
                <li><strong>Manage Addresses:</strong> You can add, edit, or delete any saved delivery address, and choose a default address.</li>
                <li><strong>Logout:</strong> You can log out at any time, which immediately invalidates your active session token.</li>
            </ul>

            <h2>8. Children\'s Privacy</h2>
            <p>Our services are intended for users who are 18 years of age or older. We do not knowingly collect personal information from minors. If we become aware that a minor\'s data has been collected, we will take steps to promptly delete it.</p>

            <h2>9. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time to reflect changes in our practices or for legal, operational, or regulatory reasons. Continued use of the app after any update constitutes your acceptance of the revised policy.</p>

            <h2>10. Contact Us</h2>
            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or your personal data, please reach out to us through the support option available in the app.</p>
            ';
            $last_updated = date('Y-m-d');
        }

        $this->send_response(true, 'Privacy Policy fetched successfully.', [
            'title'        => $title,
            'app_name'     => 'Ghanshyam Murtibhandar',
            'last_updated' => $last_updated,
            'content'      => $content,
        ]);
    }

    /*-----------------------------------------------------------------------
    | TERMS & CONDITIONS
    | GET /api/terms_conditions
    |-----------------------------------------------------------------------*/
    public function terms_conditions(): void
    {
        $page = $this->General_model->getOne('policy_pages', ['slug' => 'terms_conditions']);

        if ($page) {
            $title = $page->title;
            $content = $page->content;
            $last_updated = date('Y-m-d', strtotime($page->updated_at));
        } else {
            $title = 'Terms & Conditions';
            $content = '
            <h2>1. Acceptance of Terms</h2>
            <p>By registering on or using the Ghanshyam Murtibhandar application, you agree to be bound by these Terms & Conditions. If you do not agree, please discontinue use of the app immediately. These terms apply to all registered users browsing or purchasing idols, murtis, and religious products through our platform.</p>

            <h2>2. Account Registration & Verification</h2>
            <p>You must provide accurate name, email, and mobile number details at the time of registration. Login is verified using a One-Time Password (OTP) sent to your registered mobile number. You are responsible for keeping your OTP confidential and must not share it with anyone. Any activity carried out through a successfully verified session is considered authorized by you.</p>

            <h2>3. Product Listings, Pricing & Availability</h2>
            <p>All idols, murtis, and puja items listed on the app are subject to availability. We make reasonable efforts to display accurate product images, descriptions, pricing (price and MRP), and stock levels; however, prices, offers, and stock quantity may change without prior notice. Since many of our products are handcrafted, minor variations in size, colour, finish, or design compared to the displayed image may occur.</p>

            <h2>4. Cart & Ordering</h2>
            <p>You may add products to your cart, update quantities, or remove items before placing an order. Orders are subject to confirmation of product availability at the time of checkout. We reserve the right to cancel or adjust an order if a product becomes unavailable, incorrectly priced, or out of stock after it was added to your cart.</p>

            <h2>5. Delivery</h2>
            <p>You are responsible for providing a complete and accurate delivery address, including city, state, pincode, and landmark, to ensure smooth delivery. Delivery timelines may vary based on your location, product type, and courier availability. As many products are fragile (idols and murtis), please inspect your package carefully at the time of delivery and report any visible damage immediately through customer support.</p>

            <h2>6. Cancellation & Returns</h2>
            <p>Cancellation requests are accepted only before an order has been dispatched. Given the delicate and often customized nature of murtis and religious items, returns or replacements are considered only in cases of damage during transit, manufacturing defects, or an incorrect item being delivered, and must be reported within a reasonable time of delivery with supporting evidence (such as photographs).</p>

            <h2>7. User Conduct</h2>
            <p>You agree not to misuse the app, attempt unauthorized access to other accounts, submit false information, or use the platform for any unlawful purpose. Any misuse, fraudulent activity, or abusive behaviour towards our support team may result in restriction, suspension, or permanent deactivation of your account.</p>

            <h2>8. Account Deactivation</h2>
            <p>You may request deletion of your account at any time. Upon deletion, your account is deactivated, and your saved cart and address information will be removed. Certain order records may be retained as required for accounting, legal, or dispute-resolution purposes.</p>

            <h2>9. Intellectual Property</h2>
            <p>All content on the app, including product images, descriptions, logos, and design elements, is the property of Ghanshyam Murtibhandar and is protected under applicable intellectual property laws. You may not copy, reproduce, or redistribute any content without our prior written consent.</p>

            <h2>10. Limitation of Liability</h2>
            <p>We strive to ensure accurate listings and timely delivery; however, we shall not be held liable for delays, damages, or losses caused by factors beyond our reasonable control, including courier delays, incorrect address information provided by the user, or unforeseen circumstances. Our liability, where applicable, shall be limited to the value of the specific order in question.</p>

            <h2>11. Changes to These Terms</h2>
            <p>We may revise these Terms & Conditions periodically to reflect changes in our services, policies, or legal requirements. Continued use of the app after such changes constitutes your acceptance of the updated terms.</p>

            <h2>12. Governing Law</h2>
            <p>These Terms & Conditions shall be governed by and construed in accordance with the laws of India, and any disputes shall be subject to the jurisdiction of the competent courts in the applicable region.</p>

            <h2>13. Contact Us</h2>
            <p>For any questions regarding these Terms & Conditions, please contact us through the support option available within the Ghanshyam Murtibhandar app.</p>
            ';
            $last_updated = date('Y-m-d');
        }

        $this->send_response(true, 'Terms & Conditions fetched successfully.', [
            'title'        => $title,
            'app_name'     => 'Ghanshyam Murtibhandar',
            'last_updated' => $last_updated,
            'content'      => $content,
        ]);
    }

    /*-----------------------------------------------------------------------
    | REFUND POLICY
    | GET /api/refund_policy
    |-----------------------------------------------------------------------*/
    public function refund_policy(): void
    {
        $page = $this->General_model->getOne('policy_pages', ['slug' => 'refund_policy']);

        if ($page) {
            $title = $page->title;
            $content = $page->content;
            $last_updated = date('Y-m-d', strtotime($page->updated_at));
        } else {
            $title = 'Refund Policy';
            $content = '
            <h2>1. Overview</h2>
            <p>Ghanshyam Murtibhandar aims to provide carefully packed idols, murtis, puja items, and religious products. This Refund Policy explains when cancellations, replacements, and refunds may be considered for purchases made through our application or website.</p>

            <h2>2. Order Cancellation</h2>
            <p>You may request cancellation only before the order has been dispatched. Once an order is dispatched or handed over to the delivery partner, cancellation may not be available. If a prepaid order is successfully cancelled before dispatch, the eligible refund will be processed to the original payment method.</p>

            <h2>3. Damaged, Defective, or Incorrect Items</h2>
            <p>Because many products are fragile and handcrafted, refunds or replacements are considered only when an item is damaged in transit, has a manufacturing defect, or the wrong item is delivered. Please report the issue through customer support as soon as possible after delivery and share clear photographs or video evidence of the product, packaging, and invoice/order details.</p>

            <h2>4. Non-Refundable Cases</h2>
            <ul>
                <li>Products damaged due to misuse, mishandling, or improper installation after delivery.</li>
                <li>Minor colour, size, finish, or design variations in handcrafted idols or murtis.</li>
                <li>Requests made without required proof such as photos, videos, or order details.</li>
                <li>Customized, made-to-order, or specially arranged products, unless damaged, defective, or incorrect.</li>
                <li>Products returned without prior approval from our support team.</li>
                <li>Products returned without original packaging.</li>
            </ul>

            <h2>5. Return & Replacement Process</h2>
            <p>If your request is approved, our support team will guide you through the return, replacement, or refund process. The product must be unused, complete, and returned with original packaging, accessories, and invoice where applicable. Replacement is subject to product availability.</p>

            <h2>6. Refund Processing</h2>
            <p>Approved refunds for prepaid orders will be initiated to the original payment method. The time taken for the refunded amount to reflect may vary depending on the bank, payment gateway, or wallet provider. Shipping, handling, or convenience charges may be deducted where applicable unless the refund is due to our error.</p>

            <h2>7. Cash on Delivery Orders</h2>
            <p>For eligible Cash on Delivery orders, refunds may be processed through a bank transfer or another available method after verification of the customer and order details.</p>

            <h2>8. Final Decision</h2>
            <p>All refund, return, and replacement requests are reviewed by Ghanshyam Murtibhandar based on product condition, evidence provided, order status, and applicable policy terms. Our decision will be final in cases of misuse, incomplete evidence, or policy abuse.</p>

            <h2>9. Policy Updates</h2>
            <p>We may update this Refund Policy from time to time to reflect changes in our services, logistics process, payment providers, or legal requirements. Continued use of the app after any update constitutes your acceptance of the revised policy.</p>

            <h2>10. Contact Us</h2>
            <p>For cancellation, return, replacement, or refund requests, please contact us through the support option available within the Ghanshyam Murtibhandar app.</p>
            ';
            $last_updated = date('Y-m-d');
        }

        $this->send_response(true, 'Refund Policy fetched successfully.', [
            'title'        => $title,
            'app_name'     => 'Ghanshyam Murtibhandar',
            'last_updated' => $last_updated,
            'content'      => $content,
        ]);
    }

    public function logout_user(): void
    {
        $this->require_method('POST');

        $decoded = $this->validate_token(true);
        $token = $decoded->token;

        if (!$this->db->table_exists('token_blacklist')) {
            $this->send_response(false, 'token_blacklist table is missing. Please create it first.', null, 500);
        }

        $exists = $this->db
            ->where('token', $token)
            ->count_all_results('token_blacklist') > 0;

        if (!$exists) {
            $this->db->insert('token_blacklist', [
                'token'      => $token,
                'expires_at' => date('Y-m-d H:i:s', $decoded->exp ?? time()),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->send_response(true, 'Logout successful - token invalidated.');
    }

    /*-----------------------------------------------------------------------
    | HELP & SUPPORT
    | GET /api/help_support
    |-----------------------------------------------------------------------*/
    public function help_support(): void
    {
        $settings = $this->db->get_where('help_support', ['id' => 1])->row();

        if (!$settings) {
            $settings = (object) [
                'phone_number'    => '',
                'email'           => '',
                'whatsapp_number' => '',
                'telegram_link'   => '',
                'instagram_link'  => '',
                'facebook_link'   => '',
                'youtube_link'    => ''
            ];
        }

        $phone = $settings->phone_number ?? '';
        $email = $settings->email ?? '';

        // Fallback: If phone_number or email is empty, fetch them from the admin profile
        if (empty($phone) || empty($email)) {
            $admin = $this->db->get_where('users', ['role' => 'admin'])->row();
            if (!$admin) {
                $admin = $this->db->get_where('users', ['role' => 1])->row();
            }
            if (!$admin) {
                $admin = $this->db->order_by('id', 'ASC')->get('users')->row();
            }
            if ($admin) {
                if (empty($phone)) {
                    $phone = $admin->mobile ?? '';
                }
                if (empty($email)) {
                    $email = $admin->email ?? '';
                }
            }
        }

        $this->send_response(true, 'Help & Support settings fetched successfully.', [
            'phone_number'    => $phone,
            'email'           => $email,
            'whatsapp_number' => $settings->whatsapp_number ?? '',
            'telegram_link'   => $settings->telegram_link ?? '',
            'instagram_link'  => $settings->instagram_link ?? '',
            'facebook_link'   => $settings->facebook_link ?? '',
            'youtube_link'    => $settings->youtube_link ?? '',
            'updated_at'      => $settings->updated_at ?? date('Y-m-d H:i:s')
        ]);
    }
}
