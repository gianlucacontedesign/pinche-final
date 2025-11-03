/**
 * JavaScript para Sistema de Autenticación
 * Validaciones, UX mejorada y funcionalidades interactivas
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ====================
    // TOGGLE PASSWORD VISIBILITY
    // ====================
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const targetInput = document.getElementById(targetId);
            
            if (targetInput) {
                const currentType = targetInput.getAttribute('type');
                const newType = currentType === 'password' ? 'text' : 'password';
                targetInput.setAttribute('type', newType);
                
                // Cambiar ícono
                const eyeIcon = this.querySelector('.eye-icon');
                if (eyeIcon) {
                    if (newType === 'text') {
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        `;
                    } else {
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        `;
                    }
                }
            }
        });
    });
    
    // ====================
    // PASSWORD STRENGTH METER
    // ====================
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');
    
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthUI(strength, passwordStrength);
        });
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length === 0) return null;
        if (password.length >= 6) strength += 25;
        if (password.length >= 10) strength += 25;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        if (/\d/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        if (strength < 50) return 'weak';
        if (strength < 80) return 'medium';
        return 'strong';
    }
    
    function updatePasswordStrengthUI(strength, container) {
        const strengthBar = container.querySelector('.strength-bar');
        const strengthText = container.querySelector('.strength-text');
        
        // Limpiar clases previas
        strengthBar.classList.remove('weak', 'medium', 'strong');
        
        if (!strength) {
            strengthText.textContent = '';
            return;
        }
        
        strengthBar.classList.add(strength);
        
        const messages = {
            'weak': 'Débil - Agrega más caracteres',
            'medium': 'Media - Buen comienzo',
            'strong': 'Fuerte - ¡Excelente!'
        };
        
        strengthText.textContent = messages[strength] || '';
    }
    
    // ====================
    // CONFIRM PASSWORD MATCH
    // ====================
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatchHint = document.getElementById('passwordMatch');
    
    if (confirmPasswordInput && passwordInput && passwordMatchHint) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatchHint.textContent = '';
                passwordMatchHint.style.color = '';
                return;
            }
            
            if (password === confirmPassword) {
                passwordMatchHint.textContent = '✓ Las contraseñas coinciden';
                passwordMatchHint.style.color = '#10b981';
            } else {
                passwordMatchHint.textContent = '✗ Las contraseñas no coinciden';
                passwordMatchHint.style.color = '#dc2626';
            }
        }
    }
    
    // ====================
    // EMAIL AVAILABILITY CHECK (AJAX)
    // ====================
    const emailInput = document.getElementById('email');
    const emailAvailability = document.getElementById('emailAvailability');
    let emailCheckTimeout;
    
    if (emailInput && emailAvailability && window.location.pathname.includes('register')) {
        emailInput.addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value;
            
            // Validar formato de email
            if (!isValidEmail(email)) {
                emailAvailability.textContent = '';
                return;
            }
            
            // Debounce: esperar 500ms después de que el usuario deje de escribir
            emailCheckTimeout = setTimeout(() => {
                checkEmailAvailability(email, emailAvailability);
            }, 500);
        });
    }
    
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function checkEmailAvailability(email, container) {
        // Mostrar loading
        container.textContent = 'Verificando disponibilidad...';
        container.style.color = '#6b7280';
        
        // Hacer petición AJAX
        fetch(window.location.origin + '/ajax/check-email.php?email=' + encodeURIComponent(email))
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    container.textContent = '✓ Email disponible';
                    container.style.color = '#10b981';
                } else {
                    container.textContent = '✗ Este email ya está registrado';
                    container.style.color = '#dc2626';
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
                container.textContent = '';
            });
    }
    
    // ====================
    // COLLAPSIBLE SECTIONS
    // ====================
    const collapseToggles = document.querySelectorAll('.collapse-toggle');
    
    collapseToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const targetContent = document.getElementById(targetId);
            
            if (targetContent) {
                const isVisible = targetContent.style.display !== 'none';
                
                if (isVisible) {
                    targetContent.style.display = 'none';
                    this.classList.remove('active');
                } else {
                    targetContent.style.display = 'block';
                    this.classList.add('active');
                }
            }
        });
    });
    
    // ====================
    // FORM VALIDATION MESSAGES
    // ====================
    const forms = document.querySelectorAll('.auth-form, .account-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            // Validar que las contraseñas coincidan en formularios de registro
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            
            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    isValid = false;
                    
                    // Mostrar mensaje de error
                    showFormError(form, 'Las contraseñas no coinciden');
                    return false;
                }
            }
            
            // Validar longitud mínima de contraseña
            if (password && password.value.length > 0 && password.value.length < 6) {
                e.preventDefault();
                isValid = false;
                showFormError(form, 'La contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
    });
    
    function showFormError(form, message) {
        // Buscar si ya existe un alert
        let alert = form.querySelector('.alert-error');
        
        if (!alert) {
            alert = document.createElement('div');
            alert.className = 'alert alert-error';
            alert.innerHTML = `
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="alert-message"></span>
            `;
            form.insertBefore(alert, form.firstChild);
        }
        
        alert.querySelector('.alert-message').textContent = message;
        
        // Scroll al alert
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // ====================
    // AUTO-DISMISS ALERTS
    // ====================
    const alerts = document.querySelectorAll('.alert-success');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000); // Desaparecer después de 5 segundos
    });
    
    // ====================
    // ACCOUNT DROPDOWN
    // ====================
    const accountDropdown = document.querySelector('.account-dropdown');
    
    if (accountDropdown) {
        // Click fuera del dropdown para cerrarlo
        document.addEventListener('click', function(e) {
            if (!accountDropdown.contains(e.target)) {
                accountDropdown.classList.remove('active');
            }
        });
        
        // Toggle en móvil
        const accountBtn = accountDropdown.querySelector('.account-btn');
        if (accountBtn && window.innerWidth <= 768) {
            accountBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                accountDropdown.classList.toggle('active');
            });
        }
    }
    
    // ====================
    // SMOOTH SCROLLING
    // ====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ====================
    // FORM INPUT ANIMATIONS
    // ====================
    const formInputs = document.querySelectorAll('.form-control');
    
    formInputs.forEach(input => {
        // Focus effect
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            
            // Validación básica al perder el foco
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        
        // Clear error on input
        input.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });
    
    // ====================
    // PHONE NUMBER FORMATTING (OPCIONAL)
    // ====================
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Formato: +54 11 1234-5678
            if (value.length > 2) {
                value = value.substring(0, 2) + ' ' + value.substring(2);
            }
            if (value.length > 5) {
                value = value.substring(0, 5) + ' ' + value.substring(5);
            }
            if (value.length > 10) {
                value = value.substring(0, 10) + '-' + value.substring(10, 14);
            }
            
            e.target.value = '+' + value;
        });
    }
    
    // ====================
    // LOADING STATES
    // ====================
    const submitButtons = document.querySelectorAll('form button[type="submit"]');
    
    submitButtons.forEach(button => {
        button.closest('form').addEventListener('submit', function() {
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Procesando...';
            
            // Restaurar después de 5 segundos (por si falla)
            setTimeout(() => {
                button.disabled = false;
                button.textContent = originalText;
            }, 5000);
        });
    });
    
    console.log('✓ Sistema de autenticación inicializado');
});
