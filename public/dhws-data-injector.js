// dhws-data-injector.js
// Dynamic data injector for user dashboard

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in and fetch user data
    fetchUserData();

    async function fetchUserData() {
        try {
            const response = await fetch('../backend/auth.php?action=check');
            const data = await response.json();

            if (data.success) {
                const user = data.user;
                // Fetch additional user profile data
                await fetchUserProfile(user);
                // Fetch user statistics
                await fetchUserStats(user.id);
                // Fetch user reports
                await fetchUserReports(user.id);
                // Fetch user activities
                await fetchUserActivities(user.id);
                // Fetch notifications
                await fetchNotifications(user.id);
                // Fetch achievements
                await fetchAchievements(user.id);
            } else {
                // Redirect to login if not authenticated
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Error fetching user data:', error);
        }
    }

    async function fetchUserProfile(user) {
        try {
            // Fetch profile data based on user level
            let profileData = {};
            if (user.level === 'superadmin') {
                const response = await fetch(`../backend/get_profile.php?user_id=${user.id}&level=superadmin`);
                if (response.ok) {
                    profileData = await response.json();
                }
            } else if (user.level === 'admin') {
                const response = await fetch(`../backend/get_profile.php?user_id=${user.id}&level=admin`);
                if (response.ok) {
                    profileData = await response.json();
                }
            } else if (user.level === 'anggota') {
                const response = await fetch(`../backend/get_profile.php?user_id=${user.id}&level=anggota`);
                if (response.ok) {
                    profileData = await response.json();
                }
            }

            // Update user name and email in navigation
            updateUserProfile(user, profileData);
        } catch (error) {
            console.error('Error fetching user profile:', error);
        }
    }

    function updateUserProfile(user, profileData) {
        // Update welcome message
        const welcomeElement = document.querySelector('h1');
        if (welcomeElement && profileData.nama_lengkap) {
            welcomeElement.innerHTML = `Selamat Datang, ${profileData.nama_lengkap}! 👋`;
        } else if (welcomeElement) {
            welcomeElement.innerHTML = `Selamat Datang, ${user.username}! 👋`;
        }

        // Update user dropdown in navigation
        const userNameElements = document.querySelectorAll('.user-name');
        const userEmailElements = document.querySelectorAll('.user-email');
        const userAvatarElements = document.querySelectorAll('.user-avatar');

        userNameElements.forEach(el => {
            el.textContent = profileData.nama_lengkap || user.username;
        });

        userEmailElements.forEach(el => {
            el.textContent = user.email;
        });

        if (profileData.foto_profil) {
            userAvatarElements.forEach(el => {
                el.src = profileData.foto_profil;
            });
        }
    }

    async function fetchUserStats(userId) {
        try {
            const response = await fetch(`../backend/get_user_stats.php?user_id=${userId}`);
            if (response.ok) {
                const stats = await response.json();
                updateUserStats(stats);
            }
        } catch (error) {
            console.error('Error fetching user stats:', error);
        }
    }

    function updateUserStats(stats) {
        // Update quick stats cards
        const totalReportsEl = document.querySelector('.stat-total-reports');
        const resolvedReportsEl = document.querySelector('.stat-resolved-reports');
        const pendingReportsEl = document.querySelector('.stat-pending-reports');
        const pointsEl = document.querySelector('.stat-points');

        if (totalReportsEl) totalReportsEl.textContent = stats.total_reports || 0;
        if (resolvedReportsEl) resolvedReportsEl.textContent = stats.resolved_reports || 0;
        if (pendingReportsEl) pendingReportsEl.textContent = stats.pending_reports || 0;
        if (pointsEl) pointsEl.textContent = stats.points || 0;
    }

    async function fetchUserReports(userId) {
        try {
            const response = await fetch(`../backend/get_user_reports.php?user_id=${userId}`);
            if (response.ok) {
                const reports = await response.json();
                updateUserReports(reports);
            }
        } catch (error) {
            console.error('Error fetching user reports:', error);
        }
    }

    function updateUserReports(reports) {
        const reportsContainer = document.querySelector('.reports-list');
        if (!reportsContainer || !reports.length) return;

        const reportsHtml = reports.map(report => `
            <div class="border border-border-light rounded-xl p-4 hover:shadow-md transition-base">
                <div class="flex gap-4">
                    <img src="${report.foto || 'https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'}"
                         alt="Foto laporan ${report.judul}"
                         class="w-24 h-24 rounded-lg object-cover flex-shrink-0"
                         loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-headline font-semibold text-text-primary">${report.judul || report.kategori}</h3>
                            <span class="badge badge-${getStatusClass(report.status)} flex-shrink-0">${report.status}</span>
                        </div>
                        <p class="text-sm text-text-secondary mb-3">${report.alamat || 'Alamat tidak tersedia'}</p>
                        <div class="flex items-center gap-4 text-xs text-text-secondary">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>${formatDate(report.created_at)}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span>LPR-${report.id.toString().padStart(3, '0')}</span>
                            </div>
                        </div>
                        ${report.status === 'Diproses' ? `
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-text-secondary">Progress Penanganan</span>
                                <span class="text-warning font-medium">${report.progress || 0}%</span>
                            </div>
                            <div class="w-full bg-surface rounded-full h-2">
                                <div class="bg-warning h-2 rounded-full" style="width: ${report.progress || 0}%"></div>
                            </div>
                        </div>
                        ` : ''}
                        <div class="mt-3 flex items-center gap-2">
                            <button class="text-sm text-primary hover:text-primary-700 font-medium transition-base">
                                Lihat Detail
                            </button>
                            <span class="text-text-tertiary">•</span>
                            <button class="text-sm text-text-secondary hover:text-text-primary transition-base">
                                Unduh Bukti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        reportsContainer.innerHTML = reportsHtml;
    }

    async function fetchUserActivities(userId) {
        try {
            const response = await fetch(`../backend/get_user_activities.php?user_id=${userId}`);
            if (response.ok) {
                const activities = await response.json();
                updateUserActivities(activities);
            }
        } catch (error) {
            console.error('Error fetching user activities:', error);
        }
    }

    function updateUserActivities(activities) {
        const activitiesContainer = document.querySelector('.activities-timeline');
        if (!activitiesContainer || !activities.length) return;

        const activitiesHtml = activities.map(activity => `
            <div class="flex gap-4">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 bg-${getActivityColor(activity.type)}-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-${getActivityColor(activity.type)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${getActivityIcon(activity.type)}"/>
                        </svg>
                    </div>
                    <div class="w-0.5 h-full bg-border-light mt-2"></div>
                </div>
                <div class="flex-1 pb-6">
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="font-medium text-text-primary">${activity.title}</h3>
                        <span class="text-xs text-text-secondary">${formatTimeAgo(activity.created_at)}</span>
                    </div>
                    <p class="text-sm text-text-secondary mb-2">${activity.description}</p>
                    ${activity.reference_id ? `<span class="text-xs text-${getActivityColor(activity.type)} font-medium">${activity.reference}</span>` : ''}
                </div>
            </div>
        `).join('');

        activitiesContainer.innerHTML = activitiesHtml;
    }

    async function fetchNotifications(userId) {
        try {
            const response = await fetch(`../backend/get_notifications.php?user_id=${userId}`);
            if (response.ok) {
                const notifications = await response.json();
                updateNotifications(notifications);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    function updateNotifications(notifications) {
        const notificationsContainer = document.querySelector('.notifications-list');
        if (!notificationsContainer) return;

        const notificationsHtml = notifications.map(notification => `
            <div class="p-3 bg-${getNotificationColor(notification.type)}-50 border border-${getNotificationColor(notification.type)}-200 rounded-lg">
                <div class="flex items-start gap-2 mb-1">
                    <svg class="w-5 h-5 text-${getNotificationColor(notification.type)} flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="${getNotificationIcon(notification.type)}" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">${notification.title}</p>
                        <p class="text-xs text-text-secondary mt-1">${notification.message}</p>
                        <p class="text-xs text-${getNotificationColor(notification.type)} mt-1">${formatTimeAgo(notification.created_at)}</p>
                    </div>
                </div>
            </div>
        `).join('');

        notificationsContainer.innerHTML = notificationsHtml;

        // Update notification count
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            const unreadCount = notifications.filter(n => !n.is_read).length;
            notificationBadge.textContent = unreadCount > 0 ? unreadCount : '';
            notificationBadge.style.display = unreadCount > 0 ? 'inline' : 'none';
        }
    }

    async function fetchAchievements(userId) {
        try {
            const response = await fetch(`../backend/get_user_achievements.php?user_id=${userId}`);
            if (response.ok) {
                const achievements = await response.json();
                updateAchievements(achievements);
            }
        } catch (error) {
            console.error('Error fetching achievements:', error);
        }
    }

    function updateAchievements(achievements) {
        const achievementsContainer = document.querySelector('.achievements-grid');
        if (!achievementsContainer) return;

        const achievementsHtml = achievements.map(achievement => `
            <div class="text-center p-4 ${achievement.unlocked ? 'bg-surface' : 'bg-surface opacity-50'} rounded-lg">
                <div class="w-16 h-16 ${achievement.unlocked ? 'bg-primary-100' : 'bg-border-light'} rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-8 h-8 ${achievement.unlocked ? 'text-primary' : 'text-text-tertiary'}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="${achievement.icon}"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-text-primary">${achievement.name}</p>
            </div>
        `).join('');

        achievementsContainer.innerHTML = achievementsHtml;
    }

    // Helper functions
    function getStatusClass(status) {
        const statusMap = {
            'Selesai': 'success',
            'Diproses': 'warning',
            'Menunggu': 'secondary',
            'Ditolak': 'danger'
        };
        return statusMap[status] || 'secondary';
    }

    function getActivityColor(type) {
        const colorMap = {
            'report_created': 'primary',
            'status_update': 'warning',
            'badge_earned': 'accent',
            'report_resolved': 'secondary'
        };
        return colorMap[type] || 'primary';
    }

    function getActivityIcon(type) {
        const iconMap = {
            'report_created': 'M12 4v16m8-8H4',
            'status_update': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'badge_earned': 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            'report_resolved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        };
        return iconMap[type] || 'M12 4v16m8-8H4';
    }

    function getNotificationColor(type) {
        const colorMap = {
            'success': 'secondary',
            'warning': 'warning',
            'info': 'primary',
            'error': 'danger'
        };
        return colorMap[type] || 'primary';
    }

    function getNotificationIcon(type) {
        const iconMap = {
            'success': 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'warning': 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
            'info': 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
            'error': 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z'
        };
        return iconMap[type] || 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }

    function formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

        if (diffInHours < 1) return 'Baru saja';
        if (diffInHours < 24) return `${diffInHours} jam lalu`;
        if (diffInHours < 48) return '1 hari lalu';
        return `${Math.floor(diffInHours / 24)} hari lalu`;
    }
});