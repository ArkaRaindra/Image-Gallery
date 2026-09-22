//
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function formatCount(n) {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (n >= 1_000) return (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'k';
    return String(n);
}

function categoryColor(category) {
    return {
        artist: '#f87171',
        character: '#4ade80',
        copyright: '#c084fc',
        meta: '#fbbf24',
    }[category] || '#38bdf8';
}

function setupTagAutocomplete(input) {
    const wrapper = input.closest('[data-tag-autocomplete-wrapper]');
    if (!wrapper) return;

    const list = document.createElement('ul');
    list.className = 'absolute left-0 right-0 mt-1 bg-gray-900 border border-gray-700 rounded shadow-lg max-h-80 overflow-y-auto text-sm z-30 hidden';
    wrapper.appendChild(list);

    let activeIndex = -1;
    let currentItems = [];

    function currentToken() {
        const parts = input.value.split(' ');
        return parts[parts.length - 1] || '';
    }

    function replaceToken(newTag) {
        const parts = input.value.split(' ');
        const prefix = parts[parts.length - 1].startsWith('-') ? '-' : '';
        parts[parts.length - 1] = prefix + newTag;
        input.value = parts.join(' ') + ' ';
        list.classList.add('hidden');
        input.focus();
    }

    function render(tags, query) {
        list.innerHTML = '';
        currentItems = tags;
        activeIndex = -1;

        if (!tags.length) {
            list.classList.add('hidden');
            return;
        }

        tags.forEach((tag, i) => {
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-3 px-3 py-1.5 cursor-pointer hover:bg-gray-800';
            li.dataset.index = i;

            const nameSpan = document.createElement('span');
            nameSpan.style.color = categoryColor(tag.category);

            const lowerName = tag.name.toLowerCase();
            const lowerQuery = query.toLowerCase();
            if (lowerQuery && lowerName.startsWith(lowerQuery)) {
                const bold = document.createElement('b');
                bold.textContent = tag.name.slice(0, query.length);
                nameSpan.appendChild(bold);
                nameSpan.appendChild(document.createTextNode(tag.name.slice(query.length)));
            } else {
                nameSpan.textContent = tag.name;
            }

            const countSpan = document.createElement('span');
            countSpan.className = 'text-gray-500 text-xs shrink-0';
            countSpan.textContent = formatCount(tag.post_count);

            li.appendChild(nameSpan);
            li.appendChild(countSpan);

            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                replaceToken(tag.name);
            });

            list.appendChild(li);
        });

        list.classList.remove('hidden');
    }

    function highlight() {
        [...list.children].forEach((li, i) => {
            li.classList.toggle('bg-gray-800', i === activeIndex);
        });
    }

    const fetchSuggestions = debounce(async () => {
        const token = currentToken().replace(/^-/, '');
        if (!token) {
            list.classList.add('hidden');
            return;
        }
        try {
            const res = await fetch('/tags/autocomplete?q=' + encodeURIComponent(token));
            const data = await res.json();
            render(data, token);
        } catch (e) {
            list.classList.add('hidden');
        }
    }, 200);

    input.addEventListener('input', fetchSuggestions);

    input.addEventListener('keydown', (e) => {
        if (list.classList.contains('hidden')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, currentItems.length - 1);
            highlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            highlight();
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            replaceToken(currentItems[activeIndex].name);
        } else if (e.key === 'Escape') {
            list.classList.add('hidden');
        }
    });

    input.addEventListener('blur', () => {
        setTimeout(() => list.classList.add('hidden'), 150);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-tag-autocomplete]').forEach(setupTagAutocomplete);
});

function applyVotedState(widget, direction) {
    widget.dataset.voted = direction ?? '';
    widget.querySelectorAll('[data-vote]').forEach((btn) => {
        const isActive = direction === btn.dataset.vote;
        const activeColor = btn.dataset.vote === 'up' ? 'text-green-400' : 'text-red-400';
        const hoverColor = btn.dataset.vote === 'up' ? 'hover:text-green-400' : 'hover:text-red-400';
        btn.classList.remove('text-green-400', 'text-red-400', 'hover:text-green-400', 'hover:text-red-400');
        btn.classList.add(isActive ? activeColor : hoverColor);
    });
}

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-vote]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const widget = btn.closest('[data-vote-widget]');
    const postId = widget.dataset.postId;
    const direction = btn.dataset.vote;

    try {
        const res = await fetch(`/posts/${postId}/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ direction }),
        });
        const data = await res.json();
        widget.querySelectorAll('[data-score]').forEach((el) => (el.textContent = data.score));
        applyVotedState(widget, data.voted);
    } catch (err) {
        console.error('Vote failed', err);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vote-widget]').forEach((widget) => {
        if (widget.dataset.voted) {
            applyVotedState(widget, widget.dataset.voted);
        }
    });
});

function applyCommentVotedState(widget, direction) {
    widget.dataset.voted = direction ?? '';
    widget.querySelectorAll('[data-comment-vote]').forEach((btn) => {
        const isActive = direction === btn.dataset.commentVote;
        const activeColor = btn.dataset.commentVote === 'up' ? 'text-green-400' : 'text-red-400';
        const hoverColor = btn.dataset.commentVote === 'up' ? 'hover:text-green-400' : 'hover:text-red-400';
        btn.classList.remove('text-green-400', 'text-red-400', 'hover:text-green-400', 'hover:text-red-400');
        btn.classList.add(isActive ? activeColor : hoverColor);
    });
}

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-comment-vote]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const widget = btn.closest('[data-comment-vote-widget]');
    const commentId = widget.dataset.commentId;
    const direction = btn.dataset.commentVote;

    try {
        const res = await fetch(`/comments/${commentId}/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ direction }),
        });
        const data = await res.json();
        widget.querySelectorAll('[data-comment-score]').forEach((el) => (el.textContent = data.score));
        applyCommentVotedState(widget, data.voted);
    } catch (err) {
        console.error('Comment vote failed', err);
    }
});

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.reply-toggle-btn');
    if (!btn) return;

    const commentId = btn.dataset.commentId;
    const form = document.querySelector(`.reply-form[data-comment-id="${commentId}"]`);
    if (!form) return;

    form.classList.toggle('hidden');

    if (!form.classList.contains('hidden')) {
        const textarea = form.querySelector('textarea');
        if (!textarea.value) {
            const mentionName = btn.dataset.username.trim().replace(/\s+/g, '_');
            textarea.value = `@${mentionName} `;
        }
        textarea.focus();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-comment-vote-widget]').forEach((widget) => {
        if (widget.dataset.voted) {
            applyCommentVotedState(widget, widget.dataset.voted);
        }
    });
});

document.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.view-replies-btn');
    if (viewBtn) {
        const wrapper = document.querySelector(`.replies-wrapper[data-comment-id="${viewBtn.dataset.commentId}"]`);
        if (!wrapper) return;

        const isHidden = wrapper.classList.toggle('hidden');
        viewBtn.textContent = isHidden
            ? `View ${viewBtn.dataset.count} ${viewBtn.dataset.count == 1 ? 'reply' : 'replies'}`
            : 'Hide replies';
        return;
    }

    const moreBtn = e.target.closest('.view-more-replies-btn');
    if (moreBtn) {
        const wrapper = moreBtn.closest('.replies-wrapper');
        wrapper.querySelectorAll('.extra-reply').forEach((el) => el.classList.remove('hidden'));
        moreBtn.remove();
    }
});

function setupMentionAutocomplete(textarea) {
    const list = document.createElement('ul');
    list.className = 'fixed z-50 hidden bg-gray-900 border border-gray-700 rounded shadow-lg max-h-56 overflow-y-auto text-sm';
    document.body.appendChild(list);

    let currentItems = [];
    let activeIndex = -1;
    let mentionStart = -1;

    function positionList() {
        const rect = textarea.getBoundingClientRect();
        list.style.left = rect.left + 'px';
        list.style.top = (rect.bottom + window.scrollY + 4) + 'px';
        list.style.width = Math.min(rect.width, 240) + 'px';
    }

    function closeList() {
        list.classList.add('hidden');
        mentionStart = -1;
    }

    function currentMentionWord() {
        const cursor = textarea.selectionStart;
        const text = textarea.value.slice(0, cursor);
        const match = text.match(/@([a-zA-Z0-9_.]*)$/);
        if (!match) return null;
        mentionStart = cursor - match[0].length;
        return match[1];
    }

    function render(users) {
        currentItems = users;
        activeIndex = -1;
        list.innerHTML = '';

        if (!users.length) {
            closeList();
            return;
        }

        users.forEach((user) => {
            const li = document.createElement('li');
            li.className = 'px-3 py-1.5 cursor-pointer hover:bg-gray-800 text-sky-400';
            li.textContent = '@' + user.name;
            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                insertMention(user.name);
            });
            list.appendChild(li);
        });

        positionList();
        list.classList.remove('hidden');
    }

    function highlight() {
        [...list.children].forEach((li, i) => li.classList.toggle('bg-gray-800', i === activeIndex));
    }

        function insertMention(username) {
        const mentionName = username.trim().replace(/\s+/g, '_');
        const cursor = textarea.selectionStart;
        const before = textarea.value.slice(0, mentionStart);
        const after = textarea.value.slice(cursor);
        textarea.value = before + '@' + mentionName + ' ' + after;
        const newPos = (before + '@' + mentionName + ' ').length;
        textarea.setSelectionRange(newPos, newPos);
        textarea.focus();
        closeList();
    }

    const fetchUsers = debounce(async (query) => {
        try {
            const res = await fetch('/users/autocomplete?q=' + encodeURIComponent(query));
            const data = await res.json();
            render(data);
        } catch (e) {
            closeList();
        }
    }, 200);

    textarea.addEventListener('input', () => {
        const word = currentMentionWord();
        if (word === null || word.length === 0) {
            closeList();
            return;
        }
        fetchUsers(word);
    });

    textarea.addEventListener('keydown', (e) => {
        if (list.classList.contains('hidden')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, currentItems.length - 1);
            highlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            highlight();
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            insertMention(currentItems[activeIndex].name);
        } else if (e.key === 'Escape') {
            closeList();
        }
    });

    textarea.addEventListener('blur', () => setTimeout(closeList, 150));

    window.addEventListener('scroll', () => {
        if (!list.classList.contains('hidden')) positionList();
    }, true);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea[name="body"]').forEach(setupMentionAutocomplete);
});

document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-toggle-btn');
    if (editBtn) {
        const form = document.querySelector(`.edit-form[data-comment-id="${editBtn.dataset.commentId}"]`);
        form?.classList.toggle('hidden');
        return;
    }

    const cancelBtn = e.target.closest('.cancel-edit-btn');
    if (cancelBtn) {
        cancelBtn.closest('.edit-form')?.classList.add('hidden');
    }
});

function formatDuration(totalSeconds) {
    if (!isFinite(totalSeconds) || totalSeconds < 0) return null;

    const rounded = Math.round(totalSeconds);
    const minutes = Math.floor(rounded / 60);
    const seconds = rounded % 60;

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

function revealDurationBadge(el, text) {
    const label = el.querySelector('[data-duration-text]');
    if (label) label.textContent = text;
    el.classList.remove('hidden');
    el.classList.add('flex');
}

function loadVideoDuration(el) {
    const probe = document.createElement('video');
    probe.preload = 'metadata';
    probe.muted = true;
    probe.src = el.dataset.src;

    probe.addEventListener('loadedmetadata', () => {
        const text = formatDuration(probe.duration);
        if (text) revealDurationBadge(el, text);
    }, { once: true });
}

function parseGifFrameDelays(buffer) {
    const bytes = new Uint8Array(buffer);
    const delays = [];

    for (let i = 0; i < bytes.length - 8; i++) {
        // Graphic Control Extension block: 0x21 0xF9 0x04 <flags> <delayLo> <delayHi> ...
        if (bytes[i] === 0x21 && bytes[i + 1] === 0xf9 && bytes[i + 2] === 0x04) {
            const raw = bytes[i + 4] | (bytes[i + 5] << 8);
            delays.push(raw < 2 ? 10 : raw); // delays under 20ms render as ~100ms in browsers
        }
    }

    return delays;
}

async function loadGifDuration(el) {
    try {
        const res = await fetch(el.dataset.src);
        const buffer = await res.arrayBuffer();
        const delays = parseGifFrameDelays(buffer);

        if (!delays.length) return;

        const totalSeconds = delays.reduce((sum, d) => sum + d, 0) / 100;
        const text = formatDuration(totalSeconds);
        if (text) revealDurationBadge(el, text);
    } catch (err) {
        // Leave the badge hidden if the gif can't be fetched/parsed.
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-duration-badge]').forEach((el) => {
        if (el.dataset.kind === 'video') {
            loadVideoDuration(el);
        } else if (el.dataset.kind === 'gif') {
            loadGifDuration(el);
        }
    });
});

document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-toggle-btn');
    if (editBtn) {
        const form = document.querySelector(`.edit-form[data-comment-id="${editBtn.dataset.commentId}"]`);
        form?.classList.toggle('hidden');
        return;
    }

    const cancelBtn = e.target.closest('.cancel-edit-btn');
    if (cancelBtn) {
        cancelBtn.closest('.edit-form')?.classList.add('hidden');
    }
});

const THUMB_HOVER_PANEL_GAP = 4;

// Computes the "object-fit: contain" box of a naturalWidth x naturalHeight
// media element inside a containerWidth x containerHeight box: the box's
// pixel width/height and its centered top/left offset.
function computeContainFit(containerWidth, containerHeight, naturalWidth, naturalHeight) {
    const scale = Math.min(containerWidth / naturalWidth, containerHeight / naturalHeight);
    const width = Math.max(1, Math.round(naturalWidth * scale));
    const height = Math.max(1, Math.round(naturalHeight * scale));

    return {
        width,
        height,
        left: Math.round((containerWidth - width) / 2),
        top: Math.round((containerHeight - height) / 2),
    };
}

function applyThumbFit(container) {
    const fit = container.querySelector('[data-thumb-fit]');
    const media = container.querySelector('[data-thumb-media]');
    if (!fit || !media) return;

    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;
    const naturalWidth = media.tagName === 'VIDEO' ? media.videoWidth : media.naturalWidth;
    const naturalHeight = media.tagName === 'VIDEO' ? media.videoHeight : media.naturalHeight;

    if (!containerWidth || !containerHeight || !naturalWidth || !naturalHeight) return;

    const box = computeContainFit(containerWidth, containerHeight, naturalWidth, naturalHeight);

    fit.style.width = box.width + 'px';
    fit.style.height = box.height + 'px';
    fit.style.left = box.left + 'px';
    fit.style.top = box.top + 'px';

    const group = container.closest('.group');
    const panel = group?.querySelector('[data-hover-panel]');
    if (panel) {
        panel.style.bottom = (group.offsetHeight - box.top + THUMB_HOVER_PANEL_GAP) + 'px';
    }
}

function setupThumbFit(container) {
    const media = container.querySelector('[data-thumb-media]');
    if (!media) return;

    const run = () => applyThumbFit(container);

    if (media.tagName === 'VIDEO') {
        if (media.readyState >= 1 && media.videoWidth) run();
        media.addEventListener('loadedmetadata', run, { once: true });
    } else {
        if (media.complete && media.naturalWidth) run();
        media.addEventListener('load', run, { once: true });
    }
}

// Empty (letterboxed) space inside the thumbnail box shouldn't navigate to
// the post — only clicks landing on the actual rendered picture should.
function bindThumbEmptyClickGuard(container) {
    container.addEventListener('click', (e) => {
        if (!e.target.closest('[data-thumb-fit]')) {
            e.preventDefault();
        }
    });
}

function bindThumbHoverPanel(fit) {
    const panel = fit.closest('.group')?.querySelector('[data-hover-panel]');
    if (!panel) return;

    fit.addEventListener('mouseenter', () => {
        panel.classList.remove('opacity-0', 'invisible', 'delay-200');
        panel.classList.add('opacity-100', 'visible', 'delay-75');
    });

    fit.addEventListener('mouseleave', () => {
        panel.classList.add('opacity-0', 'invisible', 'delay-200');
        panel.classList.remove('opacity-100', 'visible', 'delay-75');
    });
}

window.recomputeThumbFits = function () {
    document.querySelectorAll('[data-thumb-container]').forEach(applyThumbFit);
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-thumb-container]').forEach((container) => {
        setupThumbFit(container);
        bindThumbEmptyClickGuard(container);
    });
    document.querySelectorAll('[data-thumb-fit]').forEach(bindThumbHoverPanel);
});

window.addEventListener('resize', debounce(() => window.recomputeThumbFits(), 150));

// --- Post translation notes -------------------------------------------------
// Notes are rectangles (stored as % of the image) overlaid on a post's image,
// following the formatting rules at https://danbooru.donmai.us/wiki_pages/help:notes.
// The note itself stays empty; its translation only appears in a hover popup
// underneath it. Any viewer can drag a note to reposition it, but that's
// purely visual/in-memory — nothing is saved, so it resets on refresh.
// Only the post's uploader or an admin can add/edit/delete notes.

function stripTags(html) {
    const div = document.createElement('div');
    div.innerHTML = html || '';
    return div.textContent || '';
}

function initPostNotes() {
    const layer = document.querySelector('[data-notes-layer]');
    const mediaContainer = document.querySelector('[data-post-media-container]');
    const media = document.getElementById('post-image');
    if (!layer || !mediaContainer || !media || media.tagName === 'VIDEO') return;

    const canManage = layer.dataset.canManage === '1';
    const storeUrl = layer.dataset.storeUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const minimumNoteSize = 8;
    let notes = [];
    let notesVisible = true;
    let drawModeOn = false;
    let drawing = false;
    let drawStart = null;
    let drawBox = null;
    let suppressNextOutsideClick = false;
    let currentDialog = null;
    let currentDialogBox = null;

    try {
        notes = JSON.parse(layer.dataset.notes || '[]');
    } catch (err) {
        notes = [];
    }

    function jsonHeaders() {
        return {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };
    }

    function clamp(value, minimum, maximum) {
        return Math.min(maximum, Math.max(minimum, value));
    }

    function getBoxGeometry(box) {
        return {
            x: parseFloat(box.style.left) || 0,
            y: parseFloat(box.style.top) || 0,
            width: parseFloat(box.style.width) || 0,
            height: parseFloat(box.style.height) || 0,
        };
    }

    function clampGeometry(geometry) {
        const width = clamp(geometry.width || 0, 0.05, 100);
        const height = clamp(geometry.height || 0, 0.05, 100);
        const x = clamp(geometry.x || 0, 0, 100 - width);
        const y = clamp(geometry.y || 0, 0, 100 - height);

        return { x, y, width, height };
    }

    function setBoxGeometry(box, geometry) {
        const clamped = clampGeometry(geometry);
        box.style.left = clamped.x + '%';
        box.style.top = clamped.y + '%';
        box.style.width = clamped.width + '%';
        box.style.height = clamped.height + '%';
    }

    function getBoxPixelRect(box) {
        const boxRect = box.getBoundingClientRect();
        const layerRect = layer.getBoundingClientRect();

        return {
            left: boxRect.left - layerRect.left,
            top: boxRect.top - layerRect.top,
            width: boxRect.width,
            height: boxRect.height,
        };
    }

    function setBoxPixelRect(box, rect) {
        setBoxGeometry(box, {
            x: rect.left / layer.clientWidth * 100,
            y: rect.top / layer.clientHeight * 100,
            width: rect.width / layer.clientWidth * 100,
            height: rect.height / layer.clientHeight * 100,
        });
    }

    function clampDragRect(rect) {
        const width = Math.max(minimumNoteSize, rect.width);
        const height = Math.max(minimumNoteSize, rect.height);
        const left = clamp(rect.left, 0, Math.max(0, layer.clientWidth - width));
        const top = clamp(rect.top, 0, Math.max(0, layer.clientHeight - height));

        return { left, top, width, height };
    }

    function findHtmlBackground(html) {
        const probe = document.createElement('div');
        probe.innerHTML = html || '';
        const candidates = [probe.firstElementChild, ...probe.querySelectorAll('[style]')];

        for (const element of candidates) {
            if (!element?.style) continue;

            const backgroundColor = element.style.backgroundColor;
            const background = element.style.background;
            const value = backgroundColor || background;
            if (value && value !== 'transparent' && value !== 'none') {
                return value;
            }
        }

        return '';
    }

    function applyHtmlBackground(element, html) {
        const background = findHtmlBackground(html);
        element.style.background = background || '';
    }

    function isPositionChanged(box, note) {
        const geometry = getBoxGeometry(box);
        return Math.abs(geometry.x - note.x) > 0.01 || Math.abs(geometry.y - note.y) > 0.01;
    }

    function toggleNoteBorder(box, note) {
        const positionChanged = isPositionChanged(box, note);
        if (box.dataset.borderState === 'blue') {
            const stableColor = positionChanged ? 'border-red-500' : 'border-black';
            box.dataset.borderState = positionChanged ? 'red' : 'black';
            setNoteBorder(box, stableColor);
        } else {
            box.dataset.borderState = 'blue';
            setNoteBorder(box, 'border-blue-500');
        }
    }

    function positionLayer() {
        const naturalWidth = media.naturalWidth;
        const naturalHeight = media.naturalHeight;
        if (!naturalWidth || !naturalHeight) return;

        const box = computeContainFit(mediaContainer.clientWidth, mediaContainer.clientHeight, naturalWidth, naturalHeight);
        layer.style.left = box.left + 'px';
        layer.style.top = box.top + 'px';
        layer.style.width = box.width + 'px';
        layer.style.height = box.height + 'px';
        layer.classList.toggle('hidden', !notesVisible);
    }

    // Border color state machine: clicking always turns the border blue while
    // the note is being dragged. When the drag ends, the border becomes
    // black if the note is back on its original (server) position, or red
    // if it was moved away. Clicking again starts a new drag, turning it
    // blue once more, and the cycle repeats based on the final position.
    const NOTE_BORDER_CLASSES = ['border-black', 'border-blue-500', 'border-red-500'];

    function setNoteBorder(box, color) {
        box.classList.remove(...NOTE_BORDER_CLASSES);
        box.classList.add(color);
    }

    function closeAnyOpenDialog() {
        layer.querySelectorAll('[data-note-dialog]').forEach((el) => el.remove());
        currentDialog = null;
        currentDialogBox = null;
    }

    function repositionDialog() {
        if (!currentDialog || !currentDialogBox) return;
        const boxRect = getBoxPixelRect(currentDialogBox);
        const gap = 6;
        const dialogWidth = currentDialog.offsetWidth || 288;
        const dialogHeight = currentDialog.offsetHeight || 240;
        let dialogLeft = boxRect.left;
        let dialogTop = boxRect.top + boxRect.height + gap;
        if (dialogTop + dialogHeight > layer.clientHeight) {
            dialogTop = boxRect.top - dialogHeight - gap;
        }
        dialogLeft = clamp(dialogLeft, 0, Math.max(0, layer.clientWidth - dialogWidth));
        dialogTop = clamp(dialogTop, 0, Math.max(0, layer.clientHeight - dialogHeight));
        currentDialog.style.left = dialogLeft + 'px';
        currentDialog.style.top = dialogTop + 'px';
    }

    function renderNoteContent(box, note) {
        const content = box.querySelector('[data-note-content]');
        if (content) {
            content.innerHTML = '';
        }
        applyHtmlBackground(box, note.body);

        const tooltip = box.querySelector('[data-note-tooltip]');
        if (!tooltip) return;

        const hasBody = stripTags(note.body).trim() !== '';
        tooltip.innerHTML = hasBody ? note.body : 'Click to edit';
    }

    function bindDrag(box, note) {
        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startRect = null;

        function onDragMove(e) {
            if (!dragging || !startRect) return;

            const rect = clampDragRect({
                left: startRect.left + (e.clientX - startX),
                top: startRect.top + (e.clientY - startY),
                width: startRect.width,
                height: startRect.height,
            });
            setBoxPixelRect(box, rect);
            repositionDialog();
        }

        function onDragEnd() {
            if (!dragging) return;
            dragging = false;
            const geometry = getBoxGeometry(box);
            note.x = geometry.x;
            note.y = geometry.y;
            note.width = geometry.width;
            note.height = geometry.height;
            startRect = null;
            document.removeEventListener('mousemove', onDragMove);
            document.removeEventListener('mouseup', onDragEnd);
        }

        box.addEventListener('mousedown', (e) => {
            if (e.target.closest('[data-note-tooltip], [data-note-resize-handle]')) return;

            toggleNoteBorder(box, note);
            dragging = true;
            startRect = getBoxPixelRect(box);
            startX = e.clientX;
            startY = e.clientY;

            document.addEventListener('mousemove', onDragMove, { passive: true });
            document.addEventListener('mouseup', onDragEnd);

            e.preventDefault();
        });
    }

    function bindResize(box) {
        ['nw', 'ne', 'sw', 'se'].forEach((direction) => {
            const handle = document.createElement('div');
            handle.dataset.noteResizeHandle = direction;
            handle.className = 'note-resize-handle note-resize-' + direction;
            handle.style.cursor = direction === 'nw' || direction === 'se' ? 'nwse-resize' : 'nesw-resize';
            box.appendChild(handle);

            handle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const startRect = getBoxPixelRect(box);
                const startX = e.clientX;
                const startY = e.clientY;
                let resizing = true;

                function onResizeMove(event) {
                    if (!resizing) return;

                    const deltaX = event.clientX - startX;
                    const deltaY = event.clientY - startY;
                    let left = startRect.left;
                    let top = startRect.top;
                    let right = startRect.left + startRect.width;
                    let bottom = startRect.top + startRect.height;

                    if (direction.includes('w')) {
                        left = clamp(startRect.left + deltaX, 0, right - minimumNoteSize);
                    }
                    if (direction.includes('e')) {
                        right = clamp(startRect.left + startRect.width + deltaX, left + minimumNoteSize, layer.clientWidth);
                    }
                    if (direction.includes('n')) {
                        top = clamp(startRect.top + deltaY, 0, bottom - minimumNoteSize);
                    }
                    if (direction.includes('s')) {
                        bottom = clamp(startRect.top + startRect.height + deltaY, top + minimumNoteSize, layer.clientHeight);
                    }

                    setBoxPixelRect(box, {
                        left,
                        top,
                        width: right - left,
                        height: bottom - top,
                    });
                    repositionDialog();
                }

                function onResizeEnd() {
                    if (!resizing) return;
                    resizing = false;
                    const geometry = getBoxGeometry(box);
                    note.x = geometry.x;
                    note.y = geometry.y;
                    note.width = geometry.width;
                    note.height = geometry.height;
                    document.removeEventListener('mousemove', onResizeMove);
                    document.removeEventListener('mouseup', onResizeEnd);
                }

                document.addEventListener('mousemove', onResizeMove, { passive: true });
                document.addEventListener('mouseup', onResizeEnd);
            });
        });
    }

    function buildNoteBox(note) {
        const box = document.createElement('div');
        box.dataset.noteBox = '';
        box.dataset.noteId = note.id;
        box.dataset.borderState = 'black';
        box.className = 'absolute border-2 border-black group/note cursor-move';
        box.style.left = note.x + '%';
        box.style.top = note.y + '%';
        box.style.width = note.width + '%';
        box.style.height = note.height + '%';

        const content = document.createElement('div');
        content.dataset.noteContent = '';
        content.className = 'note-content';
        box.appendChild(content);

        const hasBody = stripTags(note.body).trim() !== '';
        if (canManage || hasBody) {
            const tooltip = document.createElement('div');
            tooltip.dataset.noteTooltip = '';
            tooltip.className = 'hidden group-hover/note:block absolute z-40 left-0 top-full mt-1 min-w-[8rem] max-w-xs bg-white text-gray-900 text-xs rounded shadow border border-gray-400 px-2 py-1 leading-tight'
                + (canManage ? ' cursor-pointer' : '');
            tooltip.innerHTML = hasBody ? note.body : 'Click to edit';

            if (canManage) {
                tooltip.addEventListener('mousedown', (e) => e.stopPropagation());
                tooltip.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openEditDialog(note, box);
                });
            }

            box.appendChild(tooltip);
        }

        if (canManage) {
            bindResize(box);
        }
        bindDrag(box, note);
        renderNoteContent(box, note);
        return box;
    }

    function renderNotes() {
        layer.querySelectorAll('[data-note-box]').forEach((el) => el.remove());
        notes.forEach((note) => layer.appendChild(buildNoteBox(note)));
    }

    async function deleteNote(note, box, { silent = false } = {}) {
        if (!silent && !confirm('Delete this note?')) return;

        try {
            const res = await fetch(`/notes/${note.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!res.ok) {
                return;
            }
            notes = notes.filter((n) => n.id !== note.id);
            box.remove();
        } catch (err) {
            // Leave the note in place if deletion failed.
        }
    }

    // The five-button editor dialog (Save / Preview / Cancel / Delete / History),
    // matching https://danbooru.donmai.us/wiki_pages/help:notes.
    function openEditDialog(note, box, { isNew = false } = {}) {
        closeAnyOpenDialog();

        const dialog = document.createElement('div');
        dialog.dataset.noteDialog = '';
        dialog.className = 'absolute z-50 w-72 min-w-[16rem] min-h-[12rem] max-w-full max-h-full overflow-auto bg-gray-900 text-gray-100 border border-gray-600 rounded shadow-2xl p-3 text-xs';
        layer.appendChild(dialog);

        const boxRect = getBoxPixelRect(box);
        const gap = 6;
        let dialogLeft = boxRect.left;
        let dialogTop = boxRect.top + boxRect.height + gap;
        const dialogWidth = dialog.offsetWidth || 288;
        const dialogHeight = dialog.offsetHeight || 240;
        if (dialogTop + dialogHeight > layer.clientHeight) {
            dialogTop = boxRect.top - dialogHeight - gap;
        }
        dialogLeft = clamp(dialogLeft, 0, Math.max(0, layer.clientWidth - dialogWidth));
        dialogTop = clamp(dialogTop, 0, Math.max(0, layer.clientHeight - dialogHeight));
        dialog.style.left = dialogLeft + 'px';
        dialog.style.top = dialogTop + 'px';

        currentDialog = dialog;
        currentDialogBox = box;

        dialog.innerHTML = `
            <div class="flex items-center justify-between mb-2 gap-2">
                <span class="font-semibold">Editing note #${note.id} (<a href="https://danbooru.donmai.us/wiki_pages/help:notes" target="_blank" rel="noopener" class="text-sky-400 hover:underline font-normal">view help</a>)</span>
                <button type="button" data-close class="text-gray-400 hover:text-white cursor-pointer shrink-0">×</button>
            </div>
            <div data-edit-container>
                <textarea data-textarea class="w-full flex-1 px-2 py-1.5 rounded border border-sky-500 bg-white text-gray-900 text-xs resize-none" spellcheck="false"></textarea>
                <div data-preview-container class="hidden w-full flex-1 px-2 py-1.5 rounded border border-sky-500 bg-white text-gray-900 text-xs overflow-auto"></div>
            </div>
            <div class="flex flex-wrap gap-1.5 mt-2">
                <button type="button" data-save class="px-2 py-1 rounded bg-green-700 hover:bg-green-800 text-white cursor-pointer">Save</button>
                <button type="button" data-preview-btn class="px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 text-white cursor-pointer">Preview</button>
                <button type="button" data-cancel class="px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 text-white cursor-pointer">Cancel</button>
                <button type="button" data-delete class="px-2 py-1 rounded bg-red-800 hover:bg-red-900 text-white cursor-pointer">Delete</button>
                <a href="/notes/${note.id}/history" target="_blank" rel="noopener" data-history class="px-2 py-1 rounded bg-gray-700 hover:bg-gray-600 text-white cursor-pointer inline-block">History</a>
            </div>
        `;

        dialog.addEventListener('mousedown', (e) => e.stopPropagation());

        // Add resize handles to dialog
        const minDialogWidth = 256;
        const minDialogHeight = 192;
        ['nw', 'ne', 'sw', 'se'].forEach((direction) => {
            const handle = document.createElement('div');
            handle.dataset.dialogResizeHandle = direction;
            handle.className = 'dialog-resize-handle dialog-resize-' + direction;
            handle.style.cursor = direction === 'nw' || direction === 'se' ? 'nwse-resize' : 'nesw-resize';
            dialog.appendChild(handle);

            handle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const startLeft = dialog.offsetLeft;
                const startTop = dialog.offsetTop;
                const startWidth = dialog.offsetWidth;
                const startHeight = dialog.offsetHeight;
                const startX = e.clientX;
                const startY = e.clientY;
                let resizing = true;

                function onResizeMove(event) {
                    if (!resizing) return;

                    const deltaX = event.clientX - startX;
                    const deltaY = event.clientY - startY;
                    let left = startLeft;
                    let top = startTop;
                    let right = startLeft + startWidth;
                    let bottom = startTop + startHeight;

                    if (direction.includes('w')) {
                        left = Math.max(0, Math.min(startLeft + deltaX, right - minDialogWidth));
                    }
                    if (direction.includes('e')) {
                        right = Math.min(layer.clientWidth, Math.max(startLeft + startWidth + deltaX, left + minDialogWidth));
                    }
                    if (direction.includes('n')) {
                        top = Math.max(0, Math.min(startTop + deltaY, bottom - minDialogHeight));
                    }
                    if (direction.includes('s')) {
                        bottom = Math.min(layer.clientHeight, Math.max(startTop + startHeight + deltaY, top + minDialogHeight));
                    }

                    dialog.style.left = left + 'px';
                    dialog.style.top = top + 'px';
                    dialog.style.width = (right - left) + 'px';
                    dialog.style.height = (bottom - top) + 'px';
                }

                function onResizeEnd() {
                    if (!resizing) return;
                    resizing = false;
                    document.removeEventListener('mousemove', onResizeMove);
                    document.removeEventListener('mouseup', onResizeEnd);
                }

                document.addEventListener('mousemove', onResizeMove, { passive: true });
                document.addEventListener('mouseup', onResizeEnd);
            });
        });

        const textarea = dialog.querySelector('[data-textarea]');
        const previewContainer = dialog.querySelector('[data-preview-container]');
        textarea.value = note.body || '';
        textarea.focus();

        const previewBtn = dialog.querySelector('[data-preview-btn]');
        let previewOn = false;

        async function updatePreview() {
            previewContainer.innerHTML = '<span class="text-gray-400">Loading preview…</span>';
            try {
                const res = await fetch('/notes/preview', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ body: textarea.value }),
                });
                const data = await res.json();
                const html = data.html || '';
                previewContainer.innerHTML = html || '<span class="text-gray-400">Nothing to preview.</span>';
                applyHtmlBackground(previewContainer, html);
            } catch (err) {
                previewContainer.innerHTML = '<span class="text-red-600">Preview failed.</span>';
            }
        }

        previewBtn.addEventListener('click', async () => {
            previewOn = !previewOn;
            textarea.classList.toggle('hidden', previewOn);
            previewContainer.classList.toggle('hidden', !previewOn);
            previewBtn.textContent = previewOn ? 'Edit' : 'Preview';
            if (previewOn) await updatePreview();
        });

        function closeDialog() {
            dialog.remove();
            currentDialog = null;
            currentDialogBox = null;
        }

        dialog.querySelector('[data-close]').addEventListener('click', () => cancelEdit());
        dialog.querySelector('[data-cancel]').addEventListener('click', () => cancelEdit());

        function cancelEdit() {
            closeDialog();
            if (isNew) {
                deleteNote(note, box, { silent: true });
            }
        }

        dialog.querySelector('[data-delete]').addEventListener('click', () => {
            closeDialog();
            deleteNote(note, box);
        });

        dialog.querySelector('[data-save]').addEventListener('click', async () => {
            const text = textarea.value;
            const geometry = getBoxGeometry(box);

            try {
                const res = await fetch(`/notes/${note.id}`, {
                    method: 'PUT',
                    headers: jsonHeaders(),
                    body: JSON.stringify({
                        body: text,
                        x: geometry.x,
                        y: geometry.y,
                        width: geometry.width,
                        height: geometry.height,
                    }),
                });
                if (!res.ok) return;

                const data = await res.json();
                Object.assign(note, data.note);
                renderNoteContent(box, note);
                setBoxGeometry(box, {
                    x: note.x,
                    y: note.y,
                    width: note.width,
                    height: note.height,
                });
                box.dataset.borderState = 'black';
                setNoteBorder(box, 'border-black');
                closeDialog();
            } catch (err) {
                // Keep the dialog open when the request cannot be sent.
            }
        });
    }

    // Drawing a brand-new note rectangle (admin/uploader only). Matches
    // Danbooru: an empty note is created immediately, then the editor opens.
    function onDrawMouseDown(e) {
        if (e.target !== layer) return;

        drawing = true;
        const rect = layer.getBoundingClientRect();
        drawStart = { x: e.clientX - rect.left, y: e.clientY - rect.top };

        drawBox = document.createElement('div');
        drawBox.className = 'absolute border-2 border-dashed border-sky-500 bg-sky-400/10';
        drawBox.style.left = drawStart.x + 'px';
        drawBox.style.top = drawStart.y + 'px';
        layer.appendChild(drawBox);

        document.addEventListener('mousemove', onDrawMouseMove);
        document.addEventListener('mouseup', onDrawMouseUp);
    }

    function onDrawMouseMove(e) {
        if (!drawing || !drawBox) return;

        const rect = layer.getBoundingClientRect();
        const currX = Math.max(0, Math.min(e.clientX - rect.left, layer.clientWidth));
        const currY = Math.max(0, Math.min(e.clientY - rect.top, layer.clientHeight));

        drawBox.style.left = Math.min(currX, drawStart.x) + 'px';
        drawBox.style.top = Math.min(currY, drawStart.y) + 'px';
        drawBox.style.width = Math.abs(currX - drawStart.x) + 'px';
        drawBox.style.height = Math.abs(currY - drawStart.y) + 'px';
    }

    async function onDrawMouseUp() {
        document.removeEventListener('mousemove', onDrawMouseMove);
        document.removeEventListener('mouseup', onDrawMouseUp);
        drawing = false;

        const rectPx = {
            left: parseFloat(drawBox.style.left) || 0,
            top: parseFloat(drawBox.style.top) || 0,
            width: parseFloat(drawBox.style.width) || 0,
            height: parseFloat(drawBox.style.height) || 0,
        };

        drawBox.remove();
        drawBox = null;
        exitDrawMode();
        suppressNextOutsideClick = true;

        if (rectPx.width < 8 || rectPx.height < 8) return;

        const payload = {
            body: '',
            x: (rectPx.left / layer.clientWidth) * 100,
            y: (rectPx.top / layer.clientHeight) * 100,
            width: (rectPx.width / layer.clientWidth) * 100,
            height: (rectPx.height / layer.clientHeight) * 100,
        };

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                const data = await res.json();
                notes.push(data.note);
                const box = buildNoteBox(data.note);
                layer.appendChild(box);
                openEditDialog(data.note, box, { isNew: true });
            }
        } catch (err) {
            // Drawing silently fails; the user can just try again.
        }
    }

    function enterDrawMode() {
        layer.style.cursor = 'crosshair';
        layer.addEventListener('mousedown', onDrawMouseDown);
        if (addNoteBtn) addNoteBtn.textContent = 'Cancel adding note';
    }

    function exitDrawMode() {
        layer.style.cursor = '';
        layer.removeEventListener('mousedown', onDrawMouseDown);
        if (addNoteBtn) addNoteBtn.textContent = 'Add note';
    }

    if (media.complete && media.naturalWidth) positionLayer();
    media.addEventListener('load', positionLayer);
    window.addEventListener('resize', debounce(positionLayer, 150));
    window.recomputePostNotesLayer = positionLayer;

    renderNotes();

    const addNoteBtn = document.getElementById('add-note-btn');
    addNoteBtn?.addEventListener('click', () => {
        drawModeOn = !drawModeOn;
        if (drawModeOn) {
            enterDrawMode();
        } else {
            exitDrawMode();
        }
    });

    mediaContainer.addEventListener('click', (e) => {
        if (suppressNextOutsideClick) {
            suppressNextOutsideClick = false;
            return;
        }
        if (drawModeOn || drawing || currentDialog || e.target.closest('[data-note-box], [data-note-dialog], [data-note-tooltip], #add-note-btn')) return;
        notesVisible = !notesVisible;
        layer.classList.toggle('hidden', !notesVisible);
    });
}

document.addEventListener('DOMContentLoaded', initPostNotes);