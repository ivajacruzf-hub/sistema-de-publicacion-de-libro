import { getUserList, initUserCrud } from './components/UserList.js';

const app = document.getElementById("app");

const fallbackViews = {
    productos: {
        title: "Productos",
        text: "Modulo preparado para administrar productos relacionados con la publicacion de libros."
    },
    libros: {
        title: "Libros",
        text: "Modulo preparado para registrar, revisar y publicar libros dentro del sistema."
    },
    autores: {
        title: "Autores",
        text: "Modulo preparado para administrar la informacion de los autores."
    },
    lectores: {
        title: "Lectores",
        text: "Modulo preparado para gestionar lectores y sus lecturas."
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

function renderFallback(viewName) {
    const view = fallbackViews[viewName];

    if (!view) {
        app.innerHTML = `
            <h3 class="text-xl font-bold text-slate-950">Pagina no disponible</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">La seccion solicitada todavia no esta configurada.</p>
        `;
        return;
    }

    app.innerHTML = `
        <h3 class="text-xl font-bold text-slate-950">${view.title}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">${view.text}</p>
    `;
}

async function loadView(viewName) {
    setActiveLink(viewName);

    if (!viewFiles[viewName]) {
        renderFallback(viewName);
        return;
    }

    try {
        const response = await fetch(viewFiles[viewName]);

        if (!response.ok) {
            renderFallback(viewName);
            return;
        }

        app.innerHTML = await response.text();

        if (viewName === "users") {
            await getUserList();
            initUserCrud();
        }
    } catch (error) {
        renderFallback(viewName);
    }
}

document.querySelectorAll("[data-view]").forEach((link) => {
    link.addEventListener("click", (event) => {
        event.preventDefault();
        loadView(link.dataset.view);
    });
});

loadView("users");
