import './bootstrap';

const RH_CORRECTOR_SELECTOR = [
    'textarea',
    'input[type="text"]',
    'input[type="search"]',
    'input[type="url"]',
    'input[type="tel"]',
    'input:not([type])',
].join(', ');

const rhCorrectorProcesados = new WeakSet();

function rhEsCampoExcluido(campo) {
    return campo.disabled
        || campo.readOnly
        || campo.dataset.noSpellcheck === 'true'
        || campo.hasAttribute('data-no-spellcheck');
}

function rhAplicarCorrectorNativo(campo) {
    if (rhCorrectorProcesados.has(campo) || rhEsCampoExcluido(campo)) {
        return;
    }

    campo.spellcheck = true;
    campo.setAttribute('spellcheck', 'true');
    campo.setAttribute('autocorrect', 'on');
    campo.setAttribute('lang', 'es');

    if (
        campo.tagName === 'TEXTAREA'
        || campo.getAttribute('type') === 'text'
        || campo.getAttribute('type') === 'search'
        || !campo.getAttribute('type')
    ) {
        if (!campo.hasAttribute('autocapitalize')) {
            campo.setAttribute('autocapitalize', 'sentences');
        }
    }

    rhCorrectorProcesados.add(campo);
}

function rhRecorrerCampos(root = document) {
    if (root instanceof Element && root.matches(RH_CORRECTOR_SELECTOR)) {
        rhAplicarCorrectorNativo(root);
    }

    root.querySelectorAll?.(RH_CORRECTOR_SELECTOR).forEach((campo) => {
        rhAplicarCorrectorNativo(campo);
    });
}

function rhInicializarCorrector() {
    document.documentElement.setAttribute('lang', 'es');
    document.body?.setAttribute('lang', 'es');
    rhRecorrerCampos(document);

    document.addEventListener('livewire:navigated', () => {
        rhRecorrerCampos(document);
    });

    const observador = new MutationObserver((mutaciones) => {
        for (const mutacion of mutaciones) {
            mutacion.addedNodes.forEach((nodo) => {
                if (nodo instanceof HTMLElement) {
                    rhRecorrerCampos(nodo);
                }
            });
        }
    });

    if (document.body) {
        observador.observe(document.body, { childList: true, subtree: true });
    } else {
        window.addEventListener('DOMContentLoaded', () => {
            observador.observe(document.body, { childList: true, subtree: true });
        }, { once: true });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', rhInicializarCorrector, { once: true });
} else {
    rhInicializarCorrector();
}
