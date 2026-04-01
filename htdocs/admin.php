<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true){
    header('Location: /');
    exit;
}
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sistem Yönetimi - Hypertrophy Max Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap');
        :root { --neon-red: #ff3b3b; --dark-bg: #0b0b0b; --card-bg: #121212; --text-gray: #a1a1aa; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--dark-bg); color: white; margin: 0; padding: 0; }
        h1, h2, h3, h4, .font-teko { font-family: 'Teko', sans-serif; }
        .admin-card { background: var(--card-bg); border: 1px solid #1a1a1a; border-radius: 20px; transition: 0.3s; }
        .admin-card:hover { border-color: var(--neon-red); transform: translateY(-2px); }
        .btn-red { background: var(--neon-red); color: white; font-family: 'Teko', sans-serif; border-radius: 8px; padding: 10px 20px; border: none; cursor: pointer; text-transform: uppercase; transition: 0.3s; font-size: 1.1rem; }
        .btn-red:hover { opacity: 0.8; box-shadow: 0 0 15px rgba(255, 59, 59, 0.3); }
        .btn-outline { background: transparent; border: 1px solid #333; color: #aaa; font-family: 'Teko', sans-serif; border-radius: 8px; padding: 10px 20px; cursor: pointer; text-transform: uppercase; transition: 0.3s; font-size: 1.1rem; }
        .btn-outline:hover { border-color: #555; color: #fff; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; box-shadow: 0 0 10px #22c55e; margin-right: 6px; }
        .offline-dot { width: 8px; height: 8px; background: #4b5563; border-radius: 50%; display: inline-block; margin-right: 6px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { font-family: 'Teko', sans-serif; text-transform: uppercase; letter-spacing: 1px; color: #555; font-size: 1.2rem; text-align: left; padding: 15px; border-bottom: 1px solid #222; }
        td { padding: 15px; border-bottom: 1px solid #1a1a1a; color: #aaa; font-size: 0.9rem; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); color: white; }
    </style>
</head>
<body class="min-h-screen">

    <header class="bg-black/80 backdrop-blur-xl sticky top-0 z-50 border-b border-white/5 py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold tracking-tighter text-white uppercase italic leading-none">
                    Admin <span class="text-[#ff3b3b]">Control</span>
                </h1>
                <p class="text-[9px] text-gray-500 tracking-[0.4em] uppercase mt-1 font-medium">Sistem Yöneticisi: <?php echo $username; ?></p>
            </div>
            <div class="flex gap-3">
                <a href="/dashboard.php" class="btn-outline flex items-center gap-2 py-2 px-4 shadow-xl"><i class="fas fa-arrow-left text-xs"></i> DASHBOARD</a>
                <a href="/logout.php" class="btn-red flex items-center gap-2 py-2 px-4 shadow-xl bg-red-900/50">ÇIKIŞ</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10">
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="admin-card p-6 flex flex-col justify-center items-center">
                <i class="fas fa-users text-3xl text-blue-500 mb-2"></i>
                <h4 id="statTotalUsers" class="text-5xl font-teko leading-none">--</h4>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Toplam Kullanıcı</p>
            </div>
            <div class="admin-card p-6 flex flex-col justify-center items-center border-green-900/40">
                <i class="fas fa-signal text-3xl text-green-500 mb-2"></i>
                <h4 id="statOnlineUsers" class="text-5xl font-teko leading-none">--</h4>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Aktif (5 Dakika)</p>
            </div>
            <div class="admin-card p-6 flex flex-col justify-center items-center border-yellow-900/40">
                <i class="fas fa-clipboard-list text-3xl text-yellow-500 mb-2"></i>
                <h4 id="statTotalPlans" class="text-5xl font-teko leading-none">--</h4>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Üretilen Plan</p>
            </div>
            <div class="admin-card p-6 flex flex-col justify-center items-center border-red-900/40">
                <i class="fas fa-comments text-3xl text-red-500 mb-2"></i>
                <h4 id="statTotalMessages" class="text-5xl font-teko leading-none">--</h4>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Mesaj Sayısı</p>
            </div>
        </div>

        <!-- User Management -->
        <div class="admin-card overflow-hidden">
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 bg-white/5">
                <h3 class="text-3xl font-teko uppercase italic tracking-widest text-[#ff3b3b]">Kullanıcı Yönetimi</h3>
                <div class="relative w-full md:w-64">
                    <input type="text" id="userSearch" onkeyup="filterUsers()" placeholder="Kullanıcı Ara..." class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:border-red-500 outline-none transition-all">
                    <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 text-xs text-[#ff3b3b]"></i>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Kayıt Tarihi</th>
                            <th>Son Aktivite</th>
                            <th>Durum</th>
                            <th>Admin</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <!-- Users will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Success/Error Toast -->
    <div id="adminToast" class="fixed bottom-10 right-10 px-6 py-4 rounded-xl text-white font-teko text-2xl tracking-widest translate-y-20 transition-all duration-300 z-[100] border-l-8 hidden"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetchStats();
            fetchUsersList();
        });

        async function fetchStats() {
            try {
                const res = await fetch('admin_api.php?action=stats');
                const stats = await res.json();
                document.getElementById('statTotalUsers').innerText = stats.total_users;
                document.getElementById('statOnlineUsers').innerText = stats.online_users;
                document.getElementById('statTotalPlans').innerText = stats.total_plans;
                document.getElementById('statTotalMessages').innerText = stats.total_messages;
            } catch(e) { console.error('Stats fetch error:', e); }
        }

        let allUsers = [];
        async function fetchUsersList() {
            try {
                const res = await fetch('admin_api.php?action=list_users');
                allUsers = await res.json();
                renderUsers(allUsers);
            } catch(e) { console.error('Users list fetch error:', e); }
        }

        function renderUsers(users) {
            const body = document.getElementById('userTableBody');
            if (!users.length) {
                body.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-gray-600 font-teko text-2xl uppercase">Sonuç Bulunamadı</td></tr>';
                return;
            }
            body.innerHTML = users.map(u => `
                <tr id="user-row-${u.id}">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-600/10 border border-red-500/20 flex items-center justify-center text-red-500 font-teko text-xl font-bold">
                                ${u.username[0].toUpperCase()}
                            </div>
                            <span class="font-teko text-2xl tracking-wide">${u.username}</span>
                        </div>
                    </td>
                    <td>${new Date(u.created_at).toLocaleDateString('tr-TR')}</td>
                    <td>${new Date(u.last_activity).toLocaleString('tr-TR', {hour:'2-digit', minute:'2-digit', day:'2-digit', month:'2-digit'})}</td>
                    <td>
                        <div class="flex items-center">
                            ${u.is_online ? '<span class="online-dot"></span><span class="text-green-500 text-[10px] font-bold uppercase">Online</span>' : '<span class="offline-dot"></span><span class="text-gray-600 text-[10px] font-bold uppercase">Offline</span>'}
                        </div>
                    </td>
                    <td>
                         <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" ${u.is_admin ? 'checked' : ''} class="sr-only peer" onchange="toggleAdmin(${u.id}, this.checked)">
                            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </td>
                    <td>
                        <button onclick="confirmDelete(${u.id}, '${u.username}')" class="w-10 h-10 bg-red-600/10 border border-red-500/20 text-red-500 hover:bg-red-600 hover:text-white rounded-lg transition-all flex items-center justify-center">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function filterUsers() {
            const query = document.getElementById('userSearch').value.toLowerCase();
            const filtered = allUsers.filter(u => u.username.toLowerCase().includes(query));
            renderUsers(filtered);
        }

        async function toggleAdmin(userId, status) {
            try {
                const res = await fetch('admin_api.php?action=make_admin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, status: status ? 1 : 0 })
                });
                const data = await res.json();
                showToast(data.success ? 'Yetki güncellendi' : data.error, !data.success);
            } catch(e) { showToast('Bağlantı hatası', true); }
        }

        function confirmDelete(userId, username) {
            if (confirm(`${username} isimli kullanıcıyı ve tüm verilerini (planlar, mesajlar, vs.) KALICI OLARAK silmek istediğinize emin misiniz?`)) {
                deleteUser(userId);
            }
        }

        async function deleteUser(userId) {
            try {
                const res = await fetch('admin_api.php?action=delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.success);
                    document.getElementById(`user-row-${userId}`).remove();
                    fetchStats();
                    allUsers = allUsers.filter(u => u.id !== userId);
                } else {
                    showToast(data.error, true);
                }
            } catch(e) { showToast('Bağlantı hatası', true); }
        }

        function showToast(msg, isError = false) {
            const toast = document.getElementById('adminToast');
            toast.innerText = msg;
            toast.classList.remove('hidden', 'translate-y-20', 'bg-green-600', 'bg-red-600', 'border-green-400', 'border-red-400');
            toast.classList.add(isError ? 'bg-red-600' : 'bg-green-600');
            toast.classList.add(isError ? 'border-red-400' : 'border-green-400');
            toast.style.display = 'block';
            setTimeout(() => { toast.classList.add('translate-y-20'); }, 3000);
        }
    </script>
</body>
</html>
