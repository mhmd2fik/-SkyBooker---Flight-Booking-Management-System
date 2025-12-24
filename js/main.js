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
    // AJAX MESSAGE POLLING & SENDING
    // ========================================
    let lastMessageId = 0;
    let pollingInterval = null;
    
    // Initialize last message ID from existing messages
    function initializeLastMessageId() {
        const messages = $('.chat-messages .msg');
        if (messages.length > 0) {
            const lastMsg = messages.last();
            const msgId = lastMsg.data('msg-id');
            if (msgId) {
                lastMessageId = parseInt(msgId);
            }
        }
    }
    
    // Fetch new messages via AJAX
    function fetchNewMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const receiverId = urlParams.get('to');
        
        if (!receiverId || receiverId == '0') {
            return;
        }
        
        $.ajax({
            url: 'fetch_messages.php',
            type: 'GET',
            data: {
                to: receiverId,
                last_id: lastMessageId
            },
            dataType: 'json',
            success: function(response) {
                if (response.messages && response.messages.length > 0) {
                    const chatBox = $('.chat-messages');
                    const isAtBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 50;
                    
                    response.messages.forEach(function(msg) {
                        const userId = chatBox.data('user-id');
                        const isSent = (msg.sender_id == userId);
                        const msgClass = isSent ? 'sent' : 'received';
                        
                        const msgElement = $(`
                            <div class="msg ${msgClass}" data-msg-id="${msg.id}" style="display:none;">
                                ${escapeHtml(msg.msg)}
                            </div>
                        `);
                        
                        chatBox.append(msgElement);
                        msgElement.fadeIn(300);
                        
                        lastMessageId = msg.id;
                    });
                    
                    // Auto-scroll if user was already at bottom
                    if (isAtBottom) {
                        chatBox.animate({ scrollTop: chatBox[0].scrollHeight }, 300);
                    }
                }
            },
            error: function() {
                // Silently fail - don't annoy user with errors
            }
        });
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Send message via AJAX
    function handleMessageSubmit(e) {
        e.preventDefault();
        
        const form = $(this);
        const messageInput = form.find('input[name="txt"]');
        const receiverId = form.find('input[name="receiver_id"]').val();
        const messageText = messageInput.val().trim();
        
        if (!messageText) {
            return;
        }
        
        // Disable form during send
        form.find('button').prop('disabled', true);
        messageInput.prop('disabled', true);
        
        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: {
                receiver_id: receiverId,
                txt: messageText
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Add message to chat immediately
                    const chatBox = $('.chat-messages');
                    const msgElement = $(`
                        <div class="msg sent" data-msg-id="${response.message_id}" style="display:none;">
                            ${escapeHtml(response.msg)}
                        </div>
                    `);
                    
                    chatBox.append(msgElement);
                    msgElement.fadeIn(300);
                    chatBox.animate({ scrollTop: chatBox[0].scrollHeight }, 300);
                    
                    // Update last message ID
                    lastMessageId = response.message_id;
                    
                    // Clear input
                    messageInput.val('');
                } else {
                    showNotification('Failed to send message. Please try again.', 'error');
                }
            },
            error: function() {
                showNotification('Network error. Please check your connection.', 'error');
            },
            complete: function() {
                // Re-enable form
                form.find('button').prop('disabled', false);
                messageInput.prop('disabled', false);
                messageInput.focus();
            }
        });
    }
    
    // Initialize messaging if on messages page
    if ($('.chat-messages').length > 0) {
        initializeLastMessageId();
        
        // Attach AJAX handler to message form
        $('.chat-input, .chat-area form').on('submit', handleMessageSubmit);
        
        // Start polling every 3 seconds
        pollingInterval = setInterval(fetchNewMessages, 3000);
        
        // Stop polling when leaving page
        $(window).on('beforeunload', function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });
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
    $('a').not('[href^="#"]').not('[href^="javascript"]').not('[target="_blank"]').on('click', function(e) {
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
