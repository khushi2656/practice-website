$(document).ready(function() {
    // Check if user is already logged in
    var sessionToken = localStorage.getItem('sessionToken');
    if (sessionToken) {
        window.location.href = 'profile.html';
    }
    
    // Handle login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        var email = $('#email').val().trim();
        var password = $('#password').val();
        
        // Validate form
        if (email === '' || password === '') {
            showMessage('All fields are required!', 'danger');
            return;
        }
        
        // Disable submit button
        $('button[type="submit"]').prop('disabled', true).text('Logging in...');
        
        // Send AJAX request
        $.ajax({
            url: '/api/login',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({
                email: email,
                password: password
            }),
            success: function(response) {
                if (response.success) {
                    // Store session token and user info in localStorage
                    localStorage.setItem('sessionToken', response.sessionToken);
                    localStorage.setItem('userId', response.userId);
                    localStorage.setItem('username', response.username);
                    localStorage.setItem('email', response.email);
                    
                    showMessage(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                    $('button[type="submit"]').prop('disabled', false).text('Login');
                }
            },
            error: function(xhr, status, error) {
                showMessage('An error occurred. Please try again.', 'danger');
                $('button[type="submit"]').prop('disabled', false).text('Login');
            }
        });
    });
    
    // Function to display messages
    function showMessage(message, type) {
        var messageBox = $('#messageBox');
        messageBox.removeClass('d-none alert-success alert-danger');
        messageBox.addClass('alert-' + type);
        messageBox.text(message);
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            messageBox.addClass('d-none');
        }, 5000);
    }
});
