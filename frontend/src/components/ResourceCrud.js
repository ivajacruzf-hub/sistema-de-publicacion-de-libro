import { api } from '../utils/api.js';

const buttonClass = 'rounded-lg px-3 py-2 text-sm font-semibold transition';

export const initResourceCrud = async (container, resource) => {
    container.innerHTML = '';
    const heading = document.createElement('div');
    heading.className = 'flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between';
    heading.innerHTML = `
        <div>
            <h3 class="text-xl font-bold text-slate-950">Gestión de ${resource.title.toLowerCase()}</h3>
            <p class="mt-1 text-sm text-slate-600">Crea, consulta, actualiza y elimina registros.</p>
        </div>
    `;

    const createButton = document.createElement('button');
    createButton.type = 'button';
    createButton.className = `${buttonClass} w-fit bg-slate-950 text-white hover:bg-slate-800`;
    createButton.textContent = `Nuevo ${resource.singular}`;
    heading.append(createButton);

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
    ['ID', ...resource.fields.map((field) => field.label), 'Acciones'].forEach((label) => {
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
    container.append(heading, form, status, tableWrapper);

    let records = [];
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
        if (records.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = resource.fields.length + 2;
            cell.className = 'px-4 py-6 text-center text-slate-500';
            cell.textContent = 'No hay registros todavía.';
            row.append(cell);
            tableBody.append(row);
            return;
        }

        records.forEach((record) => {
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
                } catch (error) {
                    status.textContent = error.message;
                    status.className = 'mt-4 text-sm text-red-700';
                }
            });
            actionsCell.append(editButton, deleteButton);
            row.append(actionsCell);
            tableBody.append(row);
        });
    };

    const loadRecords = async () => {
        status.className = 'mt-4 text-sm text-slate-600';
        status.textContent = 'Cargando registros...';
        tableBody.replaceChildren();
        try {
            const result = await api.get(resource.endpoint);
            if (!Array.isArray(result)) {
                throw new Error(result?.error || 'La API no devolvió una lista de registros');
            }
            records = result;
            renderRows();
            status.textContent = `${records.length} registro(s).`;
        } catch (error) {
            records = [];
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = resource.fields.length + 2;
            cell.className = 'px-4 py-6 text-center text-red-700';
            cell.textContent = error.message;
            row.append(cell);
            tableBody.append(row);
            status.textContent = 'No se pudieron cargar los datos. Verifica la conexión de la API y la base de datos.';
            status.className = 'mt-4 text-sm text-red-700';
        }
    };

    createButton.addEventListener('click', () => openForm());
    cancelButton.addEventListener('click', closeForm);
    form.addEventListener('submit', async (event) => {
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
        } catch (error) {
            formMessage.textContent = error.message;
        } finally {
            saveButton.disabled = false;
        }
    });

    await loadRecords();
};
