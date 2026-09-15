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

function applyThumbFit(container) {
    const fit = container.querySelector('[data-thumb-fit]');
    const media = container.querySelector('[data-thumb-media]');
    if (!fit || !media) return;

    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;
    const naturalWidth = media.tagName === 'VIDEO' ? media.videoWidth : media.naturalWidth;
    const naturalHeight = media.tagName === 'VIDEO' ? media.videoHeight : media.naturalHeight;

    if (!containerWidth || !containerHeight || !naturalWidth || !naturalHeight) return;

    const scale = Math.min(containerWidth / naturalWidth, containerHeight / naturalHeight);
    const width = Math.max(1, Math.round(naturalWidth * scale));
    const height = Math.max(1, Math.round(naturalHeight * scale));
    const top = Math.round((containerHeight - height) / 2);

    fit.style.width = width + 'px';
    fit.style.height = height + 'px';
    fit.style.left = Math.round((containerWidth - width) / 2) + 'px';
    fit.style.top = top + 'px';

    const group = container.closest('.group');
    const panel = group?.querySelector('[data-hover-panel]');
    if (panel) {
        panel.style.bottom = (group.offsetHeight - top + THUMB_HOVER_PANEL_GAP) + 'px';
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