$(document).ready(function() {
    // Handle signup form submission
    $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        var username = $('#username').val().trim();
        var email = $('#email').val().trim();
        var password = $('#password').val();
        var confirmPassword = $('#confirmPassword').val();
        
        // Validate form
        if (username === '' || email === '' || password === '' || confirmPassword === '') {
            showMessage('All fields are required!', 'danger');
            return;
        }
        
        if (password !== confirmPassword) {
            showMessage('Passwords do not match!', 'danger');
            return;
        }
        
        if (password.length < 6) {
            showMessage('Password must be at least 6 characters long!', 'danger');
            return;
        }
        
        // Disable submit button
        $('button[type="submit"]').prop('disabled', true).text('Signing up...');
        
        // Send AJAX request
        $.ajax({
            url: '/api/signup',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                email: email,
                password: password
            }),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 1500);
                } else {
                    showMessage(response.message, 'danger');
                    $('button[type="submit"]').prop('disabled', false).text('Sign Up');
                }
            },
            error: function(xhr, status, error) {
                showMessage('An error occurred. Please try again.', 'danger');
                $('button[type="submit"]').prop('disabled', false).text('Sign Up');
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
