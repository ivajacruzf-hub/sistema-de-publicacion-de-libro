import { api } from '../utils/api.js';

const buttonClass = 'rounded-lg px-3 py-2 text-sm font-semibold transition';

const createSearchIcon = (className) => {
    const namespace = 'http://www.w3.org/2000/svg';
    const icon = document.createElementNS(namespace, 'svg');
    icon.setAttribute('viewBox', '0 0 24 24');
    icon.setAttribute('width', '24');
    icon.setAttribute('height', '24');
    icon.setAttribute('fill', 'none');
    icon.setAttribute('stroke', 'currentColor');
    icon.setAttribute('stroke-width', '2');
    icon.setAttribute('stroke-linecap', 'round');
    icon.setAttribute('stroke-linejoin', 'round');
    icon.setAttribute('aria-hidden', 'true');
    icon.setAttribute('class', className);

    const lens = document.createElementNS(namespace, 'circle');
    lens.setAttribute('cx', '10.5');
    lens.setAttribute('cy', '10.5');
    lens.setAttribute('r', '6.5');
    const handle = document.createElementNS(namespace, 'path');
    handle.setAttribute('d', 'm15.5 15.5 5 5');
    icon.append(lens, handle);
    return icon;
};

const initBookSearch = async (container, resource) => {
    container.innerHTML = '';
    const section = document.createElement('section');
    section.className = 'mx-auto w-full max-w-4xl';

    const heading = document.createElement('div');
    heading.className = 'text-center';
    const title = document.createElement('h3');
    title.className = 'text-2xl font-bold text-slate-950';
    title.textContent = resource.title;
    const description = document.createElement('p');
    description.className = 'mt-2 text-sm text-slate-600';
    description.textContent = 'Encuentra libros por su título.';
    heading.append(title, description);

    const form = document.createElement('form');
    form.className = 'relative mx-auto mt-6 flex w-full max-w-2xl flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg shadow-slate-900/10 sm:flex-row sm:items-center';
    const searchButton = document.createElement('button');
    searchButton.type = 'submit';
    searchButton.className = `${buttonClass} flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-800 px-5 py-3 text-white hover:bg-emerald-950 disabled:cursor-not-allowed disabled:opacity-60`;
    searchButton.disabled = true;
    const buttonLabel = document.createElement('span');
    buttonLabel.textContent = 'Buscar';
    searchButton.append(createSearchIcon('h-5 w-5'), buttonLabel);

    const inputWrapper = document.createElement('div');
    inputWrapper.className = 'relative min-w-0 flex-1';
    const searchIcon = createSearchIcon('pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400');
    const input = document.createElement('input');
    input.type = 'text';
    input.inputMode = 'search';
    input.disabled = true;
    input.name = 'book-title-search';
    input.placeholder = 'Buscar libros por título...';
    input.setAttribute('aria-label', 'Buscar libros por título');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.autocomplete = 'off';
    input.className = 'block w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-2 focus:ring-emerald-700/20 disabled:cursor-wait disabled:opacity-60';
    const suggestions = document.createElement('ul');
    suggestions.className = 'absolute left-0 right-0 top-full z-20 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl';
    suggestions.setAttribute('role', 'listbox');
    suggestions.setAttribute('aria-label', 'Sugerencias de libros');
    inputWrapper.append(searchIcon, input, suggestions);
    form.append(searchButton, inputWrapper);

    const results = document.createElement('div');
    results.className = 'mx-auto mt-6 w-full max-w-2xl';
    results.setAttribute('aria-live', 'polite');
    results.setAttribute('aria-atomic', 'true');
    section.append(heading, form, results);
    container.append(section);

    let books = [];
    let booksLoadError = null;
    let hasSearched = false;
    let submittedTerm = '';
    let visibleBooks = [];

    const showResults = () => {
        results.replaceChildren();
        if (!hasSearched) return;

        if (booksLoadError) {
            const errorMessage = document.createElement('p');
            errorMessage.className = 'rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700';
            errorMessage.textContent = booksLoadError;
            results.append(errorMessage);
            return;
        }

        const normalizedTerm = submittedTerm.trim().toLocaleLowerCase('es');
        if (!normalizedTerm) {
            const prompt = document.createElement('p');
            prompt.className = 'rounded-xl border border-slate-200 bg-white px-5 py-4 text-center text-sm text-slate-600 shadow-sm';
            prompt.textContent = 'Escribe un título para buscar libros.';
            results.append(prompt);
            return;
        }

        visibleBooks = books.filter((book) =>
            String(book.titulo ?? '').toLocaleLowerCase('es').includes(normalizedTerm)
        );

        if (visibleBooks.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'rounded-xl border border-slate-200 bg-white px-5 py-4 text-center text-sm text-slate-600 shadow-sm';
            emptyMessage.textContent = 'No se encontraron libros';
            results.append(emptyMessage);
            return;
        }

        const list = document.createElement('ul');
        list.className = 'space-y-3';
        visibleBooks.forEach((book) => {
            const item = document.createElement('li');
            const resultButton = document.createElement('button');
            resultButton.type = 'button';
            resultButton.className = 'flex w-full items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-5 py-4 text-left shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-700/30';
            const bookTitle = document.createElement('span');
            bookTitle.className = 'font-semibold text-slate-900';
            bookTitle.textContent = book.titulo || 'Libro sin título';
            const bookStatus = document.createElement('span');
            bookStatus.className = 'shrink-0 text-xs text-slate-500';
            bookStatus.textContent = book.estado_publicacion ?? '';
            resultButton.append(bookTitle, bookStatus);
            resultButton.addEventListener('click', () => showBookDetails(book));
            item.append(resultButton);
            list.append(item);
        });
        results.append(list);
    };

    const showBookDetails = (book) => {
        results.replaceChildren();
        const backButton = document.createElement('button');
        backButton.type = 'button';
        backButton.className = `${buttonClass} mb-4 border border-slate-300 bg-white text-slate-700 hover:bg-slate-50`;
        backButton.textContent = 'Volver a los resultados';
        backButton.addEventListener('click', showResults);

        const card = document.createElement('article');
        card.className = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-md sm:p-8';
        const detailTitle = document.createElement('h4');
        detailTitle.className = 'text-2xl font-bold text-slate-950';
        detailTitle.textContent = book.titulo || 'Libro sin título';
        card.append(detailTitle);

        const details = [
            ['ISBN', book.isbn],
            ['Estado de publicación', book.estado_publicacion],
            ['Fecha de publicación', book.fecha_publicacion],
            ['Sinopsis', book.sinopsis]
        ];
        details.forEach(([label, value]) => {
            if (value === null || value === undefined || value === '') return;
            const detail = document.createElement('div');
            detail.className = 'mt-5';
            const detailLabel = document.createElement('h5');
            detailLabel.className = 'text-sm font-semibold text-slate-700';
            detailLabel.textContent = label;
            const detailValue = document.createElement('p');
            detailValue.className = 'mt-1 whitespace-pre-line text-sm leading-6 text-slate-600';
            detailValue.textContent = String(value);
            detail.append(detailLabel, detailValue);
            card.append(detail);
        });
        results.append(backButton, card);
    };

    const renderSuggestions = () => {
        suggestions.replaceChildren();
        const term = input.value.trim().toLocaleLowerCase('es');
        if (!term) {
            suggestions.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            return;
        }

        const matches = books.filter((book) =>
            String(book.titulo ?? '').toLocaleLowerCase('es').includes(term)
        ).slice(0, 6);
        if (matches.length === 0) {
            suggestions.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            return;
        }

        matches.forEach((book) => {
            const item = document.createElement('li');
            const suggestion = document.createElement('button');
            suggestion.type = 'button';
            suggestion.setAttribute('role', 'option');
            suggestion.className = 'w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-900 focus:bg-emerald-50 focus:outline-none';
            suggestion.textContent = book.titulo || 'Libro sin título';
            suggestion.addEventListener('click', () => {
                input.value = book.titulo ?? '';
                suggestions.classList.add('hidden');
                input.setAttribute('aria-expanded', 'false');
                submittedTerm = input.value.trim();
                hasSearched = true;
                showResults();
            });
            item.append(suggestion);
            suggestions.append(item);
        });
        suggestions.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    };

    input.addEventListener('input', renderSuggestions);
    input.addEventListener('focus', renderSuggestions);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            suggestions.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submittedTerm = input.value.trim();
        hasSearched = true;
        suggestions.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        showResults();
    });

    const loading = document.createElement('p');
    loading.className = 'mt-6 text-center text-sm text-slate-500';
    loading.textContent = 'Cargando catálogo...';
    results.append(loading);
    try {
        const response = await api.get(resource.endpoint);
        if (!Array.isArray(response)) {
            throw new Error(response?.error || 'La API no devolvió una lista de libros');
        }
        books = response;
        input.disabled = false;
        searchButton.disabled = false;
        results.replaceChildren();
    } catch (error) {
        booksLoadError = error.message || 'No se pudo cargar el catálogo de libros.';
        input.disabled = false;
        searchButton.disabled = false;
        results.replaceChildren();
        const errorMessage = document.createElement('p');
        errorMessage.className = 'mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700';
        errorMessage.textContent = booksLoadError;
        results.append(errorMessage);
        console.error('Error al cargar libros para la búsqueda:', error);
    }
};

export const initResourceCrud = async (container, resource) => {
    if (resource.searchOnly) {
        await initBookSearch(container, resource);
        return;
    }

    const canManage = !resource.readOnly;
    container.innerHTML = '';
    const heading = document.createElement('div');
    heading.className = 'flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between';
    heading.innerHTML = `
        <div>
            <h3 class="text-xl font-bold text-slate-950">${resource.searchOnly ? resource.title : `Gestión de ${resource.title.toLowerCase()}`}</h3>
            <p class="mt-1 text-sm text-slate-600">${canManage ? 'Crea, consulta, actualiza y elimina registros.' : resource.searchOnly ? 'Busca libros por su título.' : 'Consulta los libros disponibles.'}</p>
        </div>
    `;

    const createButton = document.createElement('button');
    createButton.type = 'button';
    createButton.className = `${buttonClass} w-fit bg-slate-950 text-white hover:bg-slate-800`;
    createButton.textContent = `Nuevo ${resource.singular}`;
    if (canManage) heading.append(createButton);

    const form = document.createElement('form');
    form.className = 'mt-5 hidden rounded-lg border border-slate-200 bg-slate-50 p-4';
    const fieldsContainer = document.createElement('div');
    fieldsContainer.className = 'grid gap-3 md:grid-cols-2';
    const controls = new Map();

    resource.fields.forEach((field) => {
        const label = document.createElement('label');
        label.className = 'block text-sm font-medium text-slate-700';
        label.append(document.createTextNode(field.label));

        let input;
        if (field.type === 'textarea') {
            input = document.createElement('textarea');
            input.rows = 3;
        } else if (field.type === 'select') {
            input = document.createElement('select');
            field.options.forEach((optionValue) => {
                const option = document.createElement('option');
                option.value = optionValue;
                option.textContent = optionValue;
                input.append(option);
            });
        } else {
            input = document.createElement('input');
            input.type = field.type || 'text';
            if (field.min !== undefined) input.min = field.min;
            if (field.step !== undefined) input.step = field.step;
        }

        input.name = field.name;
        input.dataset.resourceField = field.name;
        input.required = Boolean(field.required);
        input.className = 'mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';
        if (field.value !== undefined) input.value = field.value;
        label.append(input);
        fieldsContainer.append(label);
        controls.set(field.name, input);
    });

    const formMessage = document.createElement('p');
    formMessage.className = 'mt-3 text-sm text-red-700';
    formMessage.setAttribute('role', 'alert');
    const actions = document.createElement('div');
    actions.className = 'mt-3 flex gap-2';
    const saveButton = document.createElement('button');
    saveButton.type = 'submit';
    saveButton.className = `${buttonClass} bg-emerald-600 text-white hover:bg-emerald-700`;
    saveButton.textContent = 'Guardar';
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = `${buttonClass} border border-slate-300 text-slate-700 hover:bg-white`;
    cancelButton.textContent = 'Cancelar';
    actions.append(saveButton, cancelButton);
    form.append(fieldsContainer, actions, formMessage);

    const status = document.createElement('p');
    status.className = 'mt-4 text-sm text-slate-600';
    status.setAttribute('role', 'status');
    const tableWrapper = document.createElement('div');
    tableWrapper.className = 'mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white';
    const table = document.createElement('table');
    table.className = 'min-w-full divide-y divide-slate-200 text-left text-sm';
    const tableHead = document.createElement('thead');
    tableHead.className = 'bg-slate-50 text-xs uppercase text-slate-500';
    const headerRow = document.createElement('tr');
    const headers = ['ID', ...resource.fields.map((field) => field.label)];
    if (canManage) headers.push('Acciones');
    headers.forEach((label) => {
        const cell = document.createElement('th');
        cell.className = 'px-4 py-3 font-semibold';
        cell.textContent = label;
        headerRow.append(cell);
    });
    tableHead.append(headerRow);
    const tableBody = document.createElement('tbody');
    tableBody.className = 'divide-y divide-slate-100 text-slate-700';
    table.append(tableHead, tableBody);
    tableWrapper.append(table);
    let searchInput = null;
    let searchForm = null;
    let submittedSearchTerm = '';
    let hasSearched = false;
    if (resource.searchable) {
        searchForm = document.createElement('form');
        searchForm.className = 'mt-5 flex gap-2';
        const searchButton = document.createElement('button');
        searchButton.type = 'submit';
        searchButton.className = `${buttonClass} flex shrink-0 items-center gap-2 bg-emerald-800 text-white hover:bg-emerald-950`;
        searchButton.innerHTML = '<span class="icon-[mdi--magnify] text-lg" aria-hidden="true"></span><span>Buscar</span>';
        searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.inputMode = 'search';
        searchInput.name = 'book-title-search';
        searchInput.placeholder = 'Buscar libro por título';
        searchInput.setAttribute('aria-label', 'Buscar libro por título');
        searchInput.autocomplete = 'off';
        searchInput.className = 'block min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/20';
        searchForm.append(searchButton, searchInput);
    }
    container.append(heading);
    if (canManage) container.append(form);
    if (searchForm) container.append(searchForm);
    container.append(status, tableWrapper);

    let records = [];
    let loadError = null;
    let editingId = null;

    const closeForm = () => {
        form.reset();
        resource.fields.forEach((field) => {
            if (field.value !== undefined) controls.get(field.name).value = field.value;
        });
        editingId = null;
        formMessage.textContent = '';
        form.classList.add('hidden');
        saveButton.textContent = 'Guardar';
    };

    const openForm = (record = null) => {
        form.reset();
        editingId = record ? record.id : null;
        resource.fields.forEach((field) => {
            const input = controls.get(field.name);
            input.value = record?.[field.name] ?? field.value ?? '';
        });
        formMessage.textContent = '';
        saveButton.textContent = record ? 'Guardar cambios' : 'Guardar';
        form.classList.remove('hidden');
        controls.get(resource.fields[0].name).focus();
    };

    const renderRows = () => {
        tableBody.replaceChildren();
        if (loadError) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = resource.fields.length + (canManage ? 2 : 1);
            cell.className = 'px-4 py-6 text-center text-red-700';
            cell.textContent = loadError;
            row.append(cell);
            tableBody.append(row);
            return;
        }

        const searchTerm = submittedSearchTerm.toLocaleLowerCase('es');
        const visibleRecords = searchTerm
            ? records.filter((record) => String(record[resource.searchField || 'titulo'] ?? '').toLocaleLowerCase('es').includes(searchTerm))
            : (resource.searchOnly ? [] : records);
        if (visibleRecords.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = resource.fields.length + (canManage ? 2 : 1);
            cell.className = 'px-4 py-6 text-center text-slate-500';
            cell.textContent = resource.searchOnly && (!searchTerm || !hasSearched)
                ? 'Escribe un título para buscar libros.'
                : records.length === 0 && !resource.searchOnly
                    ? 'No hay registros todavía.'
                    : 'No se encontraron libros con ese título.';
            row.append(cell);
            tableBody.append(row);
        } else {
            visibleRecords.forEach((record) => {
                const row = document.createElement('tr');
                const idCell = document.createElement('td');
                idCell.className = 'whitespace-nowrap px-4 py-3';
                idCell.textContent = record.id ?? '—';
                row.append(idCell);
                resource.fields.forEach((field) => {
                    const cell = document.createElement('td');
                    cell.className = 'max-w-xs px-4 py-3';
                    cell.textContent = record[field.name] ?? '—';
                    row.append(cell);
                });

                if (canManage) {
                    const actionsCell = document.createElement('td');
                    actionsCell.className = 'whitespace-nowrap px-4 py-3';
                    const editButton = document.createElement('button');
                    editButton.type = 'button';
                    editButton.className = `${buttonClass} mr-2 bg-amber-100 text-amber-800 hover:bg-amber-200`;
                    editButton.textContent = 'Editar';
                    editButton.addEventListener('click', () => openForm(record));
                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.className = `${buttonClass} bg-red-100 text-red-800 hover:bg-red-200`;
                    deleteButton.textContent = 'Eliminar';
                    deleteButton.addEventListener('click', async () => {
                        if (!window.confirm(`¿Eliminar este registro de ${resource.title.toLowerCase()}?`)) return;
                        try {
                            await api.delete(`${resource.endpoint}/${encodeURIComponent(record.id)}`);
                            status.textContent = 'Registro eliminado correctamente.';
                            await loadRecords();
                            window.dispatchEvent(new Event('dashboard:refresh-stats'));
                        } catch (error) {
                            status.textContent = error.message;
                            status.className = 'mt-4 text-sm text-red-700';
                        }
                    });
                    actionsCell.append(editButton, deleteButton);
                    row.append(actionsCell);
                }
                tableBody.append(row);
            });
        }
        status.textContent = resource.searchOnly && (!searchTerm || !hasSearched)
            ? 'Escribe un título y pulsa Buscar.'
            : searchTerm
                ? `${visibleRecords.length} de ${records.length} libro(s) encontrados.`
                : `${records.length} registro(s).`;
    };

    const loadRecords = async () => {
        status.className = 'mt-4 text-sm text-slate-600';
        status.textContent = 'Cargando registros...';
        loadError = null;
        tableBody.replaceChildren();
        try {
            const result = await api.get(resource.endpoint);
            if (!Array.isArray(result)) {
                throw new Error(result?.error || 'La API no devolvió una lista de registros');
            }
            records = result;
            renderRows();
        } catch (error) {
            records = [];
            loadError = error.message;
            renderRows();
            status.textContent = 'No se pudieron cargar los datos. Verifica la conexión de la API y la base de datos.';
            status.className = 'mt-4 text-sm text-red-700';
        }
    };

    if (searchForm) searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submittedSearchTerm = searchInput.value.trim();
        hasSearched = true;
        renderRows();
    });
    if (canManage) createButton.addEventListener('click', () => openForm());
    if (canManage) cancelButton.addEventListener('click', closeForm);
    if (canManage) form.addEventListener('submit', async (event) => {
        event.preventDefault();
        formMessage.textContent = '';
        const data = {};
        resource.fields.forEach((field) => {
            const value = controls.get(field.name).value.trim();
            if (field.type === 'number' && value !== '') {
                data[field.name] = Number(value);
            } else {
                data[field.name] = value === '' && !field.required ? null : value;
            }
        });

        saveButton.disabled = true;
        try {
            const endpoint = editingId
                ? `${resource.endpoint}/${encodeURIComponent(editingId)}`
                : resource.endpoint;
            await (editingId ? api.put(endpoint, data) : api.post(endpoint, data));
            closeForm();
            status.className = 'mt-4 text-sm text-emerald-700';
            status.textContent = 'Registro guardado correctamente.';
            await loadRecords();
            window.dispatchEvent(new Event('dashboard:refresh-stats'));
        } catch (error) {
            formMessage.textContent = error.message;
        } finally {
            saveButton.disabled = false;
        }
    });

    await loadRecords();
};
