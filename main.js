import { GetUserList } from "./components/UserList";
const app = document.getElementById('app');
const views = {
    home:async () => {
        const res = await fetch('./src/view/home.html');
                app.innerHTML = await res.text();
    },
    users: async () => {
        const res = await fetch('./src/views/users.html');
        console.log(res);
        app.innerHTML = await res.text();
        await GetUserList();
    } 
}