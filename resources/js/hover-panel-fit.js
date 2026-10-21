// Keeps the hover panel of a post thumbnail inside the area the visitor can see.
//
// The panel always sits above the picture. When there is not enough room
// above (the top of the screen), the tag box inside the panel gets a smaller
// height and scrolls, so the uploader / date / size info on top always stays
// visible.
//
// The height is worked out when the cursor enters the picture, before the
// panel's show delay (see bindThumbHoverPanel in app.js) has passed.

const PANEL_GAP = 4;
const PANEL_MARGIN = 8;
const MIN_TAGS_HEIGHT = 32;

// Top edge (in viewport coordinates) the panel must stay below: the top of the
// screen, or the top of an ancestor that clips its content vertically.
function topBound(element) {
    let top = 0;

    for (let node = element.parentElement; node && node !== document.documentElement; node = node.parentElement) {
        if (getComputedStyle(node).overflowY === 'visible') continue;

        top = Math.max(top, node.getBoundingClientRect().top);
    }

    return top;
}

function fitHoverPanel(panel, group, fit) {
    const tags = panel.querySelector('[data-hover-tags]');
    if (!tags) return;

    // Measure with the natural height of the tag box (capped by its CSS max-height).
    tags.style.maxHeight = '';
    const wanted = tags.offsetHeight;
    const chrome = panel.offsetHeight - wanted; // uploader/date/size rows, padding and border

    const fitRect = fit.getBoundingClientRect();
    const groupRect = group.getBoundingClientRect();

    const spaceAbove = fitRect.top - topBound(panel) - PANEL_GAP - PANEL_MARGIN;
    const room = Math.floor(spaceAbove - chrome);

    tags.style.maxHeight = Math.min(wanted, Math.max(room, MIN_TAGS_HEIGHT)) + 'px';

    // Sits right above the picture. Both edges are set, so a leftover value never stretches the panel.
    panel.style.top = 'auto';
    panel.style.bottom = (groupRect.bottom - fitRect.top + PANEL_GAP) + 'px';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-hover-panel]').forEach((panel) => {
        const group = panel.closest('.group');
        const fit = group?.querySelector('[data-thumb-fit]');
        if (!fit) return;

        fit.addEventListener('mouseenter', () => {
            // A panel that is already showing must not jump around.
            if (panel.classList.contains('visible')) return;

            fitHoverPanel(panel, group, fit);
        });
    });
});