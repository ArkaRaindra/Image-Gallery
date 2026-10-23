{{-- Tag dropdown (dark list, same look as the search box on the posts page).
     Use it on <input data-wiki-tag-autocomplete> inside an element with data-wiki-tag-wrapper
     (position: relative). Add data-with-wiki="1" to only suggest tags that have a wiki page,
     or data-category="artist" (any tag category) to only suggest tags of that category. --}}
<script>
    (function() {
        if (window.wikiTagAutocompleteReady) return;
        window.wikiTagAutocompleteReady = true;

        const colors = {
            artist: '#f87171',
            character: '#4ade80',
            copyright: '#c084fc',
            meta: '#fbbf24'
        };

        function formatCount(n) {
            if (n >= 1000000) return (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
            if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
            return String(n);
        }

        function setup(input) {
            const wrapper = input.closest('[data-wiki-tag-wrapper]');
            if (!wrapper) return;

            const withWiki = input.dataset.withWiki === '1';
            const category = input.dataset.category || '';
            const list = document.createElement('ul');
            list.className = 'absolute left-0 right-0 mt-1 bg-gray-900 border border-gray-700 rounded shadow-lg max-h-80 overflow-y-auto text-sm z-30 hidden';
            wrapper.appendChild(list);

            let items = [];
            let active = -1;
            let timer;

            function pick(tag) {
                input.value = tag.name;
                list.classList.add('hidden');
                input.focus();
            }

            function highlight() {
                [...list.children].forEach((li, i) => li.classList.toggle('bg-gray-800', i === active));
            }

            function render(tags, query) {
                list.innerHTML = '';
                items = tags;
                active = -1;

                if (!tags.length) {
                    list.classList.add('hidden');
                    return;
                }

                tags.forEach((tag) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between gap-3 px-3 py-1.5 cursor-pointer hover:bg-gray-800';

                    const name = document.createElement('span');
                    name.style.color = colors[tag.category] || '#38bdf8';
                    if (tag.has_wiki && !withWiki) name.classList.add('opacity-60');

                    if (query && tag.name.toLowerCase().startsWith(query.toLowerCase())) {
                        const bold = document.createElement('b');
                        bold.textContent = tag.name.slice(0, query.length);
                        name.appendChild(bold);
                        name.appendChild(document.createTextNode(tag.name.slice(query.length)));
                    } else {
                        name.textContent = tag.name;
                    }

                    const meta = document.createElement('span');
                    meta.className = 'text-gray-500 text-xs shrink-0';
                    meta.textContent = (tag.has_wiki && !withWiki ? 'has wiki · ' : '') + formatCount(tag.post_count);

                    li.append(name, meta);
                    li.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        pick(tag);
                    });
                    list.appendChild(li);
                });

                list.classList.remove('hidden');
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const query = input.value.trim().replace(/ /g, '_');

                if (!query) {
                    list.classList.add('hidden');
                    return;
                }

                timer = setTimeout(async () => {
                    try {
                        const url = @json(route('tags.autocomplete')) + '?q=' + encodeURIComponent(query) +
                            (withWiki ? '&with_wiki=1' : '') +
                            (category ? '&category=' + encodeURIComponent(category) : '');
                        const res = await fetch(url);
                        render(await res.json(), query);
                    } catch (err) {
                        list.classList.add('hidden');
                    }
                }, 200);
            });

            input.addEventListener('keydown', (e) => {
                if (list.classList.contains('hidden')) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    active = Math.min(active + 1, items.length - 1);
                    highlight();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    active = Math.max(active - 1, 0);
                    highlight();
                } else if (e.key === 'Enter' && active >= 0) {
                    e.preventDefault();
                    pick(items[active]);
                } else if (e.key === 'Escape') {
                    list.classList.add('hidden');
                }
            });

            input.addEventListener('blur', () => setTimeout(() => list.classList.add('hidden'), 150));
        }

        document.querySelectorAll('input[data-wiki-tag-autocomplete]').forEach(setup);
    })();
</script>
