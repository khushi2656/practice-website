$(document).ready(function() {
    // Check if user is logged in
    var sessionToken = localStorage.getItem('sessionToken');
    var userId = localStorage.getItem('userId');
    var username = localStorage.getItem('username');
    
    if (!sessionToken || !userId) {
        window.location.href = 'login.html';
        return;
    }
    
    // Display welcome message
    $('#welcomeUser').text('Welcome, ' + username + '!');
    
    // Load user profile data
    loadProfile();
    
    // Handle profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        var profileData = {
            sessionToken: sessionToken,
            userId: userId,
            firstName: $('#firstName').val().trim(),
            lastName: $('#lastName').val().trim(),
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim(),
            city: $('#city').val().trim(),
            state: $('#state').val().trim(),
            country: $('#country').val().trim()
        };
        
        // Disable submit button
        $('button[type="submit"]').prop('disabled', true).text('Updating...');
        
        // Send AJAX request
        $.ajax({
            url: '/api/update_profile',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            },
            data: JSON.stringify(profileData),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                } else {
                    if (response.message === 'Invalid session') {
                        localStorage.clear();
                        window.location.href = 'login.html';
                    } else {
                        showMessage(response.message, 'danger');
                    }
                }
                $('button[type="submit"]').prop('disabled', false).text('Update Profile');
            },
            error: function(xhr, status, error) {
                showMessage('An error occurred. Please try again.', 'danger');
                $('button[type="submit"]').prop('disabled', false).text('Update Profile');
            }
        });
    });
    
    // Handle logout
    $('#logoutBtn').on('click', function() {
        $.ajax({
            url: '/api/logout',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            },
            data: JSON.stringify({}),
            success: function(response) {
                localStorage.clear();
                window.location.href = 'login.html';
            },
            error: function(xhr, status, error) {
                localStorage.clear();
                window.location.href = 'login.html';
            }
        });
    });
    
    // Function to load profile data
    function loadProfile() {
        $.ajax({
            url: '/api/get_profile',
            type: 'GET',
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            },
            success: function(response) {
                if (response.success && response.profile) {
                    var profile = response.profile;
                    $('#firstName').val(profile.firstName || '');
                    $('#lastName').val(profile.lastName || '');
                    $('#age').val(profile.age || '');
                    $('#dob').val(profile.dob || '');
                    $('#contact').val(profile.contact || '');
                    $('#address').val(profile.address || '');
                    $('#city').val(profile.city || '');
                    $('#state').val(profile.state || '');
                    $('#country').val(profile.country || '');
                } else if (response.message === 'Invalid session') {
                    localStorage.clear();
                    window.location.href = 'login.html';
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading profile:', error);
            }
        });
    }
    
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
