<style>
    /* Tailwind's preflight resets headings, lists, etc. to plain text,
       so the wiki HTML needs its own styles (same as the Wiki tab on the posts page). */
    .wiki-content img {
        max-width: 100%;
        border-radius: 0.25rem;
        margin: 0.5rem 0;
    }

    .wiki-content p {
        margin-bottom: 0.5rem;
    }

    .wiki-content h1,
    .wiki-content h2,
    .wiki-content h3,
    .wiki-content h4,
    .wiki-content h5,
    .wiki-content h6 {
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
        margin: 1rem 0 0.5rem;
    }

    .wiki-content h1 {
        font-size: 1.5rem;
    }

    .wiki-content h2 {
        font-size: 1.25rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #d1d5db;
    }

    .wiki-content h3 {
        font-size: 1.125rem;
    }

    .wiki-content h4 {
        font-size: 1rem;
    }

    .wiki-content h5,
    .wiki-content h6 {
        font-size: 0.875rem;
    }

    .wiki-content > :first-child {
        margin-top: 0;
    }

    .wiki-content ul {
        list-style: disc;
        padding-left: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .wiki-content ol {
        list-style: decimal;
        padding-left: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .wiki-content blockquote {
        border-left: 3px solid #9ca3af;
        padding-left: 0.75rem;
        margin: 0.5rem 0;
        color: #4b5563;
    }

    .wiki-content hr {
        border-top: 1px solid #d1d5db;
        margin: 1rem 0;
    }

    .wiki-content strong {
        font-weight: 700;
    }

    .wiki-content em {
        font-style: italic;
    }

    .wiki-content code,
    .wiki-content pre {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        background: #f3f4f6;
        border-radius: 0.25rem;
    }

    .wiki-content code {
        padding: 0.1rem 0.3rem;
    }

    .wiki-content pre {
        padding: 0.5rem 0.75rem;
        margin: 0.5rem 0;
        overflow-x: auto;
    }

    .wiki-content table {
        border-collapse: collapse;
        margin: 0.5rem 0;
    }

    .wiki-content th,
    .wiki-content td {
        border: 1px solid #d1d5db;
        padding: 0.25rem 0.5rem;
    }

    .wiki-content a {
        color: #0369a1;
        text-decoration: underline;
    }
</style>