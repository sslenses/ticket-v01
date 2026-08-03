<style>
    #spa-loading-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(to right, #ef4444, #f43f5e);
        z-index: 99999;
        width: 0;
        transition: width 0.3s ease-out, opacity 0.3s ease-out;
        opacity: 0;
        pointer-events: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Intercept clicks on links
        document.addEventListener('click', async (e) => {
            const link = e.target.closest('a');
            if (!link) return;
            
            // Check if it's a standard link navigation
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) return;
            
            // Exclude specific links
            if (url.pathname === '/logout' || url.pathname === '/login' || link.hasAttribute('download') || link.getAttribute('target') === '_blank') {
                return;
            }
            
            if (link.hasAttribute('data-no-spa')) return;
            
            e.preventDefault();
            await navigateTo(url.href);
        });

        // Handle back/forward navigation
        window.addEventListener('popstate', async () => {
            await navigateTo(window.location.href, false);
        });
        
        // Define global reload page helper
        window.reloadPage = async () => {
            await navigateTo(window.location.href, false);
        };
    });

    function showLoadingBar() {
        let bar = document.getElementById('spa-loading-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'spa-loading-bar';
            document.body.appendChild(bar);
        }
        bar.style.width = '0%';
        bar.style.opacity = '1';
        // Force reflow
        bar.offsetWidth;
        bar.style.width = '70%';
    }

    function hideLoadingBar() {
        const bar = document.getElementById('spa-loading-bar');
        if (bar) {
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => {
                    bar.style.width = '0%';
                }, 300);
            }, 100);
        }
    }

    async function navigateTo(url, pushState = true) {
        try {
            showLoadingBar();
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                window.location.href = url;
                return;
            }
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // 1. Update Title
            document.title = doc.title;
            
            // 2. Extract and run scripts in the new page (especially window variable initializations)
            const scripts = doc.querySelectorAll('head script, body script');
            scripts.forEach(script => {
                if (!script.src && (script.textContent.includes('window.__tenants') || script.textContent.includes('window.'))) {
                    const newScript = document.createElement('script');
                    newScript.textContent = script.textContent;
                    document.body.appendChild(newScript);
                    newScript.remove();
                }
            });
            
            // 3. Swap the root content
            const newAppRoot = doc.getElementById('app-root');
            const currentAppRoot = document.getElementById('app-root');
            if (newAppRoot && currentAppRoot) {
                currentAppRoot.replaceWith(newAppRoot);
                
                // Re-initialize Alpine on the new elements
                if (window.Alpine) {
                    window.Alpine.initTree(newAppRoot);
                }
            } else {
                // If the new page has no #app-root (e.g. login page) or the current page lacks it,
                // trigger a full page reload to let the browser render it natively.
                window.location.href = url;
            }
            
            // 4. Update browser history
            if (pushState) {
                history.pushState(null, '', url);
            }
            
            // Scroll to top
            window.scrollTo({ top: 0 });
            
            hideLoadingBar();
        } catch (error) {
            console.error('SPA navigation error:', error);
            window.location.href = url;
        }
    }
</script>
<?php /**PATH /app/resources/views/layouts/spa-script.blade.php ENDPATH**/ ?>