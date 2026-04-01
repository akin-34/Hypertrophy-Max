<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
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
    <title>Hesabım - Strategic Nutrition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap');
        :root { --neon-red: #ff3b3b; --dark-bg: #0b0b0b; --card-bg: #121212; --text-gray: #a1a1aa; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--dark-bg); color: white; margin: 0; padding: 0; }
        h1, h2, h3, h4, .font-teko { font-family: 'Teko', sans-serif; }
        .tab-active { background-color: var(--neon-red); color: white; border-color: var(--neon-red); box-shadow: 0 0 20px rgba(255, 59, 59, 0.4); }
        .tab-inactive { background-color: transparent; color: var(--text-gray); border-color: #222; }
        .meal-card { background: #141414; border-radius: 18px; padding: 18px; margin-bottom: 15px; border-left: 4px solid var(--neon-red); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        #nutritionView { display: none; padding: 20px; }
        .dashboard-header { background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); border-bottom: 1px solid #222; padding: 20px 0; sticky top: 0; z-index: 100; }
        .btn-red { background: var(--neon-red); color: white; font-family: 'Teko', sans-serif; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-red:hover { opacity: 0.8; }
        .btn-outline { background: transparent; border: 1px solid #333; color: #aaa; font-family: 'Teko', sans-serif; border-radius: 8px; padding: 8px 16px; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-outline:hover { border-color: #555; color: #fff; }
        
        /* Social Features CSS */
        #communitySidebar { position: fixed; right: -350px; top: 0; width: 320px; height: 100vh; background: #0f0f0f; border-left: 1px solid #222; z-index: 200; transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: -10px 0 30px rgba(0,0,0,0.5); }
        #communitySidebar.open { right: 0; }
        .user-card { transition: 0.2s; border-bottom: 1px solid #1a1a1a; }
        .user-card:hover { background: #1a1a1a; }
        .online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block; box-shadow: 0 0 10px #22c55e; }
        .offline-dot { width: 8px; height: 8px; background: #4b5563; border-radius: 50%; display: inline-block; }
        
        #chatBox { position: fixed; bottom: 20px; right: 340px; width: 350px; height: 450px; background: #121212; border: 1px solid #333; border-radius: 20px; display: none; flex-direction: column; z-index: 1000; box-shadow: 0 20px 50px rgba(0,0,0,0.8); overflow: hidden; }
        #chatMessages { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 10px; }
        .msg { max-width: 80%; padding: 10px 14px; border-radius: 15px; font-size: 13px; line-height: 1.4; position: relative; }
        .msg-sent { align-self: flex-end; background: var(--neon-red); color: white; border-bottom-right-radius: 2px; }
        .msg-received { align-self: flex-start; background: #222; color: #eee; border-bottom-left-radius: 2px; }
        .chat-img { border-radius: 10px; max-width: 100%; margin-top: 5px; cursor: pointer; }
        .chat-input { background: #0a0a0a; border-top: 1px solid #222; padding: 12px; display: flex; gap: 8px; align-items: center; }
        
        #socialToggleBtn { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: var(--neon-red); border-radius: 50%; display: flex; items-center justify-center; cursor: pointer; z-index: 250; box-shadow: 0 10px 20px rgba(255, 59, 59, 0.3); transition: 0.3s; }
        #socialToggleBtn:hover { transform: scale(1.1); }

        /* Mobile Responsive Overrides */
        @media (max-width: 768px) {
            #communitySidebar { width: 90%; right: -90%; }
            #chatBox { right: 10px; left: 10px; width: auto; bottom: 85px; height: 75vh; }
            .dashboard-header .text-4xl { font-size: 2.25rem; }
            .dashboard-header p { font-size: 8px; letter-spacing: 0.2em; }
            h2.text-3xl { font-size: 1.75rem; }
            h2.text-4xl { font-size: 1.75rem; }
            #arenaSection .text-5xl { font-size: 2.25rem; }
            #questionText { font-size: 1.5rem; padding-left: 1rem; }
            .macro-card p.text-2xl { font-size: 1.5rem; }
        }

        /* Glassmorphism for Plans Grid */
        .plan-glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .plan-glass-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .plan-glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), transparent);
            pointer-events: none;
        }
    </style>
</head>
<body class="min-h-screen">

    <header class="dashboard-header mb-8">
        <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tighter text-white uppercase italic leading-none">
                    Hypertrophy <span class="text-[#ff3b3b]">Max</span> <span
                        class="text-xs italic text-gray-600 font-teko tracking-normal">V1.0</span>
                </h1>
                <p class="text-[9px] text-gray-500 tracking-[0.4em] uppercase mt-2 font-medium">Hoşgeldin, <?php echo $username; ?></p>
            </div>
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                <button onclick="showSection('plans')" id="btnPlans" class="btn-red flex-1 md:flex-none flex items-center justify-center gap-1.5 py-2.5 text-sm"><i class="fas fa-clipboard-list text-[10px]"></i> Planlar</button>
                <button onclick="showSection('arena')" id="btnArena" class="btn-outline flex-1 md:flex-none flex items-center justify-center gap-1.5 py-2.5 text-sm"><i class="fas fa-bolt text-[10px] text-yellow-500"></i> Arena</button>
                <a href="/" class="btn-outline flex-1 md:flex-none flex items-center justify-center gap-1.5 py-2.5 text-sm"><i class="fas fa-home text-[10px]"></i> Geri</a>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                <a href="/admin.php" class="btn-outline flex-1 md:flex-none flex items-center justify-center gap-1.5 py-2.5 text-sm border-yellow-600/30 text-yellow-500 hover:text-yellow-400 group"><i class="fas fa-hammer text-[10px] group-hover:rotate-12 transition-transform"></i> Admin</a>
                <?php endif; ?>
                <button onclick="logout()" class="btn-outline flex-1 md:flex-none flex items-center justify-center gap-1.5 py-2.5 text-sm border-red-900/30 text-red-500/70 hover:text-red-500"><i class="fas fa-sign-out-alt text-[10px]"></i> Çıkış</button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Plans List Section -->
            <section id="plansListSection" class="lg:col-span-2">
                <h2 class="text-3xl md:text-4xl font-teko text-red-500 uppercase italic mb-6 tracking-widest flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-xl md:text-2xl"></i> Kayıtlı Planlarınız
                </h2>
                <div id="plansGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-full text-center py-20 text-gray-500">
                        <i class="fas fa-circle-notch fa-spin text-3xl mb-4"></i>
                        <p class="font-teko uppercase tracking-widest text-xl">Planlar Yükleniyor...</p>
                    </div>
                </div>
            </section>

            <!-- Elite Arena Section -->
            <section id="arenaSection" class="lg:col-span-2 hidden">
                <div class="bg-card-bg border border-white/5 rounded-3xl p-8 relative overflow-hidden">
                    <div id="arenaIntro" class="text-center py-10">
                         <i class="fas fa-bolt text-6xl text-yellow-500 mb-6 drop-shadow-[0_0_15px_rgba(234,179,8,0.5)]"></i>
                         <h2 class="text-5xl font-teko text-white uppercase italic mb-4">Elite Arena'ya Hoşgeldin</h2>
                         <p class="text-gray-400 font-teko text-xl tracking-widest mb-8">SPOR BİLGİNİ TEST ET, PUANLARI TOPLA!</p>
                         <div class="max-w-md mx-auto">
                             <button onclick="startQuiz()" class="w-full btn-red py-6 text-2xl shadow-lg shadow-red-600/20 tracking-[0.2em] animate-pulse hover:animate-none">YARIŞMAYA BAŞLA</button>
                             <p class="text-[10px] text-gray-500 mt-4 uppercase tracking-widest leading-loose">10 SORU // 4 KOLAY (5P) - 4 ORTA (10P) - 2 ZOR (15P)</p>
                         </div>
                    </div>

                    <div id="quizContainer" class="hidden">
                        <!-- Live HUD -->
                        <div id="duelHUD" class="hidden mb-6 flex justify-between items-center bg-black/40 border border-white/5 p-3 rounded-2xl">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-600/20 flex items-center justify-center text-red-500 font-teko text-lg">Siz</div>
                                <div class="w-32 h-1 bg-white/5 rounded-full overflow-hidden">
                                     <div id="myHUDProgress" class="h-full bg-red-600" style="width: 10%"></div>
                                </div>
                            </div>
                            <div class="font-teko text-xl text-yellow-500 italic">Vs</div>
                            <div class="flex items-center gap-3">
                                <div class="w-32 h-1 bg-white/5 rounded-full overflow-hidden">
                                     <div id="oppHUDProgress" class="h-full bg-blue-500" style="width: 0%"></div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-blue-600/20 flex items-center justify-center text-blue-500 font-teko text-lg" id="oppInitial">?</div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mb-8 border-b border-white/5 pb-4">
                            <div class="flex flex-col">
                                <span id="quizProgress" class="font-teko text-2xl text-red-500 italic uppercase leading-none">Soru 1 / 10</span>
                                <div id="quizTimerBar" class="w-full h-1 bg-white/5 rounded-full mt-2 overflow-hidden">
                                    <div id="countdownProgress" class="h-full bg-red-600 transition-all duration-1000" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span id="quizScore" class="font-teko text-2xl text-yellow-500 italic uppercase leading-none">Puan: 0</span>
                                <span id="questionTimer" class="text-[10px] text-gray-500 font-mono mt-1">30S KALDI</span>
                            </div>
                        </div>
                        <div id="questionBox" class="mb-8">
                            <div id="questionDifficulty" class="inline-block px-3 py-1 bg-white/5 rounded-full text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-4 border border-white/5">Yükleniyor...</div>
                            <h3 id="questionText" class="text-3xl font-teko text-white mb-8 border-l-4 border-red-600 pl-6">Yükleniyor...</h3>
                            <div id="optionsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Options will be injected here -->
                            </div>
                        </div>

                        <!-- Sync Waiter Overlay -->
                        <div id="syncWaiter" class="hidden absolute inset-0 bg-black/80 backdrop-blur-sm z-50 flex flex-center items-center justify-center text-center">
                             <div class="animate-bounce">
                                <i class="fas fa-hourglass-half text-5xl text-red-500 mb-4"></i>
                                <h4 class="font-teko text-3xl text-white uppercase italic">Rakibin Cevabı Bekleniyor...</h4>
                                <p class="text-gray-500 text-[10px] uppercase tracking-widest mt-2">DÜELLO SENKRONİZASYONU AKTİF</p>
                             </div>
                        </div>
                    </div>

                    <div id="quizResult" class="hidden text-center py-10 animate-in fade-in zoom-in duration-500">
                        <div id="duelResultBox" class="hidden mb-8 p-6 bg-yellow-500/5 border border-yellow-500/20 rounded-2xl animate-pulse">
                            <h4 class="font-teko text-2xl text-yellow-500 uppercase tracking-widest mb-2">DÜELLO SONUCU</h4>
                            <div class="flex justify-around items-center">
                                <div class="text-center">
                                    <p id="challengerResName" class="text-[10px] text-gray-500 uppercase">OYUNCU 1</p>
                                    <p id="challengerResScore" class="font-teko text-3xl text-white">0</p>
                                </div>
                                <div class="font-teko text-4xl text-yellow-500 italic">VS</div>
                                <div class="text-center">
                                    <p id="opponentResName" class="text-[10px] text-gray-500 uppercase">OYUNCU 2</p>
                                    <p id="opponentResScore" class="font-teko text-3xl text-white">0</p>
                                </div>
                            </div>
                        </div>
                        <i id="resultIcon" class="fas fa-trophy text-7xl text-yellow-500 mb-6 font-bold"></i>
                        <h3 class="text-5xl font-teko text-white uppercase italic mb-2">YARIŞMA TAMAMLANDI!</h3>
                        <p id="finalScoreText" class="text-3xl font-teko text-red-500 uppercase tracking-[0.2em] mb-8">SKOR: 0</p>
                        <button onclick="showSection('arena')" class="btn-red px-12 py-4">TEKRAR DENE</button>
                    </div>
                </div>
            </section>

            <!-- Check-In Sidebar (LG: Col 1) -->
            <aside id="checkinSection" class="space-y-6">
                <div class="bg-card-bg border border-white/5 rounded-3xl p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-600/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                    <h2 class="text-3xl font-teko text-white uppercase italic mb-4 flex items-center gap-3">
                        <i class="fas fa-weight text-red-500"></i> Haftalık Check-In
                    </h2>
                    <form id="checkinForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest ml-1">KİLO (KG)</label>
                                <input type="number" step="0.1" id="weight" placeholder="00.0" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-red-500 outline-none transition-all" required>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest ml-1">BEL (CM)</label>
                                <input type="number" step="0.1" id="waist" placeholder="00.0" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-red-500 outline-none transition-all" required>
                            </div>
                        </div>
                        <button type="submit" class="w-full btn-red py-4 text-lg tracking-widest">KAYDET</button>
                    </form>
                </div>

                <div class="bg-card-bg border border-white/5 rounded-3xl p-6">
                    <h3 class="text-xl font-teko text-gray-400 uppercase tracking-widest mb-4">Ölçüm Geçmişi</h3>
                    <div id="checkinsList" class="space-y-3 max-h-[400px] overflow-y-auto no-scrollbar">
                        <p class="text-center py-10 text-gray-600 text-xs uppercase tracking-widest">Yükleniyor...</p>
                    </div>
                </div>
            </aside>
        </div>

        <section id="nutritionView" class="max-w-4xl mx-auto animate-in fade-in duration-500">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
                <div>
                    <h2 class="text-3xl md:text-5xl font-teko text-red-500 uppercase italic leading-none">STRATEJİK BESLENME PLANI</h2>
                    <p class="text-[9px] md:text-[10px] text-gray-500 tracking-[0.3em] uppercase mt-2 font-bold">SAVED PLAN VERSION // @GurayTraining</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="downloadCurrentPDF()" class="btn-outline text-xs px-4 py-2">PDF İndir</button>
                    <button onclick="backToPlans()" class="btn-red text-xs bg-gray-600 px-4 py-2">Geri Dön</button>
                </div>
            </div>

            <div id="macroContainer" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8"></div>
            <div id="nutritionTabs" class="flex gap-2 overflow-x-auto pb-4 mb-6 no-scrollbar"></div>
            <div id="nutritionContent" class="space-y-4"></div>
        </section>
    </main>

    <!-- Community Sidebar -->
    <div id="communitySidebar">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-black/50">
            <h2 class="text-2xl font-teko text-white tracking-widest uppercase italic">Topluluk</h2>
            <button onclick="toggleCommunity()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div id="userList" class="overflow-y-auto h-[calc(100vh-80px)] no-scrollbar">
            <!-- Users populated here -->
        </div>
    </div>

    <!-- Chat Box -->
    <div id="chatBox">
        <div class="p-4 bg-black/60 border-b border-white/5 flex justify-between items-center">
            <div>
                <span id="chatTargetName" class="font-teko text-xl text-white tracking-widest uppercase italic">Mesajlar</span>
                <span id="chatTargetStatus" class="block text-[8px] text-gray-500 uppercase tracking-widest">Çevrimdışı</span>
            </div>
            <button onclick="closeChat()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div id="chatMessages" class="no-scrollbar"></div>
        <div class="chat-input">
            <label class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors">
                <i class="fas fa-camera text-lg"></i>
                <input type="file" id="chatImageInput" class="hidden" accept="image/*" onchange="uploadChatImage()">
            </label>
            <input type="text" id="chatInput" placeholder="Mesaj yaz..." class="flex-1 bg-white/5 border border-white/10 rounded-full px-4 py-2 text-sm text-white focus:border-red-500/50 outline-none">
            <button onclick="sendMessage()" class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white"><i class="fas fa-paper-plane text-xs"></i></button>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <div id="socialToggleBtn" onclick="toggleCommunity()">
        <i class="fas fa-users text-2xl text-white"></i>
        <div id="totalUnread" class="absolute -top-1 -right-1 bg-white text-red-600 text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</div>
    </div>

    <!-- Premium Arena Modals -->
    <div id="arenaModal" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
        <div class="relative w-full max-w-md bg-[#0a0a0a] border border-red-600/30 rounded-3xl overflow-hidden shadow-[0_0_50px_rgba(220,38,38,0.2)] animate-in zoom-in duration-300">
            <div class="p-8 text-center">
                <div id="arenaModalIcon" class="w-20 h-20 bg-red-600/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-600/20">
                    <i class="fas fa-bolt text-3xl text-red-600 animate-pulse"></i>
                </div>
                <h4 id="arenaModalTitle" class="font-teko text-4xl text-white italic uppercase mb-2">ARENA DAVETİ</h4>
                <p id="arenaModalText" class="text-gray-400 font-teko text-xl tracking-wide px-4 leading-tight mb-8"></p>
                
                <div class="flex gap-4" id="arenaModalButtons">
                    <button id="arenaModalCancel" class="flex-1 py-4 bg-white/5 border border-white/10 rounded-2xl font-teko text-xl text-gray-400 hover:bg-white/10 transition-all uppercase italic">İptal</button>
                    <button id="arenaModalConfirm" class="flex-1 py-4 bg-red-600 hover:bg-red-700 rounded-2xl font-teko text-xl text-white shadow-lg shadow-red-600/20 transition-all uppercase italic">Kabul Et</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Arena Loading Overlay -->
    <div id="arenaLoading" class="hidden fixed inset-0 z-[210] flex items-center justify-center bg-black/95 backdrop-blur-xl">
        <div class="text-center">
            <div class="relative w-32 h-32 mx-auto mb-8">
                <div class="absolute inset-0 border-4 border-red-600/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-red-600 rounded-full border-t-transparent animate-spin"></div>
                <i class="fas fa-brain absolute inset-0 flex items-center justify-center text-4xl text-red-600"></i>
            </div>
            <h4 id="arenaLoadingTitle" class="font-teko text-5xl text-white italic uppercase animate-pulse">Sorular Hazırlanıyor</h4>
            <p class="text-red-500 font-mono text-[10px] tracking-[0.3em] uppercase mt-2">AI Nutrition Coach • Elite Arena</p>
        </div>
    </div>

    <script>
        // Date Helper: SQL DATETIME to Localized string
        function safeFormatDate(str, showTime = true) {
            if(!str) return '---';
            const d = new Date(str.replace(/-/g, '/')); // Replace dashes with slashes for Safari compatibility
            if(isNaN(d.getTime())) return 'Hata';
            const dateStr = d.toLocaleDateString('tr-TR');
            if(!showTime) return dateStr;
            const timeStr = d.toLocaleTimeString('tr-TR', {hour:'2-digit', minute:'2-digit'});
            return `${dateStr} ${timeStr}`;
        }

        let savedPlans = [];
        let currentPlanData = null;

        async function fetchPlans() {
            try {
                const res = await fetch('/get_plans.php', { credentials: 'include' });
                const data = await res.json();
                if (data.error) {
                    document.getElementById('plansGrid').innerHTML = `<div class="col-span-full py-20 text-red-500 font-teko text-2xl uppercase text-center">${data.error}</div>`;
                    return;
                }
                if (!data.length) {
                    document.getElementById('plansGrid').innerHTML = `<div class="col-span-full py-20 text-gray-500 font-teko text-2xl uppercase text-center">Henüz bir plan kaydetmediniz.</div>`;
                    return;
                }
                
                savedPlans = data;
                renderPlansGrid();
            } catch (e) {
                document.getElementById('plansGrid').innerHTML = '<div class="col-span-full py-20 text-red-500 font-teko text-2xl uppercase text-center">Bağlantı Hatası</div>';
            }
        }

        function renderPlansGrid() {
            const html = savedPlans.map((p, i) => {
                const date = safeFormatDate(p.created_at);
                const plan = p.plan_data.data;
                const cal = plan.macros.cal || '0';
                return `
                    <div class="plan-glass-card animate-in zoom-in duration-500 cursor-pointer group" style="animation-delay: ${i * 75}ms" onclick="showPlan(${i})">
                        <div class="flex justify-between items-start mb-6">
                            <span class="text-[10px] text-gray-500 font-bold tracking-widest uppercase bg-white/5 px-2 py-1 rounded-md border border-white/5">${date}</span>
                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-600 group-hover:bg-red-600 group-hover:text-white transition-all">
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </div>
                        </div>
                        <div class="flex items-end justify-between gap-4">
                             <div>
                                 <p class="text-[9px] text-gray-500 uppercase font-black italic tracking-widest mb-1">HEDEF KALORİ</p>
                                 <h4 class="text-4xl md:text-5xl font-teko text-white leading-none">${cal} <span class="text-sm text-gray-600 uppercase">KCAL</span></h4>
                             </div>
                             <div class="flex flex-col gap-1 text-[10px] text-gray-400 font-teko tracking-wider uppercase text-right">
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-md border border-blue-500/10">P: ${plan.macros.p}g</span>
                                <span class="px-2 py-0.5 bg-green-500/10 text-green-400 rounded-md border border-green-500/10">C: ${plan.macros.c}g</span>
                                <span class="px-2 py-0.5 bg-yellow-500/10 text-yellow-400 rounded-md border border-yellow-500/10">F: ${plan.macros.f}g</span>
                             </div>
                        </div>
                    </div>
                `;
            }).join('');
            document.getElementById('plansGrid').innerHTML = html;
        }

        function showPlan(index) {
            const p = savedPlans[index];
            currentPlanData = p.plan_data.data;
            
            document.getElementById('plansListSection').classList.add('hidden');
            const view = document.getElementById('nutritionView');
            view.style.display = 'block';
            
            // Hide Check-in sidebar during plan view as requested
            const sidebar = document.getElementById('checkinSection');
            if(sidebar) sidebar.classList.add('hidden');
            
            renderNutritionView();
        }

        function backToPlans() {
            document.getElementById('nutritionView').style.display = 'none';
            document.getElementById('plansListSection').classList.remove('hidden');
            
            // Restore sidebar
            const sidebar = document.getElementById('checkinSection');
            if(sidebar) sidebar.classList.remove('hidden');
        }

        function renderNutritionView() {
            const m = currentPlanData.macros;
            document.getElementById('macroContainer').innerHTML = `
                <div class="macro-card bg-blue-500/10 p-3 md:p-4 rounded-2xl border border-blue-500/20 text-center"><p class="text-[8px] md:text-[10px] text-blue-400 font-bold uppercase tracking-widest mb-1">PROTEİN</p><p class="font-teko text-2xl md:text-3xl font-bold">${m.p}g</p></div>
                <div class="macro-card bg-green-500/10 p-3 md:p-4 rounded-2xl border border-green-500/20 text-center"><p class="text-[8px] md:text-[10px] text-green-400 font-bold uppercase tracking-widest mb-1">KARB</p><p class="font-teko text-2xl md:text-3xl font-bold">${m.c}g</p></div>
                <div class="macro-card bg-yellow-500/10 p-3 md:p-4 rounded-2xl border border-yellow-500/20 text-center"><p class="text-[8px] md:text-[10px] text-yellow-400 font-bold uppercase tracking-widest mb-1">YAĞ</p><p class="font-teko text-2xl md:text-3xl font-bold">${m.f}g</p></div>
                <div class="macro-card bg-red-500/10 p-3 md:p-4 rounded-2xl border border-red-500/20 text-center"><p class="text-[8px] md:text-[10px] text-red-400 font-bold uppercase tracking-widest mb-1">KALORİ</p><p class="font-teko text-2xl md:text-3xl font-bold">${m.cal}</p></div>
            `;

            const dayKeys = Object.keys(currentPlanData.days);
            document.getElementById('nutritionTabs').innerHTML = dayKeys.map((d, i) => `
                <button onclick="renderNutritionDay('${d}')" class="px-8 py-3 rounded-full border border-white/5 font-teko text-2xl whitespace-nowrap ${i === 0 ? 'tab-active' : 'tab-inactive'} nut-day-tab">${d.toUpperCase()}</button>
            `).join('');
            renderNutritionDay(dayKeys[0]);
        }

        function renderNutritionDay(day) {
            document.querySelectorAll('.nut-day-tab').forEach(t => {
                if (t.innerText === day.toUpperCase()) { t.classList.add('tab-active'); t.classList.remove('tab-inactive'); }
                else { t.classList.add('tab-inactive'); t.classList.remove('tab-active'); }
            });

            const meals = currentPlanData.days[day];
            document.getElementById('nutritionContent').innerHTML = meals.map(m => `
                <div class="meal-card animate-in slide-in-from-bottom-4 duration-300">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-3xl font-teko text-white uppercase italic tracking-wide">${m.meal}</h4>
                        <span class="px-3 py-1 bg-white/5 rounded-lg text-[10px] text-gray-400 font-mono border border-white/5">${m.time}</span>
                    </div>
                    <ul class="space-y-2">
                        ${m.foods.map(f => `
                            <li class="text-sm text-gray-300 flex items-start gap-3">
                                <i class="fas fa-chevron-right text-red-600 mt-1 text-[10px]"></i> 
                                <span>${f}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `).join('');
        }

        function downloadCurrentPDF() {
            if (!currentPlanData) return;
            const plan = currentPlanData;
            const container = document.createElement('div');
            container.style.padding = '20px';
            container.style.background = '#0b0b0b';
            container.style.color = '#fff';
            container.innerHTML = `
                <h1 style="font-family:Teko, sans-serif; color:#ff3b3b; font-size:32px;">STRATEJİK BESLENME PLANI</h1>
                <p style="font-size:12px; color:#aaa">Oluşturma: ${new Date().toLocaleString()}</p>
                <div style="display:flex; gap:12px; margin-top:12px; flex-wrap:wrap;">
                    <div style="border-left:4px solid #ff3b3b; padding:8px;">PROTEİN: ${plan.macros.p}</div>
                    <div style="border-left:4px solid #22c55e; padding:8px;">KARBONHİDRAT: ${plan.macros.c}</div>
                    <div style="border-left:4px solid #f59e0b; padding:8px;">YAĞ: ${plan.macros.f}</div>
                    <div style="border-left:4px solid #ef4444; padding:8px;">KALORİ: ${plan.macros.cal}</div>
                </div>
                <hr style="margin:12px 0; border-color:#222">
            `;
            for (const day of Object.keys(plan.days)) {
                container.innerHTML += `<h2 style="font-family:Teko, sans-serif; color:#fff; margin-top:10px; border-bottom:1px solid #333; padding-bottom:5px;">${day.toUpperCase()}</h2>`;
                const meals = plan.days[day];
                for (const m of meals) {
                    container.innerHTML += `<div style="margin-bottom:8px;"><strong>${m.meal} — ${m.time}</strong><ul style="margin-left:12px; font-size:14px; color:#ccc">${m.foods.map(f => `<li>${f}</li>`).join('')}</ul></div>`;
                }
            }
            const opt = { margin: 10, filename: `saved_plan_${new Date().getTime()}.pdf`, html2canvas: { scale: 2, backgroundColor: '#0b0b0b' }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
            html2pdf().set(opt).from(container).save();
        }

        async function logout() {
            await fetch('/logout.php', { method: 'POST', credentials: 'include' });
            location.href = '/';
        }

        async function fetchCheckins() {
            try {
                const res = await fetch('/get_checkins.php', { credentials: 'include' });
                const data = await res.json();
                renderCheckins(data);
            } catch (e) {
                console.error('Checkins fetch error:', e);
            }
        }

        function renderCheckins(data) {
            const list = document.getElementById('checkinsList');
            if (!data || !data.length) {
                list.innerHTML = '<p class="text-center py-10 text-gray-600 text-[10px] uppercase tracking-widest">Henüz ölçüm yok</p>';
                return;
            }

            list.innerHTML = data.map(c => `
                <div class="bg-black/20 border border-white/5 p-4 rounded-xl flex justify-between items-center transition-all hover:border-white/10">
                    <div class="flex flex-col">
                        <span class="text-[9px] text-gray-600 font-bold uppercase">${safeFormatDate(c.created_at, false)}</span>
                        <div class="flex gap-4 mt-1">
                            <div class="flex items-center gap-1.5"><i class="fas fa-weight-scale text-[10px] text-blue-500/50"></i><span class="font-teko text-lg text-white">${c.weight}kg</span></div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-ruler-horizontal text-[10px] text-green-500/50"></i><span class="font-teko text-lg text-white">${c.waist}cm</span></div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        document.getElementById('checkinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.innerText;
            
            const weight = document.getElementById('weight').value;
            const waist = document.getElementById('waist').value;

            try {
                btn.disabled = true;
                btn.innerText = 'KAYDEDİLİYOR...';
                
                const res = await fetch('/save_checkin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ weight, waist }),
                    credentials: 'include'
                });
                
                const result = await res.json();
                if (result.success) {
                    e.target.reset();
                    await fetchCheckins();
                } else {
                    alert(result.error || 'Hata oluştu');
                }
            } catch (err) {
                alert('Bağlantı hatası');
            } finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });

        // Initial loads
        fetchPlans();
        fetchCheckins();

        // --- Social Logic ---
        let chatTargetId = null;
        let chatInterval = null;
        let communityOpen = false;

        function toggleCommunity() {
            communityOpen = !communityOpen;
            document.getElementById('communitySidebar').classList.toggle('open', communityOpen);
            if (communityOpen) fetchUsers();
        }

        async function updatePresence() {
            try { await fetch('/update_status.php', { method: 'POST', credentials: 'include' }); } catch(e){}
        }

        async function fetchUsers() {
            try {
                const res = await fetch('/get_users.php', { credentials: 'include' });
                const users = await res.json();
                renderUserList(users);
                updateUnreadCounts(users);
            } catch(e){}
        }

        let lastTotalUnread = 0;
        const notifyAudio = new Audio('/assets/notify.mp3');

        function updateUnreadCounts(users) {
            const total = users.reduce((sum, u) => sum + u.unread_count, 0);
            const badge = document.getElementById('totalUnread');
            
            if (total > 0) {
                badge.innerText = total > 9 ? '9+' : total;
                badge.classList.remove('hidden');
                document.title = `(${total}) STRATEGIC NUTRITION`;
            } else {
                badge.classList.add('hidden');
                document.title = 'STRATEGIC NUTRITION';
            }

            if (total > lastTotalUnread) {
                notifyAudio.play().catch(e => console.log('Audio blocked'));
            }
            lastTotalUnread = total;
        }

        function renderUserList(users) {
            const list = document.getElementById('userList');
            if (!users || !users.length) {
                list.innerHTML = '<p class="text-center py-20 text-gray-600 text-[10px] uppercase tracking-widest px-4">Henüz başka kayıtlı kullanıcı yok.</p>';
                return;
            }
            list.innerHTML = users.map(u => {
                const statusDot = u.is_online ? '<span class="online-dot mr-2"></span>' : '<span class="offline-dot mr-2"></span>';
                const lastTime = safeFormatDate(u.last_activity, true).split(' ')[1];
                return `
                    <div class="user-card p-4 cursor-pointer flex justify-between items-center" onclick="openChat(${u.id}, '${u.username}', ${u.is_online})">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-red-600/20 border border-red-500/20 flex items-center justify-center text-red-500 font-teko text-xl mr-3 font-bold">
                                ${u.username[0].toUpperCase()}
                            </div>
                            <div>
                                <h4 class="text-white font-teko text-lg leading-tight tracking-wider uppercase">${u.username}</h4>
                                <div class="flex items-center text-[9px] text-gray-500 uppercase tracking-widest">
                                    ${statusDot} ${u.is_online ? 'Çevrimiçi' : 'Son görülme: ' + lastTime}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            ${u.unread_count > 0 ? `<span class="bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">${u.unread_count}</span>` : ''}
                            <button onclick="event.stopPropagation(); inviteToArena(${u.id}, '${u.username}')" class="w-8 h-8 rounded-full bg-yellow-500/10 hover:bg-yellow-500/30 text-yellow-500 flex items-center justify-center transition-all" title="Arena Meydan Oku">
                                <i class="fas fa-bolt text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function openChat(id, name, isOnline) {
            chatTargetId = id;
            document.getElementById('chatBox').style.display = 'flex';
            document.getElementById('chatTargetName').innerText = name;
            document.getElementById('chatTargetStatus').innerText = isOnline ? 'ÇEVRİMİÇİ' : 'ÇEVRİMDIŞI';
            document.getElementById('chatTargetStatus').classList.toggle('text-green-500', isOnline);
            document.getElementById('chatTargetStatus').classList.toggle('text-gray-500', !isOnline);
            
            loadMessages();
            if(chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(loadMessages, 3000);
        }

        function closeChat() {
            document.getElementById('chatBox').style.display = 'none';
            chatTargetId = null;
            if(chatInterval) clearInterval(chatInterval);
        }

        async function loadMessages() {
            if(!chatTargetId) return;
            try {
                const res = await fetch(`/get_messages.php?other_id=${chatTargetId}`, { credentials: 'include' });
                const msgs = await res.json();
                renderMessages(msgs);
            } catch(e){}
        }

        function renderMessages(msgs) {
            const container = document.getElementById('chatMessages');
            const scrollDown = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
            
            container.innerHTML = msgs.map(m => {
                const isSent = m.sender_id != chatTargetId;
                let content;
                if (m.message_type === 'IMAGE') {
                    const safePath = m.file_path.replace(/"/g, '&quot;');
                    content = `<img src="/${safePath}" class="chat-img" onclick="window.open('/${safePath}')">`;
                } else {
                    // Create a text node to escape HTML
                    const dummy = document.createElement('div');
                    dummy.textContent = m.message_text;
                    content = dummy.innerHTML;
                }
                
                return `
                    <div class="msg ${isSent ? 'msg-sent' : 'msg-received'}">
                        ${content}
                        <div class="text-[8px] mt-1 opacity-50 text-right">${new Date(m.created_at).toLocaleTimeString('tr-TR', {hour:'2-digit', minute:'2-digit'})}</div>
                    </div>
                `;
            }).join('');
            
            if(scrollDown) container.scrollTop = container.scrollHeight;
        }

        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const text = input.value.trim();
            if(!text || !chatTargetId) return;

            const formData = new FormData();
            formData.append('receiver_id', chatTargetId);
            formData.append('message_text', text);

            input.value = '';
            try {
                await fetch('/send_message.php', { method: 'POST', body: formData, credentials: 'include' });
                loadMessages();
            } catch(e){}
        }

        async function uploadChatImage() {
            const fileInput = document.getElementById('chatImageInput');
            if(!fileInput.files.length || !chatTargetId) return;

            const formData = new FormData();
            formData.append('receiver_id', chatTargetId);
            formData.append('image', fileInput.files[0]);

            try {
                await fetch('/send_message.php', { method: 'POST', body: formData, credentials: 'include' });
                fileInput.value = '';
                loadMessages();
            } catch(e){}
        }

        // Enter key for chat
        document.getElementById('chatInput').addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMessage();
        });

        // Polling cycles
        setInterval(updatePresence, 60000); // Update my status every 60s
        setInterval(() => { if(communityOpen) fetchUsers(); }, 10000); // Update list every 10s if sidebar open
        updatePresence();

        // --- Elite Arena JS ---
        let currentQuizData = null;
        let currentQuizIndex = 0;
        let quizScore = 0;
        let countdownValue = 30;
        let timerInterval = null;

        function startTimer() {
            clearInterval(timerInterval);
            countdownValue = 30;
            updateTimerUI();
            timerInterval = setInterval(() => {
                countdownValue--;
                updateTimerUI();
                if (countdownValue <= 0) {
                    clearInterval(timerInterval);
                    checkAnswer(-1); // Automatically fail on time out
                }
            }, 1000);
        }

        function updateTimerUI() {
            document.getElementById('questionTimer').innerText = `${countdownValue}S KALDI`;
            const progress = (countdownValue / 30) * 100;
            document.getElementById('countdownProgress').style.width = `${progress}%`;
            
            // Color feedback
            const pbar = document.getElementById('countdownProgress');
            if(countdownValue <= 5) pbar.classList.replace('bg-red-600', 'bg-red-400');
            else pbar.classList.replace('bg-red-400', 'bg-red-600');
        }

        function showArenaToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed top-24 right-10 bg-red-600/90 text-white px-6 py-4 rounded-2xl font-teko text-2xl italic tracking-widest shadow-[0_0_20px_rgba(220,38,38,0.5)] z-[100] animate-in slide-in-from-right-full duration-500';
            toast.innerHTML = `<i class="fas fa-laugh-squint mr-3"></i> ${message}`;
            document.body.appendChild(toast);
            
            // Play laugh sound from CDN
            const laugh = new Audio('https://www.soundjay.com/human/laughter-2.mp3');
            laugh.play().catch(() => {});

            setTimeout(() => {
                toast.classList.add('animate-out', 'fade-out', 'slide-out-to-right-full');
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        function showArenaModal(title, text, confirmLabel, cancelLabel = "İptal") {
            return new Promise((resolve) => {
                const modal = document.getElementById('arenaModal');
                const titleEl = document.getElementById('arenaModalTitle');
                const textEl = document.getElementById('arenaModalText');
                const confirmBtn = document.getElementById('arenaModalConfirm');
                const cancelBtn = document.getElementById('arenaModalCancel');
                
                titleEl.innerText = title;
                textEl.innerText = text;
                confirmBtn.innerText = confirmLabel;
                cancelBtn.innerText = cancelLabel;
                
                modal.classList.remove('hidden');
                
                const onConfirm = () => {
                    modal.classList.add('hidden');
                    confirmBtn.removeEventListener('click', onConfirm);
                    cancelBtn.removeEventListener('click', onCancel);
                    resolve(true);
                };
                
                const onCancel = () => {
                    modal.classList.add('hidden');
                    confirmBtn.removeEventListener('click', onConfirm);
                    cancelBtn.removeEventListener('click', onCancel);
                    resolve(false);
                };
                
                confirmBtn.addEventListener('click', onConfirm);
                cancelBtn.addEventListener('click', onCancel);
            });
        }

        function showSection(name) {
            document.getElementById('plansListSection').classList.toggle('hidden', name !== 'plans');
            document.getElementById('arenaSection').classList.toggle('hidden', name !== 'arena');
            document.getElementById('nutritionView').style.display = 'none';
            
            // Sidebar logic: Show/Hide based on context
            const sidebar = document.getElementById('checkinSection');
            if(sidebar) {
                sidebar.classList.toggle('hidden', name === 'arena');
            }
            
            document.getElementById('btnPlans').classList.toggle('btn-red', name === 'plans');
            document.getElementById('btnArena').classList.toggle('btn-red', name === 'arena');
            document.getElementById('btnPlans').classList.toggle('btn-outline', name !== 'plans');
            document.getElementById('btnArena').classList.toggle('btn-outline', name !== 'arena');

            if(name === 'arena') {
                document.getElementById('arenaIntro').classList.remove('hidden');
                document.getElementById('quizContainer').classList.add('hidden');
                document.getElementById('quizResult').classList.add('hidden');
            }
        }

        async function markReady() {
            if(!activeChallengeId) return;
            const fd = new FormData();
            fd.append('challenge_id', activeChallengeId);
            await fetch('/challenge_manager.php?action=mark_ready', { method: 'POST', body: fd, credentials: 'include' });
        }

        async function startQuiz(injectedQuestions = null) {
            currentQuizData = null; // Reset for new session
            document.getElementById('arenaIntro').classList.add('hidden');
            document.getElementById('quizContainer').classList.remove('hidden');
            document.getElementById('questionText').innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Sorular Hazırlanıyor...';
            document.getElementById('optionsGrid').innerHTML = '';
            
            // Reset sync state
            window.prevOppStep = 0;
            window.prevOppScore = 0;
            
            // If in challenge, handle Lobby
            if(activeChallengeId) {
                document.getElementById('syncWaiter').classList.remove('hidden');
                document.getElementById('syncWaiter').querySelector('h4').innerText = 'DÜELLO LOBİSİ: RAKİP HAZIRLANIYOR...';
                
                // Mark self as ready
                const fd = new FormData();
                fd.append('challenge_id', activeChallengeId);
                await fetch('/challenge_manager.php?action=mark_ready', { method: 'POST', body: fd, credentials: 'include' });
                
                document.getElementById('duelHUD').classList.remove('hidden');
            }

            try {
                // Modified: Fetch shared questions if in challenge
                let res;
                if(injectedQuestions) {
                    currentQuizData = injectedQuestions;
                } else if(activeChallengeId) {
                    // Poll for questions until opponent prepares them
                    let attempts = 0;
                    while(attempts < 120) { // Extended for AI latency
                        const res = await fetch(`/challenge_manager.php?action=get_questions&challenge_id=${activeChallengeId}`, { credentials: 'include' });
                        if(res.ok) {
                             const text = await res.text();
                             try {
                                 const data = JSON.parse(text);
                                 if(data.questions) {
                                     currentQuizData = data;
                                     break;
                                 }
                             } catch(e) {}
                        }
                        attempts++;
                        await new Promise(r => setTimeout(r, 1000));
                    }
                    if(!currentQuizData) throw new Error("Sorular hazırlanamadı veya rakip reddetti.");
                } else {
                    const res = await fetch(`/get_quiz.php`, { credentials: 'include' });
                    currentQuizData = await res.json();
                }
                
                
                if(!currentQuizData || !currentQuizData.questions) {
                    console.error("Quiz Data Error:", currentQuizData);
                    throw new Error(currentQuizData.error || currentQuizData.message || "Soru formatı geçersiz (questions key missing)");
                }

                currentQuizIndex = 0;
                quizScore = 0;

                if(activeChallengeId) {
                    document.getElementById('syncWaiter').classList.remove('hidden');
                    document.getElementById('syncWaiter').querySelector('h4').innerText = 'DÜELLO BAŞLATIILIYOR...';
                    await markReady();
                    await waitForDuelStart();
                }

                renderQuizStep();
            } catch(e) {
                console.error("Quiz Start Error:", e);
                const msg = e.message.includes("Kota Doldu") 
                    ? "🤖 AI Sistemimiz şu an yoğun. Lütfen 10 saniye bekleyip tekrar şimşek butonuna basın."
                    : 'Yarışma başlatılamadı: ' + e.message;
                alert(msg);
                showSection('arena');
            }
        }

        async function waitForDuelStart() {
            return new Promise((resolve, reject) => {
                let attempts = 0;
                const check = setInterval(async () => {
                    attempts++;
                    if(attempts > 60) { // 2 minute timeout
                        clearInterval(check);
                        reject(new Error("Rakip beklenirken zaman aşımı oluştu."));
                        return;
                    }

                    try {
                        const res = await fetch(`/challenge_manager.php?action=sync_duel&challenge_id=${activeChallengeId}&score=0&step=0`, { credentials: 'include' });
                        const data = await res.json();
                        
                        if(data.error) {
                            clearInterval(check);
                            reject(new Error(data.error));
                            return;
                        }

                        // Init prev scores from lobby state so Q1 works correctly
                        const isChallenger = data.challenger_id == <?php echo $_SESSION['id']; ?>;
                        window.prevOppStep = parseInt(isChallenger ? data.opponent_step : data.challenger_step);
                        window.prevOppScore = parseInt(isChallenger ? data.opponent_score : data.challenger_score);

                        // HUD Initials
                        if(data.challenger_id && data.opponent_id) {
                             const oppName = isChallenger ? "Rakip" : "Meydan Okuyan"; // We don't have name in sync_duel select * unless joined
                             document.getElementById('oppInitial').innerText = isChallenger ? "R" : "M"; 
                        }

                        if(data.challenger_ready == 1 && data.opponent_ready == 1) {
                            clearInterval(check);
                            document.getElementById('syncWaiter').classList.add('hidden');
                            resolve();
                        }
                    } catch(e) {
                        console.error("Lobby Sync Error:", e);
                    }
                }, 500);
            });
        }

        function renderQuizStep() {
            if(!currentQuizData || !currentQuizData.questions || currentQuizIndex >= currentQuizData.questions.length) {
                clearInterval(timerInterval);
                finishQuiz();
                return;
            }
            
            const q = currentQuizData.questions[currentQuizIndex];
            document.getElementById('quizProgress').innerText = `Soru ${currentQuizIndex + 1} / 10`;
            document.getElementById('quizScore').innerText = `Puan: ${quizScore}`;
            
            const diffEl = document.getElementById('questionDifficulty');
            diffEl.innerText = q.difficulty || 'ORTA';
            diffEl.className = 'inline-block px-3 py-1 bg-white/5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-4 border border-white/5 ' + 
                              (q.difficulty === 'ZOR' ? 'text-purple-500' : (q.difficulty === 'BASIT' ? 'text-green-500' : 'text-yellow-500'));

            document.getElementById('questionText').innerText = q.q;
            
            document.getElementById('optionsGrid').innerHTML = q.o.map((opt, i) => `
                <button onclick="checkAnswer(${i})" class="option-btn text-left p-6 bg-white/5 border border-white/10 rounded-2xl hover:border-red-500 transition-all text-gray-300 hover:text-white font-teko text-xl tracking-wider">
                    ${opt}
                </button>
            `).join('');
            
            startTimer();
        }

        async function checkAnswer(idx) {
            clearInterval(timerInterval);
            const q = currentQuizData.questions[currentQuizIndex];
            const btns = document.querySelectorAll('#optionsGrid button');
            btns.forEach(b => b.disabled = true);
            
            const points = q.difficulty === 'ZOR' ? 15 : (q.difficulty === 'BASIT' ? 5 : 10);

            if(idx === q.a) {
                quizScore += points;
                btns[idx].classList.add('!border-green-500', '!text-green-500');
            } else if(idx !== -1) {
                btns[idx].classList.add('!border-red-500', '!text-red-500');
                btns[q.a].classList.add('!border-green-500', '!text-green-500');
            } else {
                btns[q.a].classList.add('!border-yellow-500', '!text-yellow-500');
            }
            
            setTimeout(async () => {
                // If duel, sync step before proceeding
                if(activeChallengeId) {
                    document.getElementById('syncWaiter').classList.remove('hidden');
                    document.getElementById('syncWaiter').querySelector('h4').innerText = 'RAKİBİN CEVABI BEKLENİYOR...';
                    await syncStep(currentQuizIndex + 1);
                    document.getElementById('syncWaiter').classList.add('hidden');
                }
                currentQuizIndex++;
                renderQuizStep();
            }, 1200);
        }

        async function syncStep(step) {
            return new Promise((resolve, reject) => {
                let attempts = 0;
                const sync = setInterval(async () => {
                    attempts++;
                    if(attempts > 30) { // 1 minute timeout per step
                        clearInterval(sync);
                        reject(new Error("Rakip cevabı beklenirken zaman aşımı oluştu."));
                        return;
                    }
                    try {
                        const res = await fetch(`/challenge_manager.php?action=sync_duel&challenge_id=${activeChallengeId}&score=${quizScore}&step=${step}`, { credentials: 'include' });
                        const data = await res.json();
                        
                        // Update HUD
                        document.getElementById('myHUDProgress').style.width = `${(step/10)*100}%`;
                        const isChallenger = data.challenger_id == <?php echo $_SESSION['id']; ?>;
                        const oppStep = parseInt(isChallenger ? data.opponent_step : data.challenger_step);
                        const oppScore = parseInt(isChallenger ? data.opponent_score : data.challenger_score);

                        document.getElementById('oppHUDProgress').style.width = `${(oppStep/10)*100}%`;
                        
                        if(oppStep >= step || step >= 10) {
                            clearInterval(sync);
                            
                            // Trash Talk Logic
                            if (window.prevOppStep !== undefined && oppStep > window.prevOppStep) {
                                if (oppScore === window.prevOppScore) {
                                    showArenaToast("MAL BU SORUYU YAPAMADI!");
                                }
                            }
                            
                            window.prevOppStep = oppStep;
                            window.prevOppScore = oppScore;
                            resolve();
                        }
                    } catch(e) {
                        console.error("Step Sync Error:", e);
                    }
                }, 2000);
            });
        }

        async function finishQuiz() {
            document.getElementById('quizContainer').classList.add('hidden');
            document.getElementById('quizResult').classList.remove('hidden');
            document.getElementById('finalScoreText').innerText = `TOPLAM SKOR: ${quizScore}`;
            
            try {
                // Save general score
                await fetch('/save_quiz_result.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ score: quizScore, difficulty: 'MIXED' }),
                    credentials: 'include'
                });
                
                // Submit challenge score and FETCH result
                if(activeChallengeId) {
                    const fd = new FormData();
                    fd.append('challenge_id', activeChallengeId);
                    fd.append('score', quizScore);
                    await fetch('/challenge_manager.php?action=submit_score', { method: 'POST', body: fd, credentials: 'include' });
                    
                    // Polling for opponent score (or wait a bit)
                    setTimeout(async () => {
                        const res = await fetch(`/challenge_manager.php?action=get_result&challenge_id=${activeChallengeId}`, { credentials: 'include' });
                        const data = await res.json();
                        if(data.id) {
                            document.getElementById('duelResultBox').classList.remove('hidden');
                            document.getElementById('challengerResName').innerText = data.challenger_name;
                            document.getElementById('opponentResName').innerText = data.opponent_name;
                            document.getElementById('challengerResScore').innerText = data.challenger_score;
                            document.getElementById('opponentResScore').innerText = data.opponent_score;
                            
                            if(data.status === 'FINISHED') activeChallengeId = null;
                        }
                    }, 500); 
                }
            } catch(e){}
        }

        // --- Multiplayer Logic ---
        let activeChallengeId = null;

        async function inviteToArena(id, name) {
            const confirmed = await showArenaModal("ARENA DAVETİ", `${name} kullanıcısına düello daveti göndermek istiyor musun?`, "DAVET ET");
            if(!confirmed) return;
            
            showSection('arena');
            document.getElementById('arenaIntro').classList.add('hidden');
            document.getElementById('syncWaiter').classList.remove('hidden');
            document.getElementById('syncWaiter').querySelector('h4').innerText = 'RAKİP BEKLENİYOR...';

            try {
                const fd = new FormData();
                fd.append('opponent_id', id);

                const res = await fetch('/challenge_manager.php?action=invite', { method: 'POST', body: fd, credentials: 'include' });
                const data = await res.json();
                
                if(data.success) {
                    activeChallengeId = data.challenge_id;
                    startQuiz(); 
                } else {
                    alert('Hata: ' + (data.message || 'Meydan okuma gönderilemedi.'));
                    showSection('plans');
                }
            } catch(e) {
                console.error("Invite Error:", e);
                alert(e.message);
                showSection('plans');
            }
        }

        let inviteSoundInterval = null;
        let isInviteModalOpen = false;

        async function checkInvites() {
            if(activeChallengeId || isInviteModalOpen) return;
            try {
                const res = await fetch('/challenge_manager.php?action=check_invites', { credentials: 'include' });
                const data = await res.json();
                if(data.id && !data.none) {
                    isInviteModalOpen = true;
                    // Play initial sound
                    notifyAudio.play().catch(() => {});
                    
                    // Start sound loop every 4 seconds until modal is closed
                    if(!inviteSoundInterval) {
                        inviteSoundInterval = setInterval(() => {
                            notifyAudio.play().catch(() => {});
                        }, 4000);
                    }

                    const accept = await showArenaModal("DÜELLO DAVETİ!", `${data.challenger_name} seni düelloya çağırdı! Kabul ediyor musun?`, "KABUL ET", "REDDET");
                    
                    // Stop sound loop once decision is made
                    if(inviteSoundInterval) {
                        clearInterval(inviteSoundInterval);
                        inviteSoundInterval = null;
                    }
                    isInviteModalOpen = false;

                    if(accept) {
                        isInviteModalOpen = true; // KEEP LOCKED during AI prep
                        const loadingOverlay = document.getElementById('arenaLoading');
                        const loadingTitle = document.getElementById('arenaLoadingTitle');
                        loadingTitle.innerText = "SORULAR HAZIRLANIYOR...";
                        loadingOverlay.classList.remove('hidden');

                        try {
                            const qRes = await fetch('/get_quiz.php', { credentials: 'include' });
                            const qData = await qRes.json();

                            const fd = new FormData();
                            fd.append('challenge_id', data.id);
                            fd.append('accept', '1');
                            fd.append('questions', JSON.stringify(qData));
                            await fetch('/challenge_manager.php?action=respond', { method: 'POST', body: fd, credentials: 'include' });
                            
                            activeChallengeId = data.id;
                            isInviteModalOpen = false; // FINALLY UNLOCK
                            showSection('arena');
                            startQuiz(qData);
                        } catch(e) {
                            alert("Sorular hazırlanırken hata oluştu.");
                            isInviteModalOpen = false;
                        } finally {
                            loadingOverlay.classList.add('hidden');
                        }
                    } else {
                        const fd = new FormData();
                        fd.append('challenge_id', data.id);
                        fd.append('accept', '0');
                        await fetch('/challenge_manager.php?action=respond', { method: 'POST', body: fd, credentials: 'include' });
                        isInviteModalOpen = false; // UNLOCK on reject
                    }
                }
            } catch(e){
                isInviteModalOpen = false;
            }
        }

        setInterval(checkInvites, 500);

    </script>
</body>
</html>