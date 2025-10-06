<?php
// tnt_smm_dashboard.php
// One-file PHP dashboard for TNT SMM API (Tailwind styled)
// Paste this file to your hosting (Glitch / InfinityFree / localhost)
// Set writable permissions if you want to store logs (not required)

session_start();

/**
 * Simple API wrapper class (based on the class you provided)
 */
class Api
{
    /** API URL */
    public $api_url = 'https://tntsmm.in/api/v2';

    /** Your API key */
    public $api_key = '';

    /** Add order */
    public function order($data)
    {
        $post = array_merge(['key' => $this->api_key, 'action' => 'add'], $data);
        return json_decode((string)$this->connect($post));
    }

    /** Get order status  */
    public function status($order_id)
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'status',
                'order' => $order_id
            ])
        );
    }

    /** Get orders status */
    public function multiStatus($order_ids)
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'status',
                'orders' => implode(',', (array)$order_ids)
            ])
        );
    }

    /** Get services */
    public function services()
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'services',
            ])
        );
    }

    /** Refill order */
    public function refill(int $orderId)
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'refill',
                'order' => $orderId,
            ])
        );
    }

    /** Refill orders */
    public function multiRefill(array $orderIds)
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'refill',
                'orders' => implode(',', $orderIds),
            ]),
            true
        );
    }

    /** Get refill status */
    public function refillStatus(int $refillId)
    {
         return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'refill_status',
                'refill' => $refillId,
            ])
        );
    }

    /** Get refill statuses */
    public function multiRefillStatus(array $refillIds)
    {
         return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'refill_status',
                'refills' => implode(',', $refillIds),
            ]),
            true
        );
    }

    /** Cancel orders */
    public function cancel(array $orderIds)
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'cancel',
                'orders' => implode(',', $orderIds),
            ]),
            true
        );
    }

    /** Get balance */
    public function balance()
    {
        return json_decode(
            $this->connect([
                'key' => $this->api_key,
                'action' => 'balance',
            ])
        );
    }

    private function connect($post)
    {
        $_post = [];
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; PHP TNT-SMM-Client)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}

// ---------------------------
// Configuration & bootstrap
// ---------------------------
$api = new Api();
// <-- YOUR API KEY: (you provided this key)
$api->api_key = '40f3e197c9adb234a5196f95df4bfa46';

// helpers
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Handle forms and actions
$messages = [];
$errors = [];
$lastOrder = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // create order
    if ($action === 'create_order') {
        $service = $_POST['service'] ?? '';
        $link = $_POST['link'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $runs = $_POST['runs'] ?? null;
        $interval = $_POST['interval'] ?? null;
        $comments = $_POST['comments'] ?? null;

        if (empty($service) || empty($link)) {
            $errors[] = 'Service and Link are required.';
        } else {
            $post = ['service' => $service, 'link' => $link];
            if ($quantity !== '') $post['quantity'] = $quantity;
            if ($runs !== '') $post['runs'] = $runs;
            if ($interval !== '') $post['interval'] = $interval;
            if (!empty($comments)) $post['comments'] = $comments;

            $order = $api->order($post);
            if ($order && isset($order->order)) {
                $messages[] = 'Order created: ' . e($order->order);
                $lastOrder = $order->order;
            } else {
                $errors[] = 'Unable to create order. API returned: ' . e(json_encode($order));
            }
        }
    }

    // check order status
    if ($action === 'check_status') {
        $order_id = $_POST['order_id'] ?? '';
        if (empty($order_id)) {
            $errors[] = 'Order ID is required.';
        } else {
            $status = $api->status($order_id);
            if ($status) {
                $_SESSION['last_status'] = $status;
                $messages[] = 'Status fetched for order ' . e($order_id);
            } else {
                $errors[] = 'Unable to fetch status.';
            }
        }
    }

    // refill
    if ($action === 'refill') {
        $order_id = $_POST['refill_order'] ?? '';
        if (empty($order_id)) {
            $errors[] = 'Order ID required for refill.';
        } else {
            $r = $api->refill((int)$order_id);
            if ($r) $messages[] = 'Refill response: ' . e(json_encode($r));
            else $errors[] = 'Refill failed.';
        }
    }

    // cancel
    if ($action === 'cancel') {
        $orders = $_POST['cancel_orders'] ?? '';
        if (empty($orders)) {
            $errors[] = 'Provide comma separated order IDs to cancel.';
        } else {
            $ids = array_map('trim', explode(',', $orders));
            $c = $api->cancel($ids);
            if ($c) $messages[] = 'Cancel response: ' . e(json_encode($c));
            else $errors[] = 'Cancel failed.';
        }
    }
}

// Fetch balances & services for display
$balance = $api->balance();
$services = $api->services();

?><!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>TNT SMM — Mini Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">TNT SMM — Mini Dashboard</h1>
      <div class="text-right">
        <div class="text-sm text-gray-600">API Key</div>
        <div class="font-mono text-sm">***<?= substr(e($api->api_key), -6) ?></div>
      </div>
    </div><?php if ($errors): ?>
  <div class="mb-4">
    <div class="bg-red-100 border border-red-200 text-red-800 p-3 rounded">
      <strong>Errors:</strong>
      <ul class="list-disc ml-5">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<?php if ($messages): ?>
  <div class="mb-4">
    <div class="bg-green-100 border border-green-200 text-green-800 p-3 rounded">
      <strong>Messages:</strong>
      <ul class="list-disc ml-5">
        <?php foreach ($messages as $m): ?>
          <li><?= e($m) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <!-- Balance Card -->
  <div class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-2">Balance</h2>
    <?php if ($balance && isset($balance->balance)): ?>
      <div class="text-3xl font-bold"><?= e($balance->balance) ?></div>
      <div class="text-sm text-gray-600">Currency: <?= e($balance->currency ?? '') ?></div>
    <?php else: ?>
      <div class="text-sm text-gray-600">Unable to load balance.</div>
    <?php endif; ?>
  </div>

  <!-- Services Card -->
  <div class="bg-white p-4 rounded shadow md:col-span-2">
    <div class="flex justify-between items-center mb-3">
      <h2 class="font-semibold">Services</h2>
      <form method="get">
        <button class="text-sm px-3 py-1 bg-gray-100 rounded border hover:bg-gray-50">Refresh</button>
      </form>
    </div>

    <?php if ($services && is_array($services)): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Name</th>
              <th class="p-2 text-left">Category</th>
              <th class="p-2 text-left">Rate</th>
              <th class="p-2 text-left">Min</th>
              <th class="p-2 text-left">Max</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($services as $s): ?>
              <tr class="border-t">
                <td class="p-2 align-top font-mono"><?= e($s->service ?? '') ?></td>
                <td class="p-2 align-top"><?= e($s->name ?? '') ?></td>
                <td class="p-2 align-top"><?= e($s->category ?? '') ?></td>
                <td class="p-2 align-top"><?= e($s->rate ?? '') ?></td>
                <td class="p-2 align-top"><?= e($s->min ?? '') ?></td>
                <td class="p-2 align-top"><?= e($s->max ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-sm text-gray-600">No services found or API error.</div>
    <?php endif; ?>
  </div>

  <!-- Order Form -->
  <div class="bg-white p-4 rounded shadow md:col-span-2">
    <h2 class="font-semibold mb-3">Create Order</h2>
    <form method="post" class="space-y-3">
      <input type="hidden" name="action" value="create_order">

      <div>
        <label class="text-sm block mb-1">Service</label>
        <select name="service" class="w-full p-2 border rounded">
          <option value="">-- Select service --</option>
          <?php if ($services && is_array($services)): ?>
            <?php foreach ($services as $s): ?>
              <option value="<?= e($s->service) ?>"><?= e($s->service) ?> — <?= e($s->name) ?> (min:<?= e($s->min) ?>)</option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <div>
        <label class="text-sm block mb-1">Link / Username</label>
        <input name="link" class="w-full p-2 border rounded" placeholder="Post link or username" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm block mb-1">Quantity</label>
          <input name="quantity" class="w-full p-2 border rounded" placeholder="e.g. 100" />
        </div>
        <div>
          <label class="text-sm block mb-1">Runs (optional)</label>
          <input name="runs" class="w-full p-2 border rounded" placeholder="drip runs" />
        </div>
      </div>

      <div>
        <label class="text-sm block mb-1">Interval (minutes, optional)</label>
        <input name="interval" class="w-full p-2 border rounded" placeholder="minutes between runs" />
      </div>

      <div>
        <label class="text-sm block mb-1">Custom Comments (optional) — one per line</label>
        <textarea name="comments" class="w-full p-2 border rounded" rows="4" placeholder="good pic\ngreat shot\n..."></textarea>
      </div>

      <div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Place Order</button>
      </div>
    </form>
  </div>

  <!-- Check status & Refill / Cancel -->
  <div class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-3">Check Order Status</h2>
    <form method="post" class="space-y-3">
      <input type="hidden" name="action" value="check_status">
      <div>
        <label class="text-sm block mb-1">Order ID</label>
        <input name="order_id" class="w-full p-2 border rounded" placeholder="Order ID to check" />
      </div>
      <div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Check</button>
      </div>
    </form>

    <?php if (!empty($_SESSION['last_status'])): ?>
      <div class="mt-4 bg-gray-50 p-3 rounded">
        <pre class="text-xs font-mono"><?= e(print_r($_SESSION['last_status'], true)) ?></pre>
      </div>
    <?php endif; ?>

    <hr class="my-4">

    <h3 class="font-semibold mb-2">Refill</h3>
    <form method="post" class="space-y-2">
      <input type="hidden" name="action" value="refill">
      <input name="refill_order" class="w-full p-2 border rounded" placeholder="Order ID to refill" />
      <div>
        <button class="px-3 py-1 bg-yellow-500 text-white rounded">Request Refill</button>
      </div>
    </form>

    <hr class="my-4">

    <h3 class="font-semibold mb-2">Cancel Orders</h3>
    <form method="post" class="space-y-2">
      <input type="hidden" name="action" value="cancel">
      <input name="cancel_orders" class="w-full p-2 border rounded" placeholder="Comma separated order IDs" />
      <div>
        <button class="px-3 py-1 bg-red-500 text-white rounded">Cancel</button>
      </div>
    </form>
  </div>

</div>

<footer class="mt-8 text-sm text-gray-600">
  Built with TNT SMM API • Keep your API key secret in production.
</footer>

  </div>
</body>
</html>