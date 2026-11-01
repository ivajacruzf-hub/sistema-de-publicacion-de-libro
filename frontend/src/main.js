import { getUserList, initUserCrud } from './components/UserList.js';
import { initResourceCrud } from './components/ResourceCrud.js';

const storedSession = localStorage.getItem('publicacion-user') || sessionStorage.getItem('publicacion-user');
let session = null;
try {
    session = storedSession ? JSON.parse(storedSession) : null;
} catch {
    session = null;
}

if (!session || !['Autor', 'Lector'].includes(session.rol)) {
    localStorage.removeItem('publicacion-user');
    sessionStorage.removeItem('publicacion-user');
    window.location.replace('../login.html');
} else {
const app = document.getElementById("app");

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
        fields: [
            { name: "isbn", label: "ISBN" },
            { name: "titulo", label: "Título", required: true },
            { name: "sinopsis", label: "Sinopsis", type: "textarea" },
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
    lectores: {
        title: "Lectores",
        singular: "lector",
        endpoint: "lectores",
        fields: [
            { name: "CI", label: "Cédula de identidad", required: true },
            { name: "nombre", label: "Nombre", required: true },
            { name: "apellidos", label: "Apellidos", required: true },
            { name: "email", label: "Correo electrónico", type: "email", required: true }
        ]
    }
};

const viewFiles = {
    home: "./src/views/home.html",
    users: "./src/views/user.html"
};

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
