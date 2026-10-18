<?php
// Load public station definitions
$stationsFile = 'stations.json';
$stationsData = [];
if (file_exists($stationsFile)) {
    $stationsData = json_decode(file_get_contents($stationsFile), true);
}
$stations = $stationsData['stations'] ?? [
    'station_1' => [
        'name' => 'The Den',
        'description' => 'Our Purple Den'
    ]
];

// Determine selected station
$activeStation = $_GET['station'] ?? array_key_first($stations);
if (!array_key_exists($activeStation, $stations)) {
    $activeStation = array_key_first($stations);
}

// Load playlist for active station
$playlistFile = "music/{$activeStation}_playlist.json";
$playlist = [];
if (file_exists($playlistFile)) {
    $playlist = json_decode(file_get_contents($playlistFile), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($stations[$activeStation]['name']); ?> // GLITCH RADIO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323&display=swap');

        body {
            font-family: 'Share Tech Mono', monospace;
            background-color: #07010f;
            color: #e0aaff;
            overflow-x: hidden;
        }

        .crt::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 999;
            background-size: 100% 3px, 3px 100%;
            pointer-events: none;
        }

        .neon-glow {
            text-shadow: 0 0 10px #c77dff, 0 0 20px #9d4edd, 0 0 30px #7b2cbf;
        }

        .box-glow {
            box-shadow: 0 0 20px rgba(157, 78, 221, 0.2), inset 0 0 15px rgba(124, 44, 191, 0.1);
            border: 1px solid #9d4edd;
        }

        .box-glow-active {
            box-shadow: 0 0 25px rgba(224, 170, 255, 0.4), inset 0 0 20px rgba(199, 125, 255, 0.2);
            border: 1px solid #e0aaff;
        }

        @keyframes glitch {
            0% { transform: translate(0) }
            20% { transform: translate(-2px, 2px) }
            40% { transform: translate(-2px, -2px) }
            60% { transform: translate(2px, 2px) }
            80% { transform: translate(2px, -2px) }
            100% { transform: translate(0) }
        }

        .glitch-hover:hover {
            animation: glitch 0.2s infinite;
        }

        .glitch-title {
            position: relative;
            display: inline-block;
        }

        .glitch-title::before, .glitch-title::after {
            content: attr(data-text);
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
        }

        .glitch-title::before {
            left: 2px;
            text-shadow: -2px 0 #ff007f;
            clip: rect(24px, 550px, 90px, 0);
            animation: glitch-anim 3s infinite linear alternate-reverse;
        }

        .glitch-title::after {
            left: -2px;
            text-shadow: -2px 0 #00f0ff;
            clip: rect(85px, 550px, 140px, 0);
            animation: glitch-anim2 2.5s infinite linear alternate-reverse;
        }

        @keyframes glitch-anim {
            0% { clip: rect(12px, 9999px, 44px, 0); }
            10% { clip: rect(65px, 9999px, 11px, 0); }
            20% { clip: rect(34px, 9999px, 89px, 0); }
            30% { clip: rect(78px, 9999px, 23px, 0); }
            40% { clip: rect(10px, 9999px, 55px, 0); }
            50% { clip: rect(45px, 9999px, 99px, 0); }
            60% { clip: rect(89px, 9999px, 34px, 0); }
            70% { clip: rect(23px, 9999px, 76px, 0); }
            80% { clip: rect(56px, 9999px, 12px, 0); }
            90% { clip: rect(90px, 9999px, 67px, 0); }
            100% { clip: rect(32px, 9999px, 45px, 0); }
        }

        @keyframes glitch-anim2 {
            0% { clip: rect(76px, 9999px, 12px, 0); }
            10% { clip: rect(23px, 9999px, 89px, 0); }
            20% { clip: rect(54px, 9999px, 34px, 0); }
            30% { clip: rect(12px, 9999px, 90px, 0); }
            40% { clip: rect(87px, 9999px, 45px, 0); }
            50% { clip: rect(34px, 9999px, 78px, 0); }
            60% { clip: rect(90px, 9999px, 23px, 0); }
            70% { clip: rect(45px, 9999px, 65px, 0); }
            80% { clip: rect(11px, 9999px, 88px, 0); }
            90% { clip: rect(67px, 9999px, 19px, 0); }
            100% { clip: rect(43px, 9999px, 54px, 0); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f021f;
        }
        ::-webkit-scrollbar-thumb {
            background: #7b2cbf;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #c77dff;
        }
    </style>
</head>
<body class="crt min-h-screen flex flex-col justify-between selection:bg-purple-500 selection:text-white">

    <!-- Header -->
    <header class="bg-[#0f021f]/90 border-b border-purple-900/60 py-4 px-6 backdrop-blur-md sticky top-0 z-50 shadow-[0_0_15px_rgba(123,44,191,0.3)]">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center space-x-3">
                <div class="bg-purple-950 text-purple-300 border border-purple-500 p-3 rounded-lg font-bold text-xl shadow-[0_0_15px_#9d4edd] animate-pulse">
                    <i class="fa-solid fa-skull"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-widest text-purple-200 glitch-title" data-text="// THE DEN">
                        // THE DEN
                    </h1>
                    <p class="text-xs text-purple-400 tracking-wider">TELEGRAM GLITCH RADIO // @actually_purple</p>
                </div>
            </div>

            <!-- Station Switcher -->
            <nav class="flex flex-wrap gap-2">
                <?php foreach ($stations as $sId => $sInfo): ?>
                    <a href="?station=<?php echo $sId; ?>"
                       class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wider transition uppercase <?php echo ($activeStation === $sId) ? 'bg-purple-700 text-white shadow-[0_0_15px_#c77dff] border border-purple-300' : 'bg-[#150329] text-purple-300 border border-purple-900 hover:border-purple-500 hover:bg-purple-950/50'; ?>">
                        <i class="fa-solid fa-terminal mr-2 text-purple-400"></i><?php echo htmlspecialchars($sInfo['name']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-12 w-full flex-grow">
        <div class="bg-[#0b0117]/90 box-glow rounded-xl p-6 md:p-10 relative overflow-hidden backdrop-blur-sm">
            <!-- Background Cyber Grid overlay -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#1b0538_1px,transparent_1px),linear-gradient(to_bottom,#1b0538_1px,transparent_1px)] bg-[size:2rem_2rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-30 pointer-events-none"></div>

            <div class="mb-8 relative z-10">
                <div class="flex items-center space-x-2 text-purple-400 text-xs font-bold uppercase tracking-widest mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-ping"></span>
                    <span class="text-pink-400">SIGNAL SECURED // LIVE FEED</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black text-purple-100 tracking-wider mb-2 glitch-title" data-text="<?php echo strtoupper(htmlspecialchars($stations[$activeStation]['name'])); ?>">
                    <?php echo htmlspecialchars($stations[$activeStation]['name']); ?>
                </h2>
                <p class="text-purple-300/80 text-sm tracking-wide border-l-2 border-purple-500 pl-3">
                    <?php echo htmlspecialchars($stations[$activeStation]['description']); ?>
                </p>
            </div>

            <!-- Now Playing Display -->
            <div class="bg-[#06000c] border border-purple-800/80 rounded-lg p-6 mb-8 text-center relative z-10 box-glow-active">
                <div id="track-art" class="w-24 h-24 bg-purple-950/60 border border-purple-500/50 rounded-full flex items-center justify-center mx-auto mb-4 text-purple-300 text-3xl shadow-[0_0_20px_rgba(157,78,221,0.3)]">
                    <i class="fa-solid fa-compact-disc animate-spin" style="animation-duration: 4s;"></i>
                </div>
                <div class="text-xs text-purple-400 tracking-widest uppercase mb-1">CURRENTLY EXECUTING</div>
                <div id="now-playing-title" class="text-lg md:text-xl font-bold text-purple-100 tracking-wider mb-2 truncate px-4 neon-glow">
                    SELECT TRACK FROM BUFFER
                </div>
                <div id="station-status" class="text-[11px] text-pink-400 tracking-widest uppercase">[SYSTEM READY]</div>
            </div>

            <!-- Audio Player Controls -->
            <div class="space-y-6 relative z-10">
                <audio id="audio-player" class="w-full hidden" preload="auto"></audio>

                <!-- Custom Progress Bar -->
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-purple-400 tracking-widest">
                        <span id="current-time">00:00</span>
                        <span id="total-duration">00:00</span>
                    </div>
                    <div id="progress-container" class="w-full bg-purple-950/80 border border-purple-800 h-2.5 rounded-full cursor-pointer overflow-hidden">
                        <div id="progress-bar" class="bg-gradient-to-r from-purple-600 via-fuchsia-500 to-pink-500 h-full w-0 transition-all shadow-[0_0_10px_#ff007f]"></div>
                    </div>
                </div>

                <div class="flex items-center justify-center space-x-6">
                    <button id="prev-btn" class="bg-[#150329] hover:bg-purple-900 border border-purple-700 text-purple-300 w-12 h-12 rounded-lg flex items-center justify-center transition shadow-[0_0_10px_rgba(157,78,221,0.2)] glitch-hover">
                        <i class="fa-solid fa-backward-step"></i>
                    </button>
                    <button id="play-pause-btn" class="bg-purple-700 hover:bg-purple-600 border border-purple-300 text-white w-18 h-18 rounded-xl flex items-center justify-center text-2xl transition shadow-[0_0_25px_#9d4edd] glitch-hover transform hover:scale-105 active:scale-95">
                        <i id="play-icon" class="fa-solid fa-play ml-1"></i>
                    </button>
                    <button id="next-btn" class="bg-[#150329] hover:bg-purple-900 border border-purple-700 text-purple-300 w-12 h-12 rounded-lg flex items-center justify-center transition shadow-[0_0_10px_rgba(157,78,221,0.2)] glitch-hover">
                        <i class="fa-solid fa-forward-step"></i>
                    </button>
                </div>
            </div>

            <!-- Playlist Drawer -->
            <div class="mt-12 pt-6 border-t border-purple-900/60 relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-md font-bold tracking-widest text-purple-300 flex items-center uppercase">
                        <i class="fa-solid fa-database mr-2 text-pink-500"></i> BUFFER PLAYLIST [<?php echo count($playlist); ?> FILES]
                    </h3>
                    <span class="text-xs text-purple-400 tracking-wider">SOURCE: @actually_purple</span>
                </div>
                <div class="max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                    <?php if (empty($playlist)): ?>
                        <div class="text-center py-8 text-purple-500/80 text-sm tracking-wider border border-dashed border-purple-900 rounded-lg">
                            <i class="fa-solid fa-triangle-exclamation text-xl mb-2 text-pink-500 block"></i>
                            BUFFER EMPTY. RUN TELEGRAM DOWNLOADER TO FETCH MUSIC.
                        </div>
                    <?php else: ?>
                        <?php foreach ($playlist as $index => $track): ?>
                            <div class="playlist-item flex items-center justify-between p-3 rounded-lg bg-[#06000c] hover:bg-purple-950/60 border border-purple-900/60 hover:border-purple-500 cursor-pointer transition group"
                                 data-index="<?php echo $index; ?>"
                                 data-url="<?php echo htmlspecialchars($track['url']); ?>"
                                 data-title="<?php echo htmlspecialchars($track['title']); ?>">
                                <div class="flex items-center space-x-3 truncate">
                                    <span class="text-xs text-purple-500 font-mono w-6 text-right group-hover:text-pink-400">#<?php echo sprintf('%02d', $index + 1); ?></span>
                                    <span class="text-sm font-medium text-purple-200 truncate group-hover:text-white"><?php echo htmlspecialchars($track['title']); ?></span>
                                </div>
                                <span class="text-xs text-purple-400 font-mono flex items-center group-hover:text-pink-400">
                                    <i class="fa-solid fa-play text-[10px] mr-1.5"></i>EXEC
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#0f021f] border-t border-purple-900/60 py-4 text-center text-xs text-purple-400 tracking-widest">
        // TELEGRAM GLITCH RADIO &copy; <?php echo date('Y'); ?> //
    </footer>

    <!-- Player JavaScript -->
    <script>
        const playlist = <?php echo json_encode($playlist); ?>;
        const audio = document.getElementById('audio-player');
        const playPauseBtn = document.getElementById('play-pause-btn');
        const playIcon = document.getElementById('play-icon');
        const nowPlayingTitle = document.getElementById('now-playing-title');
        const stationStatus = document.getElementById('station-status');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const playlistItems = document.querySelectorAll('.playlist-item');
        const progressBar = document.getElementById('progress-bar');
        const progressContainer = document.getElementById('progress-container');
        const currentTimeEl = document.getElementById('current-time');
        const totalDurationEl = document.getElementById('total-duration');

        let currentTrackIndex = 0;
        let isPlaying = false;

        function formatTime(seconds) {
            if (isNaN(seconds)) return "00:00";
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function loadTrack(index) {
            if (playlist.length === 0) return;
            currentTrackIndex = index;
            const track = playlist[index];
            audio.src = track.url;
            nowPlayingTitle.textContent = track.title;
            stationStatus.textContent = `[STREAMING BUFFER ${index + 1} / ${playlist.length}]`;

            playlistItems.forEach((item, idx) => {
                if (idx === index) {
                    item.classList.add('bg-purple-950/80', 'border-purple-400', 'shadow-[0_0_10px_rgba(157,78,221,0.4)]');
                    item.classList.remove('bg-[#06000c]');
                } else {
                    item.classList.remove('bg-purple-950/80', 'border-purple-400', 'shadow-[0_0_10px_rgba(157,78,221,0.4)]');
                    item.classList.add('bg-[#06000c]');
                }
            });
        }

        function playTrack() {
            if (playlist.length === 0) return;
            audio.play().then(() => {
                isPlaying = true;
                playIcon.className = "fa-solid fa-pause";
                stationStatus.textContent = "[SIGNAL ACTIVE // PLAYING]";
            }).catch(e => {
                console.error("Playback error:", e);
                stationStatus.textContent = "[CLICK PLAY TO INITIALIZE]";
            });
        }

        function pauseTrack() {
            audio.pause();
            isPlaying = false;
            playIcon.className = "fa-solid fa-play ml-1";
            stationStatus.textContent = "[SYSTEM PAUSED]";
        }

        playPauseBtn.addEventListener('click', () => {
            if (playlist.length === 0) return;
            if (!audio.src) {
                loadTrack(0);
            }
            if (isPlaying) {
                pauseTrack();
            } else {
                playTrack();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (playlist.length === 0) return;
            currentTrackIndex = (currentTrackIndex - 1 + playlist.length) % playlist.length;
            loadTrack(currentTrackIndex);
            playTrack();
        });

        nextBtn.addEventListener('click', () => {
            if (playlist.length === 0) return;
            currentTrackIndex = (currentTrackIndex + 1) % playlist.length;
            loadTrack(currentTrackIndex);
            playTrack();
        });

        playlistItems.forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.getAttribute('data-index'));
                loadTrack(index);
                playTrack();
            });
        });

        audio.addEventListener('timeupdate', () => {
            if (audio.duration) {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressBar.style.width = `${percent}%`;
                currentTimeEl.textContent = formatTime(audio.currentTime);
                totalDurationEl.textContent = formatTime(audio.duration);
            }
        });

        progressContainer.addEventListener('click', (e) => {
            if (!audio.duration) return;
            const rect = progressContainer.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / rect.width;
            audio.currentTime = pos * audio.duration;
        });

        audio.addEventListener('ended', () => {
            if (playlist.length > 0) {
                currentTrackIndex = (currentTrackIndex + 1) % playlist.length;
                loadTrack(currentTrackIndex);
                playTrack();
            }
        });

        if (playlist.length > 0) {
            loadTrack(0);
        }
    </script>
</body>
</html>
