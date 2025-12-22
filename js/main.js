$(document).ready(function() {
    
    // ========================================
    // NAVBAR SCROLL EFFECT
    // ========================================
    $(window).on('scroll', function() {
        const nav = $('nav');
        if ($(this).scrollTop() > 50) {
            nav.addClass('scrolled');
        } else {
            nav.removeClass('scrolled');
        }
    });

    // ========================================
    // IMAGE PREVIEW WITH ANIMATION
    // ========================================
    function readURL(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(target).attr('src', e.target.result).hide().fadeIn(400);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#logoInput, #photoInput").change(function() {
        readURL(this, '#imgPreview');
    });
    
    $("#photoInput").change(function() { readURL(this, '#photoPreview'); });
    $("#passportInput").change(function() { readURL(this, '#passportPreview'); });

    // ========================================
    // DYNAMIC CITIES IN ADD FLIGHT
    // ========================================
    $("#addCityBtn").click(function() {
        const newInput = $('<input type="text" name="cities[]" placeholder="Stopover City" required>');
        newInput.hide();
        $("#itineraryContainer").append(newInput);
        newInput.slideDown(300);
    });

    // ========================================
    // FORM VALIDATION WITH GLOW EFFECT
    // ========================================
    $("form").submit(function(e) {
        let isValid = true;
        $(this).find('input[required]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).css({
                    'border-color': '#ef4444',
                    'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.3), 0 0 20px rgba(239, 68, 68, 0.3)'
                });
            } else {
                $(this).css({
                    'border-color': '',
                    'box-shadow': ''
                });
            }
        });

        if (!isValid) {
            e.preventDefault();
            showNotification('Please fill in all required fields.', 'error');
        }
    });

    // ========================================
    // INPUT FOCUS GLOW
    // ========================================
    $('input, textarea, select').on('focus', function() {
        $(this).css('box-shadow', '0 0 0 3px rgba(59, 130, 246, 0.3), 0 0 20px rgba(59, 130, 246, 0.3)');
    }).on('blur', function() {
        $(this).css('box-shadow', '');
    });

    // ========================================
    // CARD HOVER GLOW EFFECT
    // ========================================
    $('.card').hover(
        function() {
            $(this).css('box-shadow', '0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(59, 130, 246, 0.3)');
        },
        function() {
            $(this).css('box-shadow', '');
        }
    );

    // ========================================
    // TABLE ROW HOVER
    // ========================================
    $('tbody tr[onclick]').css('cursor', 'pointer');

    // ========================================
    // NOTIFICATION SYSTEM
    // ========================================
    window.showNotification = function(message, type = 'info') {
        const colors = {
            success: 'linear-gradient(135deg, #10b981, #059669)',
            error: 'linear-gradient(135deg, #ef4444, #dc2626)',
            warning: 'linear-gradient(135deg, #f59e0b, #d97706)',
            info: 'linear-gradient(135deg, #3b82f6, #8b5cf6)'
        };
        
        const notification = $(`
            <div class="notification" style="
                position: fixed;
                top: 100px;
                right: 20px;
                padding: 16px 24px;
                background: ${colors[type]};
                color: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.4);
                z-index: 9999;
                animation: slideInRight 0.4s ease;
                font-weight: 500;
                backdrop-filter: blur(10px);
            ">${message}</div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.css('animation', 'slideOutRight 0.4s ease');
            setTimeout(() => notification.remove(), 400);
        }, 3000);
    };

    // ========================================
    // CHAT AUTO-SCROLL
    // ========================================
    const chatMessages = $('.chat-messages');
    if (chatMessages.length) {
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    // ========================================
    // CONFIRM DANGEROUS ACTIONS
    // ========================================
    $('button[name="cancel_flight"], button[name="cancel_booking"]').on('click', function(e) {
        if (!confirm('Are you sure you want to cancel? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // ========================================
    // SMOOTH PAGE TRANSITIONS
    // ========================================
    $('a').not('[href^="#"]').not('[href^="javascript"]').on('click', function(e) {
        const href = $(this).attr('href');
        if (href && !href.startsWith('mailto')) {
            e.preventDefault();
            $('body').css({
                'opacity': '0',
                'transform': 'translateY(-10px)'
            });
            setTimeout(() => {
                window.location = href;
            }, 200);
        }
    });

    // ========================================
    // ENTRANCE ANIMATION
    // ========================================
    $('body').css({
        'opacity': '0',
        'transform': 'translateY(10px)'
    }).animate({
        opacity: 1
    }, {
        duration: 400,
        step: function() {
            $(this).css('transform', 'translateY(0)');
        }
    });

    // ========================================
    // PARALLAX EFFECT ON SCROLL
    // ========================================
    $(window).on('scroll', function() {
        const scrolled = $(this).scrollTop();
        $('.hero h1, .hero p').css('transform', `translateY(${scrolled * 0.3}px)`);
    });

    // ========================================
    // BUTTON RIPPLE EFFECT
    // ========================================
    $('button, .btn').on('click', function(e) {
        const btn = $(this);
        const ripple = $('<span class="ripple-effect"></span>');
        const x = e.pageX - btn.offset().left;
        const y = e.pageY - btn.offset().top;
        
        ripple.css({
            left: x + 'px',
            top: y + 'px',
            position: 'absolute',
            borderRadius: '50%',
            background: 'rgba(255,255,255,0.4)',
            transform: 'scale(0)',
            animation: 'ripple 0.6s linear',
            pointerEvents: 'none'
        });
        
        btn.css('position', 'relative').css('overflow', 'hidden').append(ripple);
        setTimeout(() => ripple.remove(), 600);
    });

});

// ========================================
// CSS ANIMATIONS
// ========================================
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes ripple {
        to { transform: scale(4); opacity: 0; }
    }
    
    body {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
`;
document.head.appendChild(styleSheet);
