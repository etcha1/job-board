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
