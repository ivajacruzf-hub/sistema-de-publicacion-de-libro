import { getUserList, initUserCrud } from './components/UserList.js';
import { initResourceCrud } from './components/ResourceCrud.js';
import { api } from './utils/api.js';

const storedSession = localStorage.getItem('publicacion-user') || sessionStorage.getItem('publicacion-user');
let session = null;
try {
    session = storedSession ? JSON.parse(storedSession) : null;
} catch {
    session = null;
}

if (!session || !session.token || !['Autor', 'Lector'].includes(session.rol)) {
    localStorage.removeItem('publicacion-user');
    sessionStorage.removeItem('publicacion-user');
    window.location.replace('../login.html');
} else {
const app = document.getElementById("app");
const dashboardStats = {
    libros_publicados: document.getElementById('stats-published-books'),
    autores_registrados: document.getElementById('stats-authors'),
    lectores: document.getElementById('stats-readers'),
    pendientes: document.getElementById('stats-pending-books')
};
const dashboardStatsMessage = document.getElementById('dashboard-stats-message');

async function loadDashboardStats() {
    try {
        const stats = await api.get('/stats');
        Object.entries(dashboardStats).forEach(([key, element]) => {
            element.textContent = stats[key].toLocaleString('es');
        });
        dashboardStatsMessage.hidden = true;
        dashboardStatsMessage.textContent = '';
    } catch (error) {
        Object.values(dashboardStats).forEach((element) => {
            element.textContent = '—';
        });
        dashboardStatsMessage.textContent = error.message || 'No se pudieron cargar los indicadores.';
        dashboardStatsMessage.hidden = false;
        console.error('Error al cargar indicadores del panel:', error);
    }
}

loadDashboardStats();
window.addEventListener('dashboard:refresh-stats', loadDashboardStats);
window.setInterval(loadDashboardStats, 30000);

document.getElementById('sidebar-role').textContent = `Sesión ${session.rol.toLowerCase()}`;
document.getElementById('sidebar-username').textContent = session.username;
document.getElementById('header-role').textContent = session.rol;
document.getElementById('user-initial').textContent = session.username.charAt(0).toUpperCase();
document.querySelectorAll('[data-roles]').forEach((link) => {
    link.hidden = !link.dataset.roles.split(',').includes(session.rol);
});
document.getElementById('logout-button').addEventListener('click', () => {
    localStorage.removeItem('publicacion-user');
    sessionStorage.removeItem('publicacion-user');
    window.location.replace('../login.html');
});

const resources = {
    libros: {
        title: "Libros",
        singular: "libro",
        endpoint: "libros",
        readOnly: true,
        fields: [
            { name: "isbn", label: "ISBN" },
            { name: "titulo", label: "Título", required: true },
            { name: "sinopsis", label: "Sinopsis", type: "textarea" },
            { name: "genero", label: "Género" },
            {
                name: "estado_publicacion",
                label: "Estado de publicación",
                type: "select",
                options: ["En Redacción", "En Revisión", "Publicado", "Archivado"],
                value: "En Redacción"
            },
            { name: "fecha_publicacion", label: "Fecha de publicación", type: "date" }
        ]
    },
    autores: {
        title: "Autores",
        singular: "autor",
        endpoint: "autores",
        fields: [
            { name: "CI", label: "Cédula de identidad", required: true },
            { name: "nombre", label: "Nombre", required: true },
            { name: "apellidos", label: "Apellidos", required: true },
            { name: "biografia", label: "Biografía", type: "textarea" }
        ]
    },
    misLibros: {
        title: "mis libros",
        singular: "libro",
        endpoint: "mis-libros",
        fields: [
            { name: "titulo", label: "Título", required: true },
            { name: "sinopsis", label: "Descripción", type: "textarea" },
            { name: "portada", label: "URL de portada", type: "url" },
            { name: "genero", label: "Género" },
            { name: "isbn", label: "ISBN" },
            {
                name: "estado_publicacion",
                label: "Estado de publicación",
                type: "select",
                options: ["En Redacción", "En Revisión", "Publicado", "Archivado"],
                value: "En Redacción"
            },
            { name: "fecha_publicacion", label: "Fecha de publicación", type: "date" }
        ]
    },
    busqueda: {
        title: "Búsqueda de libros",
        singular: "libro",
        endpoint: "libros",
        searchable: true,
        searchOnly: true,
        readOnly: true,
        fields: [
            { name: "titulo", label: "Título" }
        ]
    }
};

const viewFiles = {
    home: "./src/views/home.html",
    users: "./src/views/user.html"
};

async function loadAuthorWorkspace() {
    app.innerHTML = '';
    const section = document.createElement('section');
    section.className = 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6';
    const heading = document.createElement('div');
    heading.className = 'mb-5';
    const title = document.createElement('h3');
    title.className = 'text-xl font-bold text-slate-950';
    title.textContent = 'Espacio del autor';
    const description = document.createElement('p');
    description.className = 'mt-1 text-sm text-slate-600';
    description.textContent = 'Administra tus libros o consulta el directorio de autores.';
    heading.append(title, description);

    const tabs = document.createElement('div');
    tabs.className = 'mb-5 flex flex-wrap gap-2 border-b border-slate-200 pb-3';
    tabs.setAttribute('role', 'tablist');
    const panel = document.createElement('div');
    const tabDefinitions = [
        { label: 'Mis libros', resource: resources.misLibros },
        { label: 'Directorio de autores', resource: resources.autores }
    ];

    for (const [index, tab] of tabDefinitions.entries()) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'rounded-lg px-4 py-2 text-sm font-semibold transition';
        button.setAttribute('role', 'tab');
        button.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
        button.textContent = tab.label;
        button.addEventListener('click', async () => {
            for (const sibling of tabs.querySelectorAll('[role="tab"]')) {
                const active = sibling === button;
                sibling.setAttribute('aria-selected', active ? 'true' : 'false');
                sibling.className = active
                    ? 'rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white'
                    : 'rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100';
            }
            await initResourceCrud(panel, tab.resource);
        });
        button.className = index === 0
            ? 'rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white'
            : 'rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100';
        tabs.append(button);
    }

    section.append(heading, tabs, panel);
    app.append(section);
    await initResourceCrud(panel, resources.misLibros);
}

function setActiveLink(viewName) {
    document.querySelectorAll("[data-view]").forEach((link) => {
        const isActive = link.dataset.view === viewName;
        link.classList.toggle("bg-white", isActive);
        link.classList.toggle("text-slate-950", isActive);
        link.classList.toggle("shadow-sm", isActive);
        link.classList.toggle("text-slate-300", !isActive);
    });
}

async function loadView(viewName) {
    setActiveLink(viewName);

    if (!viewFiles[viewName]) {
        if (viewName === 'autores' && session.rol === 'Autor') {
            await loadAuthorWorkspace();
            return;
        }
        const resource = resources[viewName];
        if (resource) {
            await initResourceCrud(app, resource);
            return;
        }
        app.innerHTML = `
            <h3 class="text-xl font-bold text-slate-950">Página no disponible</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">La sección solicitada no está configurada.</p>
        `;
        return;
    }

    try {
        const response = await fetch(viewFiles[viewName]);

        if (!response.ok) {
            app.innerHTML = '<p class="text-sm text-red-700">No se pudo cargar la página solicitada.</p>';
            return;
        }

        app.innerHTML = await response.text();

        if (viewName === "users") {
            await getUserList();
            initUserCrud();
        }
    } catch (error) {
        app.innerHTML = '<p class="text-sm text-red-700">No se pudo conectar para cargar la página solicitada.</p>';
    }
}

document.querySelectorAll("[data-view]").forEach((link) => {
    link.addEventListener("click", (event) => {
        event.preventDefault();
        loadView(link.dataset.view);
    });
});

loadView("home");
}
