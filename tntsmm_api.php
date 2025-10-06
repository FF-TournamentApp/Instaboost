<?php
class Api
{
    public $api_url = 'https://tntsmm.in/api/v2';
    public $api_key = '';

    public function order($data)
    {
        $post = array_merge(['key' => $this->api_key, 'action' => 'add'], $data);
        return json_decode($this->connect($post));
    }

    public function status($order_id)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        ]));
    }

    public function services()
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'services',
        ]));
    }

    public function balance()
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'balance',
        ]));
    }

    private function connect($post)
    {
        $ch = curl_init($this->api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
?>