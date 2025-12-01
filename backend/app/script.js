const BACKEND_API = '../api/index.php'; 

const App = {
    getUser: () => JSON.parse(localStorage.getItem('user')),
    requireAuth: () => {
        const user = App.getUser();
        if (!user) window.location.href = 'login.html';
        return user;
    },

    showToast: (message, type = 'success') => {
        const template = document.getElementById('toast-template');
        const container = document.getElementById('toast-container');
        
        if (!template || !container) return;

        const toast = template.cloneNode(true);
        toast.id = '';
        toast.classList.remove('hidden');
        toast.classList.add(type === 'success' ? 'toast-success' : 'toast-error');
        
        toast.querySelector('.toast-msg').innerText = message;
        
        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        
        setTimeout(() => toast.remove(), 3000);
    },

    confirm: (title, message, onConfirm) => {
        const modal = document.getElementById('generic-modal');
        if (!modal) return;

        document.getElementById('modal-title').innerText = title;
        document.getElementById('modal-text').innerText = message;

        modal.classList.remove('hidden');

        const confirmBtn = document.getElementById('modal-confirm-btn');
        const cancelBtn = document.getElementById('modal-cancel-btn');
        
        const newConfirm = confirmBtn.cloneNode(true);
        const newCancel = cancelBtn.cloneNode(true);
        
        confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

        newConfirm.addEventListener('click', () => {
            modal.classList.add('hidden');
            onConfirm();
        });

        newCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    },

    api: async (path = '', method = 'GET', body = null) => {
        try {
            let url = BACKEND_API;
            
            if (path === 'login') {
                url += '?action=login';
            } else if (path) {
                url = BACKEND_API.replace('index.php', `index.php/${path}`);
            }
            
            const options = {
                method,
                headers: { 'Content-Type': 'application/json' }
            };
                
            if (body) options.body = JSON.stringify(body);
            
            const res = await fetch(url, options);
            const data = await res.json();

            if (!res.ok) throw new Error(data.message || 'API Error');
            return data;
        } catch (err) {
            App.showToast(err.message, 'error');
            throw err;
        }
    }
};