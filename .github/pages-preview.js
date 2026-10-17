// Сетевые действия Laravel доступны только в основном приложении.
const dialog = document.getElementById('preview-info');
document.querySelector('[data-close-preview]')?.addEventListener('click', () => dialog.close());
dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
document.addEventListener('click', (event) => {
    const unavailable = event.target.closest('a[href="#preview-info"], [data-preview-form] button');
    if (!unavailable) return;
    event.preventDefault();
    dialog.showModal();
});

const toolbar = document.querySelector('.catalog-toolbar');
const grid = document.querySelector('.catalog-products');
if (toolbar && grid) {
    const base = new URL('.', document.currentScript.src);
    fetch(new URL('catalog-data.json', base)).then((response) => {
        if (!response.ok) throw new Error('Каталог временно недоступен');
        return response.json();
    }).then((products) => {
        const pagination = document.querySelector('[data-preview-pagination]');
        const search = toolbar.elements.namedItem('q');
        const category = toolbar.elements.namedItem('category');
        const sort = toolbar.elements.namedItem('sort');
        const render = () => {
            const params = new URLSearchParams(location.search);
            search.value = params.get('q') || '';
            category.value = params.get('category') || '';
            sort.value = params.get('sort') || 'name';
            let matches = products.filter((product) => product.name.toLocaleLowerCase('ru').includes(search.value.trim().toLocaleLowerCase('ru')) && (!category.value || product.categories.includes(category.value)));
            matches.sort((a, b) => {
                if (sort.value === 'price_asc') return (a.price ?? Infinity) - (b.price ?? Infinity);
                if (sort.value === 'price_desc') return (b.price ?? -Infinity) - (a.price ?? -Infinity);
                return a.name.localeCompare(b.name, 'ru');
            });
            const count = Math.max(1, Math.ceil(matches.length / 24));
            const page = Math.max(1, Math.min(count, Number.parseInt(params.get('page'), 10) || 1));
            grid.innerHTML = matches.slice((page - 1) * 24, page * 24).map((product) => product.html).join('');
            if (!matches.length) {
                const empty = document.createElement('p');
                empty.textContent = 'Ничего не найдено. Попробуйте изменить поиск или категорию.';
                grid.append(empty);
            }
            if (pagination) {
                pagination.replaceChildren();
                for (const [label, next, disabled] of [['Назад', page - 1, page <= 1], ['Далее', page + 1, page >= count]]) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = label;
                    button.disabled = disabled;
                    button.addEventListener('click', () => {
                        params.set('page', next);
                        history.pushState({}, '', '?' + params);
                        render();
                        toolbar.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                    });
                    pagination.append(button);
                }
                const status = document.createElement('span');
                status.setAttribute('role', 'status');
                status.textContent = `Страница ${page} из ${count} · ${matches.length} товаров`;
                pagination.append(status);
            }
        };
        toolbar.addEventListener('submit', (event) => {
            event.preventDefault();
            const params = new URLSearchParams(new FormData(toolbar));
            history.pushState({}, '', '?' + params);
            render();
        });
        window.addEventListener('popstate', render);
        render();
    }).catch(() => {
        toolbar.addEventListener('submit', (event) => {
            event.preventDefault();
            dialog.showModal();
        });
        const error = document.createElement('p');
        error.setAttribute('role', 'alert');
        error.textContent = 'Поиск временно недоступен. Обновите страницу.';
        toolbar.after(error);
    });
}
