<?php
// Get the phone number from the form data
$phone = $_POST['phone'];

// Use the WhatsApp API to get the QR code for the phone number
$url = "https://api.chat-api.com/instanceXXXXX/login?phone={$phone}";
$response = file_get_contents($url);
$data = json_decode($response);

// Get the WebSocket URL from the response data
$webSocketURL = $data->webhookUrl;

// Open a WebSocket connection to the WhatsApp server
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, 'webhook.chat-api.com', 443);

// Send the login command to the WebSocket
$message = array(
    'command' => 'login',
    'phone' => $phone
);
socket_write($socket, json_encode($message)."\n");

// Receive messages from the WebSocket
while ($data = socket_read($socket, 2048)) {
    $messages = explode("\n", $data);
    foreach ($messages as $message) {
        if (!empty($message)) {
            $messageData = json_decode($message);

            // Check if the message is a link to a WhatsApp group
            if (strpos($messageData->body, 'chat.whatsapp.com/') !== false) {
                $groupLink = preg_match('/https:\/\/chat.whatsapp.com\/\S+/', $messageData->body, $matches);
                if ($groupLink) {
                    // Extract the group ID from the link
                    $groupID = explode('/', $matches[0])[3];

                    // Get the name of the group by visiting the link
                    $url = "https://chat.whatsapp.com/{$groupID}";
                    $html = file_get_contents($url);

                    // Extract the group name from the HTML using a regular expression
                    $groupName = preg_match('/<title>(.+?)<\/title>/', $html, $matches) ? $matches[1] : '';

                    // Output the group link and name
                    echo "{$matches[0]} {$groupName}<br>";
                }
            }
        }
    }
}
