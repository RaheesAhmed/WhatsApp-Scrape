$(document).ready(function() {
    var socket;
  
    $('#login').click(function() {
      // Get the phone number from the form
      var phone = $('#phone').val();
  
      // Connect to the WebSocket
      socket = new WebSocket('wss://webhook.chat-api.com:443');
  
      socket.onopen = function() {
        // Send the login command to the WebSocket
        var message = {
          'command': 'login',
          'phone': phone
        };
        socket.send(JSON.stringify(message));
      };
  
      socket.onmessage = function(event) {
        var response = JSON.parse(event.data);
        if (response.event === 'qr_code') {
          // Display the QR code on the page
          var qrCode = response.qr_code;
          $('#qr-code').attr('src', qrCode);
        } else if (response.event === 'message') {
          // Check if the message is a link to a WhatsApp group
          if (response.body.indexOf('chat.whatsapp.com/') !== -1) {
            var groupLink = response.body.match(/https:\/\/chat.whatsapp.com\/\S+/);
            if (groupLink) {
              // Extract the group ID from the link
              var groupID = groupLink[0].split('/')[3];
  
              // Get the name of the group by visiting the link
              var url = 'https://chat.whatsapp.com/' + groupID;
              $.get(url, function(data) {
                // Extract the group name from the HTML using a regular expression
                var groupName = $(data).find('title').text().replace(/ |WhatsApp/g, '');
  
                // Add the link and group name to the table
                var row = '<tr><td>' + groupName + '</td><td>' + groupLink[0] + '</td></tr>';
                $('#links tbody').append(row);
              });
            }
          }
        }
      };
    });
  });
  