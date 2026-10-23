/**
 * ezyTailor Web Application - Core App Engine
 * Multi-Theme, Voice Note Recorder (Web Audio API), Mobile Drawer, FAB, Modals & Alerts
 */

// Theme Engine
const Themes = {
    current: localStorage.getItem('ezytailor_theme') || 'olive',

    init() {
        this.apply(this.current, false);
        this.bindEvents();
    },

    apply(themeName, persist = true) {
        document.documentElement.setAttribute('data-theme', themeName);
        this.current = themeName;
        localStorage.setItem('ezytailor_theme', themeName);

        // Highlight selected theme pill/radio
        document.querySelectorAll('.theme-option').forEach(el => {
            el.classList.toggle('active', el.dataset.theme === themeName);
        });

        if (persist) {
            fetch((window.APP_BASE_URL || '') + '/api/settings.php?action=set_theme&theme=' + themeName).catch(() => {});
            Toast.show('Theme changed to ' + themeName.toUpperCase(), 'info');
        }
    },

    bindEvents() {
        document.addEventListener('click', (e) => {
            const themeBtn = e.target.closest('[data-set-theme]');
            if (themeBtn) {
                e.preventDefault();
                const theme = themeBtn.getAttribute('data-set-theme');
                this.apply(theme, true);
            }
        });
    }
};

// Toast Notifications
const Toast = {
    show(message, type = 'success', duration = 3500) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            if (document.documentElement.getAttribute('dir') === 'rtl') {
                container.style.right = 'auto';
                container.style.left = '20px';
            }
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.cssText = `
            min-width: 280px;
            max-width: 400px;
            background: #fff;
            color: #1F2937;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            border-left: 5px solid ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
            pointer-events: auto;
            animation: slideInToast 0.3s ease;
        `;

        if (document.documentElement.getAttribute('dir') === 'rtl') {
            toast.style.borderLeft = 'none';
            toast.style.borderRight = `5px solid ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'}`;
        }

        const iconClass = type === 'success' ? 'bi-check-circle-fill text-success' : type === 'error' ? 'bi-exclamation-octagon-fill text-danger' : 'bi-info-circle-fill text-primary';
        toast.innerHTML = `<i class="bi ${iconClass}" style="font-size:1.3rem;"></i> <span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
};

// Web Audio API & MediaRecorder for Tailor Voice Notes
const VoiceRecorder = {
    mediaRecorder: null,
    audioChunks: [],
    recordedBlob: null,
    recordedAudioUrl: null,
    isRecording: false,
    timerInterval: null,
    secondsElapsed: 0,
    targetInputSelector: null,
    targetPlayerSelector: null,

    init() {
        this.bindEvents();
    },

    bindEvents() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.voice-record-toggle-btn');
            if (btn) {
                e.preventDefault();
                const targetInput = btn.getAttribute('data-target-input') || '#voice_note_file';
                const targetPlayer = btn.getAttribute('data-target-player') || '#voice_note_player';
                this.toggleRecording(btn, targetInput, targetPlayer);
            }
        });
    },

    async toggleRecording(btn, targetInputSelector, targetPlayerSelector) {
        this.targetInputSelector = targetInputSelector;
        this.targetPlayerSelector = targetPlayerSelector;

        if (this.isRecording) {
            this.stopRecording(btn);
        } else {
            await this.startRecording(btn);
        }
    },

    async startRecording(btn) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.audioChunks = [];
            
            // Choose supported mimeType
            let mimeType = 'audio/webm';
            if (!MediaRecorder.isTypeSupported('audio/webm')) {
                if (MediaRecorder.isTypeSupported('audio/mp4')) {
                    mimeType = 'audio/mp4';
                } else {
                    mimeType = '';
                }
            }

            this.mediaRecorder = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.audioChunks.push(event.data);
                }
            };

            this.mediaRecorder.onstop = () => {
                this.recordedBlob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType || 'audio/webm' });
                this.recordedAudioUrl = URL.createObjectURL(this.recordedBlob);

                // Update audio player preview
                const player = document.querySelector(this.targetPlayerSelector);
                if (player) {
                    player.src = this.recordedAudioUrl;
                    player.style.display = 'block';
                }

                // Stop tracks
                stream.getTracks().forEach(track => track.stop());

                // Upload blob to server via AJAX immediately or stash
                this.uploadAudioBlob(this.recordedBlob);
            };

            this.mediaRecorder.start();
            this.isRecording = true;

            // UI feedback
            btn.classList.add('recording');
            btn.innerHTML = `<i class="bi bi-stop-circle-fill"></i> <span class="voice-timer">00:00</span> - Click to Stop`;

            this.secondsElapsed = 0;
            this.timerInterval = setInterval(() => {
                this.secondsElapsed++;
                const mins = String(Math.floor(this.secondsElapsed / 60)).padStart(2, '0');
                const secs = String(this.secondsElapsed % 60).padStart(2, '0');
                const timerEl = btn.querySelector('.voice-timer');
                if (timerEl) timerEl.textContent = `${mins}:${secs}`;
            }, 1000);

            Toast.show('Recording audio note... speak clearly', 'info', 2000);

        } catch (err) {
            console.error('Audio recording error:', err);
            Toast.show('Microphone access denied or not supported on this browser.', 'error');
        }
    },

    stopRecording(btn) {
        if (this.mediaRecorder && this.isRecording) {
            this.mediaRecorder.stop();
            this.isRecording = false;
            clearInterval(this.timerInterval);

            btn.classList.remove('recording');
            btn.innerHTML = `<i class="bi bi-mic-fill"></i> Record Voice Note (Click to Re-record)`;
            Toast.show('Audio note captured successfully!', 'success');
        }
    },

    uploadAudioBlob(blob) {
        const formData = new FormData();
        const ext = blob.type.includes('mp4') ? 'mp4' : 'webm';
        formData.append('audio_file', blob, `voice_${Date.now()}.${ext}`);

        fetch((window.APP_BASE_URL || '') + '/api/upload.php?type=audio', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.filename) {
                const hiddenInput = document.querySelector(this.targetInputSelector);
                if (hiddenInput) {
                    hiddenInput.value = data.filename;
                }
                Toast.show('Audio voice note saved!', 'success');
            } else {
                Toast.show(data.message || 'Error uploading voice note', 'error');
            }
        })
        .catch(err => {
            console.error('Upload error:', err);
        });
    }
};

// UI Helpers (FAB, Mobile Drawer, Form helpers)
document.addEventListener('DOMContentLoaded', () => {
    Themes.init();
    VoiceRecorder.init();

    // Mobile Sidebar Drawer Toggle & Backdrop
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.querySelector('.app-sidebar');
    let backdrop = document.getElementById('sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'sidebar-backdrop';
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    if (mobileBtn && sidebar) {
        const toggleSidebar = (show) => {
            if (show) {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
            } else {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }
        };

        mobileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar(!sidebar.classList.contains('show'));
        });

        backdrop.addEventListener('click', () => {
            toggleSidebar(false);
        });

        // Close sidebar on navigation link click on mobile
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 991) {
                    toggleSidebar(false);
                }
            });
        });
    }

    // Speed Dial / FAB Toggle
    const fabMain = document.querySelector('.fab-btn-main');
    const fabActions = document.querySelector('.fab-actions');
    if (fabMain && fabActions) {
        fabMain.addEventListener('click', (e) => {
            e.stopPropagation();
            fabActions.classList.toggle('show');
            fabMain.querySelector('i').classList.toggle('bi-plus');
            fabMain.querySelector('i').classList.toggle('bi-x');
        });

        document.addEventListener('click', () => {
            if (fabActions.classList.contains('show')) {
                fabActions.classList.remove('show');
                fabMain.querySelector('i').classList.add('bi-plus');
                fabMain.querySelector('i').classList.remove('bi-x');
            }
        });
    }
});
