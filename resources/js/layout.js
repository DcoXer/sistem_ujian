const statusLabelMap = {
    'user-created': 'User berhasil dibuat.',
    'user-updated': 'User berhasil diupdate.',
    'user-deleted': 'User berhasil dihapus.',
    'exam-created': 'Exam berhasil dibuat sebagai draft.',
    'question-added': 'Soal berhasil ditambahkan.',
    'exam-published': 'Exam berhasil dipublish.',
    'profile-updated': 'Profil berhasil diperbarui.',
    'password-updated': 'Password berhasil diperbarui.',
    'exam-expired': 'Waktu ujian sudah habis. Jawaban dikunci otomatis.',
    'attempt-force-submitted': 'Attempt berhasil di-force submit.',
    'attempt-reopened': 'Attempt berhasil di-reopen.',
    'attempt-issue-logged': 'Kendala teknis berhasil dicatat.',
    'manual-score-updated': 'Nilai manual berhasil disimpan.',
    'exam-submitted': 'Jawaban Berhasil di Submit',
    'question-updated': 'Soal berhasil diperbarui.',
    'exam-deleted': 'Exam draft berhasil dihapus.',
};

const parseJsonDataAttribute = (raw, fallback = {}) => {
    if (!raw) return fallback;
    try {
        return JSON.parse(raw);
    } catch {
        return fallback;
    }
};

const createToastModule = (toastContainer) => {
    const show = (type, message) => {
        if (!toastContainer || !message) return;

        const el = document.createElement('div');
        const styleByType = type === 'error'
            ? 'border-rose-200 bg-rose-50 text-rose-800'
            : 'border-emerald-200 bg-emerald-50 text-emerald-800';

        el.className = `pointer-events-auto rounded-xl border px-4 py-3 text-sm shadow-lg transition ${styleByType}`;
        el.textContent = message;
        toastContainer.appendChild(el);

        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-x-2');
            setTimeout(() => el.remove(), 220);
        }, 3200);
    };

    return { show };
};

const createConfirmModule = ({
    modal,
    backdrop,
    titleEl,
    messageEl,
    cancelBtn,
    okBtn,
}) => {
    const open = ({ title, message, onConfirm }) => {
        if (!modal || !backdrop || !titleEl || !messageEl || !cancelBtn || !okBtn) {
            return;
        }

        titleEl.textContent = title || 'Konfirmasi';
        messageEl.textContent = message || 'Apakah Anda yakin?';
        modal.classList.remove('hidden');

        const close = () => modal.classList.add('hidden');
        const handleCancel = () => {
            close();
            cleanup();
        };
        const handleConfirm = () => {
            close();
            cleanup();
            onConfirm();
        };
        const cleanup = () => {
            cancelBtn.removeEventListener('click', handleCancel);
            backdrop.removeEventListener('click', handleCancel);
            okBtn.removeEventListener('click', handleConfirm);
        };

        cancelBtn.addEventListener('click', handleCancel);
        backdrop.addEventListener('click', handleCancel);
        okBtn.addEventListener('click', handleConfirm);
    };

    return { open };
};

const initSidebar = () => {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const openBtn = document.getElementById('sidebar-open');
    const closeBtn = document.getElementById('sidebar-close');

    if (!sidebar || !backdrop || !openBtn || !closeBtn) return;

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    };

    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            backdrop.classList.add('hidden');
            sidebar.classList.remove('-translate-x-full');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });
};

const initConfirmForms = (confirm) => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            confirm.open({
                title: form.dataset.confirmTitle || 'Konfirmasi',
                message: form.dataset.confirmMessage || 'Apakah Anda yakin?',
                onConfirm: () => form.submit(),
            });
        });
    });
};

const createSyncedClock = (root) => {
    // server_now_ms is seed only. Do NOT use for domain decisions.
    const serverNowMs = Number(root?.dataset?.serverNowMs || 0);
    let offsetMs = 0;
    if (serverNowMs && !Number.isNaN(serverNowMs)) {
        offsetMs = serverNowMs - Date.now();
    }

    return {
        now: () => Date.now() + offsetMs,
        sync: (nextServerNowMs) => {
            const value = Number(nextServerNowMs || 0);
            if (!value || Number.isNaN(value)) return;
            offsetMs = value - Date.now();
        },
    };
};

const initLiveClock = (nowProvider) => {
    const clock = document.getElementById('app-live-clock');
    if (!clock) return;

    const timezone = clock.dataset.timezone || 'Asia/Jakarta';
    const formatter = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: timezone,
    });

    const tick = () => {
        const parts = formatter.formatToParts(new Date(nowProvider()));
        const map = Object.fromEntries(parts.map((part) => [part.type, part.value]));
        const dayName = map.weekday ? map.weekday.charAt(0).toUpperCase() + map.weekday.slice(1) : '';
        clock.textContent = `${dayName}, ${map.day} ${map.month} ${map.year} ${map.hour}:${map.minute}:${map.second} WIB`;
    };

    tick();
    window.setInterval(tick, 1000);
};

const initPesertaExamRealtimeState = (root, syncedClock) => {
    const container = document.getElementById('peserta-exam-list');
    if (!container) return;

    const stateUrl = container.dataset.realtimeStateUrl;
    if (!stateUrl) return;

    const cards = container.querySelectorAll('[data-exam-card]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const actionMap = parseJsonDataAttribute(root?.dataset?.examUiActions, {});

    const setStartButtonState = (button, disabled) => {
        if (!button) return;
        button.disabled = disabled;
        button.classList.toggle('cursor-not-allowed', disabled);
        button.classList.toggle('bg-slate-400', disabled);
        button.classList.toggle('bg-indigo-600', !disabled);
        button.classList.toggle('hover:bg-indigo-700', !disabled);
    };

    const applyMessage = (el, tone, text) => {
        if (!el) return;
        if (!text) {
            el.classList.add('hidden');
            el.textContent = '';
            return;
        }
        el.classList.remove('hidden', 'text-amber-700', 'text-rose-700', 'text-slate-600');
        el.classList.add(tone === 'amber' ? 'text-amber-700' : (tone === 'rose' ? 'text-rose-700' : 'text-slate-600'));
        el.textContent = text;
    };

    const applyExamState = (card, examState) => {
        const action = examState.action || '';
        card.dataset.currentAction = action;
        card.dataset.attemptStatus = examState.attempt_status || '';

        const attemptStatusLabel = card.querySelector('[data-attempt-status-label]');
        if (attemptStatusLabel) {
            attemptStatusLabel.textContent = examState.attempt_status || 'belum mulai';
        }

        const startButton = card.querySelector('[data-start-button]');
        const startMessage = card.querySelector('[data-start-message]');
        const continueLink = card.querySelector('[data-continue-link]');
        const continueDisabled = card.querySelector('[data-continue-disabled]');
        const continueMessage = card.querySelector('[data-continue-expired-msg]');
        const resultLink = card.querySelector('[data-result-link]');

        if (startButton) setStartButtonState(startButton, action !== actionMap.start_enabled);

        if (continueLink && continueDisabled) {
            const showContinue = action === actionMap.continue_enabled;
            const showContinueDisabled = action === actionMap.continue_disabled || action === actionMap.waiting_result;
            continueLink.classList.toggle('hidden', !showContinue);
            continueDisabled.classList.toggle('hidden', !showContinueDisabled);
            if (showContinueDisabled) {
                continueDisabled.textContent = action === actionMap.waiting_result ? 'Menunggu Hasil' : 'Lanjut Ujian';
            }
        }

        if (resultLink) {
            resultLink.classList.toggle('hidden', action !== actionMap.result);
        }
        if (continueMessage) {
            applyMessage(
                continueMessage,
                examState.message_tone || '',
                (action === actionMap.continue_disabled || action === actionMap.waiting_result) ? (examState.message || '') : ''
            );
        }
        if (startMessage) {
            applyMessage(
                startMessage,
                examState.message_tone || '',
                action === actionMap.start_disabled ? (examState.message || '') : ''
            );
        }
    };

    const fetchAndApply = async () => {
        const response = await fetch(stateUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;
        const payload = await response.json();

        syncedClock.sync(payload.server_now_ms);

        const exams = Array.isArray(payload.exams) ? payload.exams : [];
        exams.forEach((examState) => {
            const card = container.querySelector(`[data-exam-id="${examState.id}"]`);
            if (!card) return;
            applyExamState(card, examState);
        });
    };

    fetchAndApply();
    window.setInterval(() => {
        fetchAndApply().catch(() => {});
    }, 30000);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            fetchAndApply().catch(() => {});
        }
    });
};

const initAdminDashboardRealtime = (root, syncedClock) => {
    const trigger = document.querySelector('[data-admin-dashboard-realtime-url]');
    if (!trigger) return;

    const realtimeUrl = trigger.dataset.adminDashboardRealtimeUrl;
    if (!realtimeUrl) return;

    const examsContainer = document.querySelector('[data-admin-latest-exams]');
    if (!examsContainer) return;

    const formatDate = (value) => {
        if (!value) return '-';
        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta',
        }).format(new Date(value));
    };

    const renderLatestExams = (items) => {
        examsContainer.innerHTML = '';
        if (!items.length) {
            const p = document.createElement('p');
            p.className = 'text-sm text-slate-500';
            p.textContent = 'Belum ada data ujian.';
            examsContainer.appendChild(p);
            return;
        }

        items.forEach((exam) => {
            const row = document.createElement('article');
            row.className = 'rounded-xl border border-slate-200 p-4';
            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-2';
            const left = document.createElement('div');
            const title = document.createElement('p');
            title.className = 'text-sm font-semibold text-slate-900';
            title.textContent = exam.title || '-';
            const schedule = document.createElement('p');
            schedule.className = 'mt-2 text-xs text-slate-500';
            schedule.textContent = `${formatDate(exam.start_at)} - ${formatDate(exam.end_at)}`;

            const badge = document.createElement('span');
            badge.className = 'rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase text-slate-700';
            badge.textContent = exam.status || '-';

            top.appendChild(title);
            top.appendChild(badge);
            left.appendChild(top);
            left.appendChild(schedule);
            row.appendChild(left);
            examsContainer.appendChild(row);
        });
    };

    const fetchAndRender = async () => {
        const response = await fetch(realtimeUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const payload = await response.json();
        syncedClock.sync(payload.server_now_ms);
        renderLatestExams(Array.isArray(payload.latest_exams) ? payload.latest_exams : []);
    };

    fetchAndRender().catch(() => {});
    window.setInterval(() => {
        fetchAndRender().catch(() => {});
    }, 20000);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            fetchAndRender().catch(() => {});
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('app-layout-root');
    if (!root) return;

    const syncedClock = createSyncedClock(root);

    initSidebar();
    initLiveClock(syncedClock.now);
    initPesertaExamRealtimeState(root, syncedClock);
    initAdminDashboardRealtime(root, syncedClock);

    const popupStatus = root.dataset.popupStatus || '';
    const popupError = root.dataset.popupError || '';

    const toast = createToastModule(document.getElementById('app-toast-container'));
    const confirm = createConfirmModule({
        modal: document.getElementById('app-confirm-modal'),
        backdrop: document.getElementById('app-confirm-backdrop'),
        titleEl: document.getElementById('app-confirm-title'),
        messageEl: document.getElementById('app-confirm-message'),
        cancelBtn: document.getElementById('app-confirm-cancel'),
        okBtn: document.getElementById('app-confirm-ok'),
    });

    initConfirmForms(confirm);

    if (popupStatus) {
        toast.show('success', statusLabelMap[popupStatus] || popupStatus);
    }

    if (popupError) {
        toast.show('error', popupError);
    }
});
