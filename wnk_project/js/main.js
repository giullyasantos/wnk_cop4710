// WNK - Main JavaScript

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    
    // Registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
        
        // Show/hide fields based on user type
        const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
        userTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                toggleFieldsByUserType(this.value);
            });
        });
        
        // Initialize on page load
        const checkedRadio = document.querySelector('input[name="user_type"]:checked');
        if (checkedRadio) {
            toggleFieldsByUserType(checkedRadio.value);
        }
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Toggle form fields based on user type
function toggleFieldsByUserType(userType) {
    const phoneGroup = document.getElementById('phone_group');
    const creditCardGroup = document.getElementById('credit_card_group');
    const restaurantGroup = document.getElementById('restaurant_group');
    
    // Reset all
    if (phoneGroup) phoneGroup.style.display = 'none';
    if (creditCardGroup) creditCardGroup.style.display = 'none';
    if (restaurantGroup) restaurantGroup.style.display = 'none';
    
    // Show based on type
    switch(userType) {
        case 'restaurant':
            if (phoneGroup) phoneGroup.style.display = 'block';
            if (restaurantGroup) restaurantGroup.style.display = 'block';
            setRequired('phone_number', true);
            setRequired('credit_card_number', false);
            break;
            
        case 'customer':
        case 'donner':
            if (phoneGroup) phoneGroup.style.display = 'block';
            if (creditCardGroup) creditCardGroup.style.display = 'block';
            setRequired('phone_number', true);
            setRequired('credit_card_number', true);
            break;
            
        case 'needy':
            if (phoneGroup) phoneGroup.style.display = 'block';
            setRequired('phone_number', false);
            setRequired('credit_card_number', false);
            break;
            
        case 'admin':
            setRequired('phone_number', false);
            setRequired('credit_card_number', false);
            break;
    }
}

// Set field as required or optional
function setRequired(fieldId, required) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.required = required;
        const label = document.querySelector(`label[for="${fieldId}"]`);
        if (label) {
            if (required && !label.textContent.includes('*')) {
                label.textContent += ' *';
            } else if (!required) {
                label.textContent = label.textContent.replace(' *', '');
            }
        }
    }
}

// Confirm delete action
function confirmDelete(itemName) {
    return confirm(`Are you sure you want to delete "${itemName}"?`);
}

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

// Validate credit card format (basic)
function validateCreditCard(cardNumber) {
    // Remove spaces and dashes
    cardNumber = cardNumber.replace(/[\s-]/g, '');
    
    // Check if it's all digits and 13-19 characters
    if (!/^\d{13,19}$/.test(cardNumber)) {
        return false;
    }
    
    return true;
}

// Validate expiry date
function validateExpiry(expiry) {
    // Format: MM/YYYY
    const regex = /^(0[1-9]|1[0-2])\/20\d{2}$/;
    return regex.test(expiry);
}
