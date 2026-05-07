// resources/js/app.js
import 'bootstrap';

// Lucide Icons — substituir ícones data-lucide no DOM
// CDN: carregado no layout via <script>. Aqui deixamos o hook.
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }
});
