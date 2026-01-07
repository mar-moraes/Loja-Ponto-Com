document.addEventListener('DOMContentLoaded', function () {
    const bell = document.getElementById('notification-bell');
    const badge = document.getElementById('notification-badge');
    const dropdown = document.getElementById('notification-dropdown');
    const list = document.getElementById('notification-list');
    const markAllBtn = document.getElementById('mark-all-read');

    if (!bell) return; // Not logged in or element not found

    function loadNotifications() {
        // Use relative path assuming we are in root or subfolder calling api
        // Current structure: src/index.php. JS is in assets/js.
        // API is src/api/notifications.php.
        // If loaded from src/index.php, path to api is 'api/notifications.php'.

        // Revert to relative path which works for files in src/
        fetch('api/notifications.php?action=poll')
            .then(response => response.json())
            .then(data => {
                if (data.error) return;

                // Update badge
                if (data.count > 0) {
                    badge.style.display = 'block';
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                } else {
                    badge.style.display = 'none';
                }

                // Update list
                renderList(data.notifications);
            })
            .catch(err => console.error('Error loading notifications:', err));
    }

    function renderList(notifications) {
        if (!notifications || notifications.length === 0) {
            list.innerHTML = '<div class="notification-item" style="cursor: default;">Nenhuma notificação.</div>';
            return;
        }

        list.innerHTML = notifications.map(n => `
            <div class="notification-item ${n.lida == 0 ? 'unread' : ''}" onclick="window.markAsRead(${n.id}, '${n.link || ''}')">
                <div class="notification-text" title="${n.mensagem}">${n.mensagem}</div>
                <div style="font-size: 11px; color: #888; margin-top: 4px;">${new Date(n.data_criacao).toLocaleString('pt-BR')}</div>
            </div>
        `).join('');
    }

    window.markAsRead = function (id, link) {
        fetch('api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_read&id=' + id
        }).then(() => {
            loadNotifications(); // Reload to update count/status
            if (link && link !== 'null' && link !== '') {
                window.location.href = link;
            }
        });
    };

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fetch('api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=mark_all_read'
            }).then(() => {
                loadNotifications();
            });
        });
    }

    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Poll every 5 seconds (more responsive)
    setInterval(loadNotifications, 5000);

    // Initial load
    loadNotifications();
});
