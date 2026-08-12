import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const hotReloadMeta = document.querySelector('meta[name="frankenphp-hot-reload:url"]');

if (hotReloadMeta) {
    const loadScript = (src, attributes = {}) => {
        const script = document.createElement('script');
        script.src = src;

        Object.entries(attributes).forEach(([key, value]) => {
            script.setAttribute(key, value);
        });

        document.head.appendChild(script);
    };

    loadScript('https://cdn.jsdelivr.net/npm/idiomorph');
    loadScript('https://cdn.jsdelivr.net/npm/frankenphp-hot-reload/+esm', { type: 'module' });
}

const jobBoardSearchButton = document.getElementById('job-board-search-button');
if (jobBoardSearchButton) {
    const performSearch = (query) => {
        const searchUrl = `/job-offers?q=${encodeURIComponent(query)}`;

        let resultsContainer = document.querySelector('.job-board-list');
        if (resultsContainer) {
            resultsContainer.setAttribute('aria-live', 'polite');
            resultsContainer.setAttribute('aria-atomic', 'true');
            const loading = document.createElement('li');
            loading.className = 'job-board-item';
            loading.textContent = 'Loading…';
            resultsContainer.innerHTML = '';
            resultsContainer.appendChild(loading);
        }

        fetch(searchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw new Error(response.statusText || 'Network response was not ok');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newList = doc.querySelector('.job-board-list');
                const oldList = document.querySelector('.job-board-list');
                if (newList && oldList) {
                    // preserve ARIA attributes on the container
                    if (oldList.hasAttribute('aria-live')) {
                        newList.setAttribute('aria-live', oldList.getAttribute('aria-live'));
                    }
                    if (oldList.hasAttribute('aria-atomic')) {
                        newList.setAttribute('aria-atomic', oldList.getAttribute('aria-atomic'));
                    }

                    oldList.replaceWith(newList);
                }
            })
            .catch(err => {
                console.error('Search failed', err);

                resultsContainer = document.querySelector('.job-board-list');
                const messageText = 'Search failed. Please try again.';

                if (resultsContainer) {
                    resultsContainer.innerHTML = '';
                    const li = document.createElement('li');
                    li.className = 'job-board-item job-board-error';

                    const msg = document.createElement('span');
                    msg.textContent = messageText;
                    msg.className = 'job-board-error-message';

                    const retry = document.createElement('button');
                    retry.type = 'button';
                    retry.className = 'job-board-retry-button';
                    retry.textContent = 'Retry';
                    retry.setAttribute('aria-label', `Retry search for ${query}`);
                    retry.addEventListener('click', () => {
                        performSearch(query);
                        retry.focus();
                    });

                    li.appendChild(msg);
                    li.appendChild(retry);
                    resultsContainer.appendChild(li);
                    resultsContainer.setAttribute('aria-live', 'polite');
                    resultsContainer.setAttribute('aria-atomic', 'true');
                    retry.focus();
                } else {
                    const section = document.querySelector('.job-board-section');
                    if (section) {
                        const ul = document.createElement('ul');
                        ul.className = 'job-board-list';
                        ul.setAttribute('aria-live', 'polite');
                        ul.setAttribute('aria-atomic', 'true');
                        const li = document.createElement('li');
                        li.className = 'job-board-item job-board-error';

                        const msg = document.createElement('span');
                        msg.textContent = messageText;
                        msg.className = 'job-board-error-message';

                        const retry = document.createElement('button');
                        retry.type = 'button';
                        retry.className = 'job-board-retry-button';
                        retry.textContent = 'Retry';
                        retry.setAttribute('aria-label', `Retry search for ${query}`);
                        retry.addEventListener('click', () => {
                            performSearch(query);
                            retry.focus();
                        });

                        li.appendChild(msg);
                        li.appendChild(retry);
                        ul.appendChild(li);
                        section.appendChild(ul);
                        retry.focus();
                    }
                }
            });
    };

    jobBoardSearchButton.addEventListener('click', function (event) {
        event.preventDefault();
        const searchInput = document.getElementById('job-board-search-input');
        const query = searchInput.value.trim();
        performSearch(query);
    });
}
