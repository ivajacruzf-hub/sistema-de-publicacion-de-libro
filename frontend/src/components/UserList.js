import { api } from '../utils/api.js';

export const getUserList = async () => {
    const container = document.getElementById('userTableList');
    container.innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';

    try {
        const users = await api.get('/users');
        if (!Array.isArray(users)) {
            throw new Error(users.error || 'La API no devolvió una lista de usuarios');
        }
        container.innerHTML = users.map((user) => `
            <tr>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.rol}</td>
                <td>${user.activo ? 'Activo' : 'Inactivo'}</td>
                <td>${user.fecha_registro ?? 'Sin registro'}</td>
                <td class="flex gap-2 px-4 py-3">
                    <button data-edit-user="${user.id}" class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Editar</button>
                    <button data-delete-user="${user.id}" class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Eliminar</button>
                </td>
            </tr>
        `).join('');
        document.querySelectorAll('[data-edit-user]').forEach((button) => {
            button.addEventListener('click', () => editUser(users, button.dataset.editUser));
        });
        document.querySelectorAll('[data-delete-user]').forEach((button) => {
            button.addEventListener('click', () => deleteUser(button.dataset.deleteUser));
        });
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        container.innerHTML = `<tr><td colspan="6">${error.message || 'Error al cargar la lista de usuarios'}</td></tr>`;
    }
};

const form = () => document.getElementById('userForm');

const editUser = (users, id) => {
    const user = users.find((item) => String(item.id) === String(id));
    if (!user) return;
    form().classList.remove('hidden');
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('password').value = '';
    document.getElementById('rol').value = user.rol;
    document.getElementById('activo').checked = Boolean(Number(user.activo));
};

const deleteUser = async (id) => {
    if (!confirm('¿Eliminar este usuario?')) return;
    await api.delete(`/users/${id}`);
    await getUserList();
};

export const initUserCrud = () => {
    document.getElementById('newUserButton').addEventListener('click', () => {
        form().classList.remove('hidden');
        document.getElementById('userId').value = '';
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('rol').value = 'Lector';
        document.getElementById('activo').checked = true;
    });
    document.getElementById('cancelUserButton').addEventListener('click', () => form().classList.add('hidden'));
    form().addEventListener('submit', async (event) => {
        event.preventDefault();
        const id = document.getElementById('userId').value;
        const data = {
            username: document.getElementById('username').value,
            rol: document.getElementById('rol').value,
            activo: document.getElementById('activo').checked ? 1 : 0
        };
        const password = document.getElementById('password').value;
        if (password) data.password = password;
        if (!id && !password) {
            document.getElementById('userFormMessage').textContent = 'La contraseña es obligatoria para crear un usuario.';
            return;
        }
        try {
            await (id ? api.put(`/users/${id}`, data) : api.post('/users', data));
            form().classList.add('hidden');
            await getUserList();
        } catch (error) {
            document.getElementById('userFormMessage').textContent = error.message;
        }
    });
};