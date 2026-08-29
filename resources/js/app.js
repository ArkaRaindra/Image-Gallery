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