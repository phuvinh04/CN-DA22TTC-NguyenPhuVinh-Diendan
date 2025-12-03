// Main JavaScript for Forum

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Lazy load images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// Search with debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Live search
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function(e) {
        const query = e.target.value;
        if (query.length >= 3) {
            // Implement live search here
            console.log('Searching for:', query);
        }
    }, 300));
}

// Star Rating functionality
function rate(type, id, rating) {
    console.log('Rating:', type, id, rating);
    
    // Xác định base path dựa trên URL hiện tại
    let basePath = '';
    const path = window.location.pathname;
    if (path.includes('/user/') || path.includes('/admin/')) {
        basePath = '../';
    }
    
    console.log('Fetching:', basePath + 'api/vote.php');
    
    fetch(basePath + 'api/vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            id: id,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update star display
            const ratingContainer = document.querySelector(`[data-rating-id="${id}"]`);
            if (ratingContainer) {
                // Update average rating display
                const avgDisplay = ratingContainer.querySelector('.avg-rating');
                if (avgDisplay) {
                    avgDisplay.textContent = data.avgRating;
                }
                
                // Update total ratings count
                const countDisplay = ratingContainer.querySelector('.rating-count');
                if (countDisplay) {
                    countDisplay.textContent = `(${data.totalRatings} đánh giá)`;
                }
                
                // Update star visual
                updateStarDisplay(ratingContainer, data.avgRating, data.userRating);
            }
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra', 'danger');
    });
}

// Update star display based on rating
function updateStarDisplay(container, avgRating, userRating) {
    const stars = container.querySelectorAll('.star-btn');
    stars.forEach((star, index) => {
        const starValue = index + 1;
        star.classList.remove('active', 'user-rated');
        
        // Highlight stars up to average
        if (starValue <= Math.round(avgRating)) {
            star.classList.add('active');
        }
        
        // Mark user's rating
        if (userRating && starValue <= userRating) {
            star.classList.add('user-rated');
        }
    });
}

// Hover effect for stars
function initStarRating() {
    document.querySelectorAll('.star-rating').forEach(container => {
        const stars = container.querySelectorAll('.star-btn');
        
        stars.forEach((star, index) => {
            // Hover effect
            star.addEventListener('mouseenter', () => {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });
            
            star.addEventListener('mouseleave', () => {
                stars.forEach(s => s.classList.remove('hover'));
            });
        });
    });
}

// Initialize star rating on page load
document.addEventListener('DOMContentLoaded', initStarRating);

// Copy code to clipboard
document.querySelectorAll('pre code').forEach(block => {
    const button = document.createElement('button');
    button.className = 'btn btn-sm btn-outline-secondary copy-btn';
    button.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
    button.addEventListener('click', () => {
        navigator.clipboard.writeText(block.textContent);
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        }, 2000);
    });
    block.parentElement.style.position = 'relative';
    block.parentElement.appendChild(button);
});

// Mobile sidebar toggle
const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.admin-sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}

// Tooltip initialization
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

// Toast notifications
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

// Character counter for textareas
document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
    const maxLength = textarea.getAttribute('maxlength');
    const counter = document.createElement('small');
    counter.className = 'text-muted';
    counter.textContent = `0 / ${maxLength}`;
    textarea.parentElement.appendChild(counter);
    
    textarea.addEventListener('input', () => {
        const length = textarea.value.length;
        counter.textContent = `${length} / ${maxLength}`;
        if (length > maxLength * 0.9) {
            counter.classList.add('text-danger');
        } else {
            counter.classList.remove('text-danger');
        }
    });
});

console.log('Forum JS loaded successfully!');


// ============================================
// ADVANCED UI ENHANCEMENTS
// ============================================

// Back to Top Button
(function() {
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
    backToTop.setAttribute('aria-label', 'Back to top');
    document.body.appendChild(backToTop);
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();

// Count Up Animation
function animateCountUp(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

// Count-up animation is disabled to prevent display issues
// Stats numbers will display normally without animation

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K for search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modal = document.querySelector('.modal.show');
        if (modal) {
            bootstrap.Modal.getInstance(modal)?.hide();
        }
    }
});

// Image Error Handler
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
        if (this.classList.contains('user-avatar') || 
            this.classList.contains('user-avatar-sm') || 
            this.classList.contains('user-avatar-lg')) {
            this.src = 'https://ui-avatars.com/api/?name=User&background=667eea&color=fff';
        }
    });
});

// Reading Progress Bar
(function() {
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    progressBar.innerHTML = '<div class="reading-progress-bar"></div>';
    document.body.prepend(progressBar);
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: rgba(0,0,0,0.1);
            z-index: 9999;
        }
        .reading-progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.1s ease;
        }
    `;
    document.head.appendChild(style);
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        progressBar.querySelector('.reading-progress-bar').style.width = `${progress}%`;
    });
})();

// Enhanced Form Validation Feedback
document.querySelectorAll('form').forEach(form => {
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (this.value) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
});

// Auto-resize Textareas
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.style.overflow = 'hidden';
    textarea.style.resize = 'none';
    
    function autoResize() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    }
    
    textarea.addEventListener('input', autoResize);
    autoResize.call(textarea);
});

// Confirmation Dialog Enhancement
window.confirmAction = function(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Xác nhận
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">${message}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger confirm-btn">Xác nhận</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.querySelector('.confirm-btn').addEventListener('click', () => {
        callback();
        bsModal.hide();
    });
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
};

// Loading State for Buttons
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            
            // Reset after 10 seconds (fallback)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        }
    });
});

// Notification Sound (optional)
function playNotificationSound() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleQAA');
    audio.volume = 0.3;
    audio.play().catch(() => {});
}

// Dark Mode Toggle (preparation)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

// Check saved dark mode preference
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

console.log('Advanced UI enhancements loaded!');
