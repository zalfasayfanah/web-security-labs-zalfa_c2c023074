// Virtual Security Lab - JavaScript Utilities

// Confirm before dangerous actions
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirm delete/clear actions
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Highlight code blocks
    const codeBlocks = document.querySelectorAll('.code-box');
    codeBlocks.forEach(block => {
        block.addEventListener('click', function() {
            // Select all text in code block
            const range = document.createRange();
            range.selectNodeContents(this);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Copy to clipboard
            try {
                document.execCommand('copy');
                showToast('Code copied to clipboard!', 'success');
            } catch (err) {
                console.error('Failed to copy:', err);
            }
            
            // Deselect
            setTimeout(() => selection.removeAllRanges(), 1000);
        });
    });

    // Animate lab cards on hover
    const labCards = document.querySelectorAll('.lab-card');
    labCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Warning for vulnerable actions
    const vulnerableForms = document.querySelectorAll('form[action*="vulnerable"]');
    vulnerableForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const isConfirmed = confirm('⚠️ WARNING: You are about to use a VULNERABLE version.\n\nThis is for educational purposes only.\n\nContinue?');
            if (!isConfirmed) {
                e.preventDefault();
                return false;
            }
        });
    });

    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                // Check dangerous extensions
                const dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'exe', 'sh', 'bat', 'js'];
                if (dangerousExts.includes(fileExt)) {
                    showToast(`⚠️ Warning: ${fileExt.toUpperCase()} file detected! This might be dangerous.`, 'warning');
                }
                
                // Show file info
                console.log(`File selected: ${fileName} (${fileSize})`);
            }
        });
    });

    // Detect XSS attempts in textareas
    const textareas = document.querySelectorAll('textarea, input[type="text"]');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            const value = this.value;
            const xssPatterns = [
                /<script[^>]*>.*?<\/script>/gi,
                /<iframe[^>]*>.*?<\/iframe>/gi,
                /javascript:/gi,
                /on\w+\s*=/gi,
                /<img[^>]*onerror/gi,
                /<svg[^>]*onload/gi
            ];
            
            let hasXSS = false;
            xssPatterns.forEach(pattern => {
                if (pattern.test(value)) {
                    hasXSS = true;
                }
            });
            
            if (hasXSS) {
                this.style.borderColor = '#e74c3c';
                this.style.backgroundColor = '#fef5f5';
                showToast('⚠️ XSS payload detected in input!', 'warning');
            } else {
                this.style.borderColor = '#ddd';
                this.style.backgroundColor = '#fff';
            }
        });
    });

    // Detect SQL injection attempts
    const sqlInputs = document.querySelectorAll('input[name="username"], input[name="password"]');
    sqlInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = this.value;
            const sqliPatterns = [
                /'\s*(OR|AND)\s*['"]?\d+['"]?\s*=\s*['"]?\d+/gi,
                /--/,
                /#/,
                /\/\*/,
                /;\s*(DROP|DELETE|INSERT|UPDATE)/gi,
                /UNION\s+SELECT/gi
            ];
            
            let hasSQLi = false;
            sqliPatterns.forEach(pattern => {
                if (pattern.test(value)) {
                    hasSQLi = true;
                }
            });
            
            if (hasSQLi) {
                this.style.borderColor = '#e74c3c';
                this.style.backgroundColor = '#fef5f5';
                showToast('⚠️ SQL Injection pattern detected!', 'warning');
            } else {
                this.style.borderColor = '#ddd';
                this.style.backgroundColor = '#fff';
            }
        });
    });

    // Detect IDOR attempts (ID manipulation in URL)
    const urlParams = new URLSearchParams(window.location.search);
    const idParam = urlParams.get('id');
    if (idParam) {
        console.log('ID parameter detected:', idParam);
        
        // Check if trying to access other user's data
        const currentUserId = document.querySelector('[data-current-user-id]');
        if (currentUserId && currentUserId.dataset.currentUserId !== idParam) {
            console.warn('⚠️ IDOR attempt: Trying to access ID', idParam, 'while logged in as', currentUserId.dataset.currentUserId);
        }
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Copy payload on click
    const payloadLinks = document.querySelectorAll('.payload-list code');
    payloadLinks.forEach(code => {
        code.style.cursor = 'pointer';
        code.title = 'Click to copy';
        
        code.addEventListener('click', function() {
            const text = this.textContent;
            copyToClipboard(text);
            showToast(`Payload copied: ${text.substring(0, 30)}...`, 'success');
        });
    });

});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : type === 'danger' ? '#e74c3c' : '#3498db'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Copy to clipboard utility
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .code-box {
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .code-box:hover {
        background: #e8e8e8;
    }
    
    .code-box:active {
        background: #d0d0d0;
    }
`;
document.head.appendChild(style);

// Log security lab info
console.log('%c🛡️ Virtual Security Lab', 'font-size: 20px; color: #667eea; font-weight: bold;');
console.log('%cEducational Purpose Only - Use Responsibly', 'color: #e74c3c; font-weight: bold;');
console.log('%cDeveloped for Keamanan Data Course', 'color: #27ae60;');