<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shiprocket
{
    protected $CI;
    protected $base_url;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('shiprocket');
        $this->CI->load->database();
        $this->base_url = $this->CI->config->item('shiprocket_base_url');
    }

    
    public function get_token()
    {
        $row = $this->CI->db->order_by('id', 'DESC')->limit(1)->get('shiprocket_tokens')->row();

        if ($row && strtotime($row->expires_at) > time()) {
            return $row->token;
        }

        return $this->login_and_store_token();
    }

    protected function login_and_store_token()
    {
        $email    = $this->CI->config->item('shiprocket_email');
        $password = $this->CI->config->item('shiprocket_password');

        $response = $this->curl_post($this->base_url . 'auth/login', [
            'email'    => $email,
            'password' => $password,
        ], null);

        if (empty($response['token'])) {
            log_message('error', 'Shiprocket login failed: ' . json_encode($response));
            return false;
        }

        $this->CI->db->insert('shiprocket_tokens', [
            'token'        => $response['token'],
            'generated_at' => date('Y-m-d H:i:s'),
            'expires_at'   => date('Y-m-d H:i:s', strtotime('+9 days')),
        ]);

        return $response['token'];
    }

    public function create_order($order_id)
    {
        $token = $this->get_token();
        if (!$token) {
            return ['status' => false, 'message' => 'Shiprocket auth failed'];
        }

        $this->CI->db->select('orders.*, users.name as customer_name, users.email as customer_email, users.mobile as customer_phone, user_addresses.*');
        $this->CI->db->from('orders');
        $this->CI->db->join('users', 'users.id = orders.user_id', 'left');
        $this->CI->db->join('user_addresses', 'user_addresses.id = orders.address_id', 'left');
        $this->CI->db->where('orders.id', $order_id);
        $order = $this->CI->db->get()->row();

        if (!$order) {
            return ['status' => false, 'message' => 'Order not found'];
        }

        $items = $this->CI->db->select('order_items.*, products.name, products.weight')
            ->from('order_items')
            ->join('products', 'products.id = order_items.product_id', 'left')
            ->where('order_id', $order_id)
            ->get()->result();

        $order_items = [];
        $total_weight = 0;
        foreach ($items as $item) {
            $order_items[] = [
                'name'             => $item->name,
                'sku'              => 'SKU-' . $item->product_id,
                'units'            => (int) $item->quantity,
'selling_price' => (
    ($item->subtotal + $item->gst_amount)
    / $item->quantity
),     ];
            $total_weight += ((float) $item->weight ?: 0.5) * $item->quantity;
        }

        $payload = [
            'order_id'           => $order->order_number,
            'order_date'         => date('Y-m-d H:i', strtotime($order->created_at)),
            'pickup_location'    => $this->CI->config->item('shiprocket_pickup_location'),
            'billing_customer_name' => $order->customer_name,
            'billing_last_name'  => '',
            'billing_address'    => $order->address_line1,
            'billing_city'       => $order->city,
            'billing_pincode'    => $order->pincode,
            'billing_state'      => $order->state,
            'billing_country'    => 'India',
            'billing_email'      => $order->customer_email,
            'billing_phone'      => $order->customer_phone,
            'shipping_is_billing' => true,
            'order_items'        => $order_items,
            'payment_method'     => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
'sub_total' => (float) $order->total_amount,
            'length'             => 10,
            'breadth'            => 10,
            'height'             => 10,
            'weight'             => $total_weight ?: 0.5,
        ];

        $response = $this->curl_post($this->base_url . 'orders/create/adhoc', $payload, $token);
       
        if (!empty($response['order_id'])) {
            $this->CI->db->where('id', $order_id)->update('orders', [
                'shiprocket_order_id'    => $response['order_id'],
                'shiprocket_shipment_id' => $response['shipment_id'],
                'shiprocket_synced'      => 1,
            ]);
            return ['status' => true, 'data' => $response];
        }

        return ['status' => false, 'message' => $response['message'] ?? 'Failed to create Shiprocket order'];
    }

    /** Track shipment via AWB or shipment_id */
    public function track_order($order_id)
    {
        $token = $this->get_token();
        $order = $this->CI->db->get_where('orders', ['id' => $order_id])->row();

        if (!$order || empty($order->shiprocket_shipment_id)) {
            return ['status' => false, 'message' => 'Shipment not yet created'];
        }

        $url = $this->base_url . 'courier/track/shipment/' . $order->shiprocket_shipment_id;
        $response = $this->curl_get($url, $token);

        return ['status' => true, 'data' => $response];
    }

    /** Check delivery charge/serviceability before checkout */
    public function check_serviceability($pickup_pincode, $delivery_pincode, $weight = 0.5, $cod = 0)
    {
        $token = $this->get_token();

        $url = $this->base_url . 'courier/serviceability/?' . http_build_query([
            'pickup_postcode'    => $pickup_pincode,
            'delivery_postcode'  => $delivery_pincode,
            'weight'             => $weight,
            'cod'                => $cod,
        ]);

        return $this->curl_get($url, $token);
    }
    
public function get_available_couriers($shipment_id)
{
    $token = $this->get_token();
    $order_row = $this->CI->db->select('orders.*, user_addresses.pincode')
        ->from('orders')
        ->join('user_addresses', 'user_addresses.id = orders.address_id', 'left')
        ->where('orders.shiprocket_shipment_id', $shipment_id)
        ->get()->row();

    if (!$order_row) {
        return ['status' => false, 'message' => 'Order not found'];
    }

    $items = $this->CI->db->select('order_items.quantity, products.weight')
        ->from('order_items')
        ->join('products', 'products.id = order_items.product_id', 'left')
        ->where('order_id', $order_row->id)
        ->get()->result();

    $total_weight = 0.0;
    foreach ($items as $item) {
        $total_weight += ((float) $item->weight ?: 0.5) * $item->quantity;
    }
    if ($total_weight <= 0) {
        $total_weight = 0.5;
    }

    $url = $this->base_url . 'courier/serviceability/?' . http_build_query([
        'pickup_postcode'   => $this->CI->config->item('shiprocket_pickup_pincode'),
        'delivery_postcode' => $order_row->pincode ?? '',
        'weight'            => $total_weight,
        'cod'               => $order_row->payment_method === 'cod' ? 1 : 0,
    ]);

    return $this->curl_get($url, $token);
}
public function assign_awb($shipment_id, $courier_id = null)
{
    $token = $this->get_token();
    $payload = ['shipment_id' => $shipment_id];
    if ($courier_id) {
        $payload['courier_id'] = $courier_id;
    }

    $response = $this->curl_post($this->base_url . 'courier/assign/awb', $payload, $token);
    log_message('error', 'AWB ASSIGN RESPONSE: ' . json_encode($response));
    return $response;
}
public function schedule_pickup($shipment_id)
{
    $token = $this->get_token();
    $response = $this->curl_post($this->base_url . 'courier/generate/pickup', [
        'shipment_id' => [$shipment_id],
    ], $token);

    log_message('error', 'SCHEDULE PICKUP RESPONSE: ' . json_encode($response));
    return $response;
}

public function generate_label($shipment_id)
{
    $token = $this->get_token();
    log_message('error', 'LABEL - raw value received: [' . var_export($shipment_id, true) . ']');

    $payload = ['shipment_id' => [(int) $shipment_id]];
    log_message('error', 'LABEL - payload being sent: ' . json_encode($payload));

    $response = $this->curl_post($this->base_url . 'courier/generate/label', $payload, $token);
    log_message('error', 'LABEL RESPONSE: ' . json_encode($response));
    return $response;
}
public function generate_invoice($shiprocket_order_id)
{
    $token = $this->get_token();
    log_message('error', 'INVOICE - raw value received: [' . var_export($shiprocket_order_id, true) . ']');

    $payload = ['ids' => [(int) $shiprocket_order_id]];
    log_message('error', 'INVOICE - payload being sent: ' . json_encode($payload));

    $response = $this->curl_post($this->base_url . 'orders/print/invoice', $payload, $token);
    log_message('error', 'INVOICE RESPONSE: ' . json_encode($response));
    return $response;
}
    protected function curl_post($url, $data, $token)
    {
        $headers = ['Content-Type: application/json'];
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    protected function curl_get($url, $token)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}