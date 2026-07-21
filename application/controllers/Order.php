<?php defined('BASEPATH') or exit('No direct script access allowed');

class Order extends CI_Controller
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
    | Product List
    |--------------------------------------------------------------------------
    */
   public function index()
    {
        $data['page_title'] = 'Orders Management';
        
        $this->load->view('includes/header', $data);
        $this->load->view('order_view', $data);
        $this->load->view('includes/footer', $data);
    }

    // AJAX: Get orders with pagination and filters
    public function get_orders()
    {
        $search         = trim($this->input->get_post('search', true) ?? '');
        $status         = trim($this->input->get_post('status', true) ?? '');
        $payment_status = trim($this->input->get_post('payment_status', true) ?? '');
        $page           = (int)($this->input->get_post('page', true) ?: 1);
        $limit          = 20;
        $offset         = ($page - 1) * $limit;

        // Build query
        $this->db->select('orders.*, 
                    users.name as customer_name, 
                    users.email as customer_email, 
                    users.mobile as customer_phone,
                    user_addresses.address_line1,
                    user_addresses.city,
                    user_addresses.pincode');
        $this->db->from('orders');
        $this->db->join('users', 'users.id = orders.user_id', 'left');
        $this->db->join('user_addresses', 'user_addresses.id = orders.address_id', 'left');

        // Apply filters
        if (!empty($status)) {
            $this->db->where('orders.status', $status);
        }
        if (!empty($payment_status)) {
            $this->db->where('orders.payment_status', $payment_status);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('orders.order_number', $search);
            $this->db->or_like('users.name', $search);
            $this->db->or_like('users.mobile', $search);
            $this->db->or_like('users.email', $search);
            $this->db->group_end();
        }

        // Get total
        $total_records = $this->db->count_all_results('', false);
        $total_pages   = ceil($total_records / $limit);

        // Get paginated results
        $orders = $this->db
            ->order_by('orders.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();

        // Generate HTML
        $html       = $this->generate_orders_html($orders, $offset);
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

    // Generate orders table HTML
    private function generate_orders_html($orders, $offset = 0)
    {
        if (empty($orders)) {
            return '
                <tr>
                    <td colspan="9" style="text-align:center; padding:50px; color:#666;">
                        <i class="fas fa-shopping-cart" style="font-size:42px; margin-bottom:14px; display:block;"></i>
                        <p>No orders found.</p>
                    </td>
                </tr>
            ';
        }

        $html = '';
        foreach ($orders as $i => $order) {
            $serial = $offset + $i + 1;

            // AWB code
            $awb_html = '';
            if (!empty($order['awb_code'])) {
                $awb_html = '
                    <div style="font-size:10px; color:#4caf50; margin-top:2px;">
                        <i class="fas fa-truck"></i> AWB: ' . htmlspecialchars($order['awb_code'], ENT_QUOTES, 'UTF-8') . '<br>
                        <span style="color:#999;">' . htmlspecialchars($order['courier_name'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>
                    </div>
                ';
            }

            // Razorpay ID
            $razorpay_html = '';
            if (!empty($order['razorpay_order_id'])) {
                $razorpay_html = '
                    <div style="font-size:10px; color:#666; margin-top:2px;">
                        ' . htmlspecialchars(substr($order['razorpay_order_id'], 0, 20), ENT_QUOTES, 'UTF-8') . '...
                    </div>
                ';
            }

            // Payment badge
            $payment_badges = [
                'pending' => ['class' => 'badge-inactive', 'text' => 'Pending'],
                'paid'    => ['class' => 'badge-active', 'text' => 'Paid'],
                'failed'  => ['class' => 'badge-cancelled', 'text' => 'Failed']
            ];
            $payment_badge = $payment_badges[$order['payment_status']] ?? $payment_badges['pending'];

            // Status badge
            $status_badges = [
                'pending'    => 'badge-pending',
                'confirmed'  => 'badge-confirmed',
                'processing' => 'badge-processing',
                'shipped'    => 'badge-shipped',
                'delivered'  => 'badge-delivered',
                'cancelled'  => 'badge-cancelled'
            ];
            $status_class = $status_badges[$order['status']] ?? 'badge-pending';

            // Delivery charge
            $delivery_html = '';
            if (!empty($order['delivery_charge']) && $order['delivery_charge'] > 0) {
                $delivery_html = '
                    <div style="font-size:10px; color:#666;">
                        Delivery: ₹' . number_format($order['delivery_charge'], 2) . '
                    </div>
                ';
            }

            // Discount
            $discount_html = '';
            if (!empty($order['discount']) && $order['discount'] > 0) {
                $discount_html = '
                    <div style="font-size:10px; color:#ff9800;">
                        Discount: -₹' . number_format($order['discount'], 2) . '
                    </div>
                ';
            }

            // Courier assignment dropdown item
            $courier_item = '';
            if (!empty($order['shiprocket_shipment_id']) && empty($order['awb_code'])) {
                $courier_item = '
                    <li><a class="dropdown-item text-info" href="#" onclick="openCourierModal(' . (int)$order['id'] . ')">Assign Courier</a></li>
                ';
            } elseif (!empty($order['awb_code'])) {
                $courier_item = '
                    <li><a class="dropdown-item text-success disabled" href="#">
                        Shipped (AWB: ' . htmlspecialchars($order['awb_code'], ENT_QUOTES, 'UTF-8') . ')
                    </a></li>
                ';
            }

            // Invoice button
            $invoice_btn = '';
            if ($order['status'] === 'delivered') {
                $invoice_btn = '
                    <a href="' . site_url('admin/orders/invoice/' . (int)$order['id']) . '" 
                       class="action-btn" 
                       title="Download Invoice">
                        <i class="fas fa-file-invoice"></i>
                    </a>
                ';
            }

            $html .= '
                <tr>
                    <td style="color:#666;">' . $serial . '</td>
                    
                    <td>
                        <div style="font-weight:600; color:var(--primary-red);">
                            ' . htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') . '
                        </div>
                        ' . $razorpay_html . '
                        ' . $awb_html . '
                    </td>

                    <td>
                        <div style="font-weight:600;">
                            ' . htmlspecialchars($order['customer_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '
                        </div>
                        <div style="font-size:11px; color:#999;">
                            ' . htmlspecialchars($order['customer_phone'] ?? '', ENT_QUOTES, 'UTF-8') . '
                        </div>
                        <div style="font-size:11px; color:#666;">
                            ' . htmlspecialchars($order['customer_email'] ?? '', ENT_QUOTES, 'UTF-8') . '
                        </div>
                    </td>

                    <td>
                        <span style="background:rgba(255,255,255,0.05); padding:4px 10px; border-radius:6px; font-weight:600;">
                            ' . (int)$order['total_items'] . ' item' . ((int)$order['total_items'] > 1 ? 's' : '') . '
                        </span>
                    </td>

                    <td>
                        <div style="font-weight:700; color:#4caf50; font-size:15px;">
                            ₹' . number_format($order['total_amount'], 2) . '
                        </div>
                        <div style="font-size:10px; color:#666; margin-top:3px;">
                            Subtotal: ₹' . number_format($order['subtotal'], 2) . '
                        </div>
                        ' . $delivery_html . '
                        ' . $discount_html . '
                    </td>

                    <td>
                        <div style="font-size:11px; color:#999; text-transform:uppercase; margin-bottom:4px;">
                            ' . ($order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online') . '
                        </div>
                        <span class="' . $payment_badge['class'] . '">
                            ' . $payment_badge['text'] . '
                        </span>
                    </td>

                    <td>
                        <span class="order-status-badge ' . $status_class . '">
                            ' . ucfirst($order['status']) . '
                        </span>
                    </td>

                    <td>
                        <div style="font-size:12px; color:#ccc;">
                            ' . date('d M Y', strtotime($order['created_at'])) . '
                        </div>
                        <div style="font-size:11px; color:#666;">
                            ' . date('h:i A', strtotime($order['created_at'])) . '
                        </div>
                    </td>

                    <td>
                        <div style="display:flex; gap:5px;">
                            <a href="' . site_url('order/view/' . (int)$order['id']) . '" 
                               class="action-btn view" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <div class="dropdown" style="display:inline-block;">
                                <button class="action-btn" 
                                        title="Update Status" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(' . (int)$order['id'] . ', \'confirmed\')">Mark Confirmed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(' . (int)$order['id'] . ', \'processing\')">Mark Processing</a></li>
                                    ' . $courier_item . '
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(' . (int)$order['id'] . ', \'delivered\')">Mark Delivered</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(' . (int)$order['id'] . ', \'cancelled\')">Cancel Order</a></li>
                                </ul>
                            </div>

                            ' . $invoice_btn . '
                        </div>
                    </td>
                </tr>
            ';
        }

        return $html;
    }

    // Generate pagination HTML
    private function generate_pagination($current_page, $total_pages)
    {
        if ($total_pages <= 1) {
            return '';
        }

        $html  = '<div class="custom-pagination">';
        $html .= '<div class="pagination-container">';

        // Previous button
        if ($current_page > 1) {
            $html .= '
                <button class="pagination-btn" data-page="' . ($current_page - 1) . '" title="Previous">
                    <i class="fas fa-chevron-left"></i>
                </button>
            ';
        } else {
            $html .= '
                <button class="pagination-btn disabled" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
            ';
        }

        // Calculate 3 page buttons
        $start_page = max(1, $current_page - 1);
        $end_page   = min($total_pages, $start_page + 2);

        if ($end_page - $start_page < 2) {
            $start_page = max(1, $end_page - 2);
        }

        // Page buttons
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $i == $current_page ? 'active' : '';
            $html .= '
                <button class="pagination-btn ' . $active . '" data-page="' . $i . '">
                    ' . $i . '
                </button>
            ';
        }

        // Last page with dots
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                $html .= '<span class="pagination-dots">...</span>';
            }
            $html .= '
                <button class="pagination-btn" data-page="' . $total_pages . '">
                    ' . $total_pages . '
                </button>
            ';
        }

        // Next button
        if ($current_page < $total_pages) {
            $html .= '
                <button class="pagination-btn" data-page="' . ($current_page + 1) . '" title="Next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            ';
        } else {
            $html .= '
                <button class="pagination-btn disabled" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            ';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

  public function view($order_id)
{
    // Get order details with customer and address
    $this->db->select('orders.*, 
                      users.name as customer_name, 
                      users.email as customer_email, 
                      users.mobile as customer_phone,
                      user_addresses.full_name,
                      user_addresses.mobile as delivery_mobile,
                      user_addresses.address_line1,
                      user_addresses.address_line2,
                      user_addresses.landmark,
                      user_addresses.city,
                      user_addresses.state,
                      user_addresses.pincode,
                      user_addresses.country');
    $this->db->from('orders');
    $this->db->join('users', 'users.id = orders.user_id', 'left');
    $this->db->join('user_addresses', 'user_addresses.id = orders.address_id', 'left');
    $this->db->where('orders.id', $order_id);
    $order = $this->db->get()->row_array();

    if (!$order) {
        $this->session->set_flashdata('error', 'Order not found.');
        redirect('admin/orders');
    }

    // Get order items
    $this->db->select('order_items.*, products.name as product_name, products.image');
    $this->db->from('order_items');
    $this->db->join('products', 'products.id = order_items.product_id', 'left');
    $this->db->where('order_items.order_id', $order_id);
    $order['items'] = $this->db->get()->result_array();

    // Get status history
    $this->db->where('order_id', $order_id);
    $this->db->order_by('created_at', 'DESC');
    $order['status_history'] = $this->db->get('order_status_history')->result_array();

    $data['order'] = $order;
    $data['page_title'] = 'Order Details - ' . $order['order_number'];

    $this->load->view('includes/header', $data);
    $this->load->view('order_details', $data);
    $this->load->view('includes/footer', $data);
}

    public function update_status($order_id, $new_status)
    {
        $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($new_status, $valid_statuses)) {
            $this->session->set_flashdata('error', 'Invalid status.');
            redirect('admin/orders');
        }
if ($new_status === 'confirmed') {
    $this->load->library('shiprocket');
    $this->shiprocket->create_order($order_id);
}
        $this->db->where('id', $order_id);
        $this->db->update('orders', [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Insert status history
        $this->db->insert('order_status_history', [
            'order_id' => $order_id,
            'status' => $new_status,
            'remarks' => 'Status updated by admin',
            'changed_by' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        

        $this->session->set_flashdata('success', 'Order status updated successfully.');
        redirect('order/view/' . $order_id);
    }

 public function assign_courier($order_id)
{
    $courier_id = $this->input->post('courier_id');
    $etd = $this->input->post('etd');
    $order = $this->db->get_where('orders', ['id' => $order_id])->row();

    $this->load->library('shiprocket');
    $response = $this->shiprocket->assign_awb($order->shiprocket_shipment_id, $courier_id);

    if (!empty($response['response']['data']['awb_code'])) {
        $this->db->where('id', $order_id)->update('orders', [
            'awb_code'                => $response['response']['data']['awb_code'],
            'courier_name'            => $response['response']['data']['courier_name'] ?? null,
            'expected_delivery_date'  => $etd,
            'status'                  => 'shipped',
            'updated_at'              => date('Y-m-d H:i:s'),
        ]);

        $this->db->insert('order_status_history', [
            'order_id'   => $order_id,
            'status'     => 'shipped',
            'comment'    => 'Courier assigned: ' . ($response['response']['data']['courier_name'] ?? ''),
            'updated_by' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        echo json_encode([
            'status'   => true,
            'awb_code' => $response['response']['data']['awb_code'],
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'message' => $response['message'] ?? 'Failed to assign courier',
        ]);
    }
}
public function get_couriers($order_id)
{
    $order = $this->db->select('orders.*, user_addresses.pincode')
        ->from('orders')
        ->join('user_addresses', 'user_addresses.id = orders.address_id', 'left')
        ->where('orders.id', $order_id)
        ->get()->row();

    if (!$order || empty($order->shiprocket_shipment_id)) {
        echo json_encode(['status' => false, 'message' => 'Shipment not created yet']);
        return;
    }

    $this->load->library('shiprocket');
    $result = $this->shiprocket->get_available_couriers($order->shiprocket_shipment_id);

    echo json_encode($result);
}
public function schedule_pickup($order_id)
{
    $order = $this->db->get_where('orders', ['id' => $order_id])->row();
    $this->load->library('shiprocket');
    $response = $this->shiprocket->schedule_pickup($order->shiprocket_shipment_id);

    if (!empty($response['pickup_status']) || stripos($response['message'] ?? '', 'already') !== false) {
        $this->db->where('id', $order_id)->update('orders', ['pickup_scheduled' => 1]);
        echo json_encode(['status' => true, 'message' => $response['message'] ?? 'Pickup scheduled successfully']);
    } else {
        echo json_encode(['status' => false, 'message' => $response['message'] ?? 'Failed to schedule pickup']);
    }
}

public function download_label($order_id)
{
    $order = $this->db->get_where('orders', ['id' => $order_id])->row();
    if (!$order) {
        echo json_encode(['status' => false, 'message' => 'Order not found']);
        return;
    }

    if (!empty($order->shiprocket_shipment_id)) {
        $this->load->library('shiprocket');
        $response = $this->shiprocket->generate_label($order->shiprocket_shipment_id);
        if (!empty($response['label_url'])) {
            $this->db->where('id', $order_id)->update('orders', ['label_url' => $response['label_url']]);
        }
    }

    echo json_encode(['status' => true, 'url' => site_url('order/print_label/' . $order_id)]);
}

public function print_label($order_id)
{
    $this->db->select('orders.*, 
                      users.name as customer_name, 
                      users.email as customer_email, 
                      users.mobile as customer_phone,
                      user_addresses.full_name,
                      user_addresses.mobile as delivery_mobile,
                      user_addresses.address_line1,
                      user_addresses.address_line2,
                      user_addresses.landmark,
                      user_addresses.city,
                      user_addresses.state,
                      user_addresses.pincode,
                      user_addresses.country');
    $this->db->from('orders');
    $this->db->join('users', 'users.id = orders.user_id', 'left');
    $this->db->join('user_addresses', 'user_addresses.id = orders.address_id', 'left');
    $this->db->where('orders.id', $order_id);
    $order = $this->db->get()->row_array();

    if (!$order) {
        show_404();
    }

    $this->db->select('order_items.*, products.name as product_name');
    $this->db->from('order_items');
    $this->db->join('products', 'products.id = order_items.product_id', 'left');
    $this->db->where('order_items.order_id', $order_id);
    $order['items'] = $this->db->get()->result_array();

    $data['order']      = $order;
    $data['page_title'] = 'Shipping Label - ' . $order['order_number'];

    $this->load->view('shipping_label_view', $data);
}

public function download_invoice($order_id)
{
    $order = $this->db->get_where('orders', ['id' => $order_id])->row();
    if (!$order) {
        echo json_encode(['status' => false, 'message' => 'Order not found']);
        return;
    }

    if (!empty($order->shiprocket_order_id)) {
        $this->load->library('shiprocket');
        $response = $this->shiprocket->generate_invoice($order->shiprocket_order_id);
        if (!empty($response['invoice_url'])) {
            $this->db->where('id', $order_id)->update('orders', ['invoice_url' => $response['invoice_url']]);
        }
    }

    echo json_encode(['status' => true, 'url' => site_url('order/print_invoice/' . $order_id)]);
}

public function print_invoice($order_id)
{
    $this->db->select('orders.*, 
                      users.name as customer_name, 
                      users.email as customer_email, 
                      users.mobile as customer_phone,
                      user_addresses.full_name,
                      user_addresses.mobile as delivery_mobile,
                      user_addresses.address_line1,
                      user_addresses.address_line2,
                      user_addresses.landmark,
                      user_addresses.city,
                      user_addresses.state,
                      user_addresses.pincode,
                      user_addresses.country');
    $this->db->from('orders');
    $this->db->join('users', 'users.id = orders.user_id', 'left');
    $this->db->join('user_addresses', 'user_addresses.id = orders.address_id', 'left');
    $this->db->where('orders.id', $order_id);
    $order = $this->db->get()->row_array();

    if (!$order) {
        show_404();
    }

    $this->db->select('order_items.*, products.name as product_name');
    $this->db->from('order_items');
    $this->db->join('products', 'products.id = order_items.product_id', 'left');
    $this->db->where('order_items.order_id', $order_id);
    $order['items'] = $this->db->get()->result_array();

    $data['order']      = $order;
    $data['page_title'] = 'Tax Invoice - ' . $order['order_number'];

    $this->load->view('invoice_view', $data);
}
public function refresh_tracking($order_id)
{
    $order = $this->db->get_where('orders', ['id' => $order_id])->row();

    if (!$order || empty($order->shiprocket_shipment_id)) {
        echo json_encode(['status' => false, 'message' => 'Shipment not created yet']);
        return;
    }

    $this->load->library('shiprocket');
    $result = $this->shiprocket->track_order($order_id);

    if (!empty($result['data']['tracking_data']['shipment_track'][0]['current_status'])) {
        $status = $result['data']['tracking_data']['shipment_track'][0]['current_status'];
        $this->db->where('id', $order_id)->update('orders', [
            'tracking_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    echo json_encode(['status' => true, 'data' => $result]);
}
}