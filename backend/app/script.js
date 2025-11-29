const API_URL = '../api/api.php';

const App = {
    // --- Auth & Utils (Mesmo de antes) ---
    getAuthUser: () => {
        const user = localStorage.getItem('app_user');
        return user ? JSON.parse(user) : null;
    },

    requireAuth: () => {
        const user = App.getAuthUser();
        if (!user) {
            window.location.href = 'login.html';
        }
        return user;
    },

    logout: () => {
        localStorage.removeItem('app_user');
        window.location.href = 'login.html';
    },

    fetch: async (url, options = {}) => {
        try {
            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Error');
            return data;
        } catch (error) {
            App.showToast(error.message, 'error');
            throw error;
        }
    },

    // --- Validation Helpers (NOVO) ---
    
    // Formata CPF enquanto digita (000.000.000-00)
    maskCPF: (value) => {
        return value
            .replace(/\D/g, '') // Remove tudo que não é dígito
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    },

    // Validação matemática do CPF
    isValidCPF: (cpf) => {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf == '') return false;
        // Elimina CPFs invalidos conhecidos
        if (cpf.length != 11 || 
            cpf == "00000000000" || 
            cpf == "11111111111" || 
            cpf == "22222222222" || 
            cpf == "33333333333" || 
            cpf == "44444444444" || 
            cpf == "55555555555" || 
            cpf == "66666666666" || 
            cpf == "77777777777" || 
            cpf == "88888888888" || 
            cpf == "99999999999")
                return false;
        
        // Valida 1o digito
        let add = 0;
        for (let i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
        let rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) rev = 0;
        if (rev != parseInt(cpf.charAt(9))) return false;
        
        // Valida 2o digito
        add = 0;
        for (let i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
        rev = 11 - (add % 11);
        if (rev == 10 || rev == 11) rev = 0;
        if (rev != parseInt(cpf.charAt(10))) return false;
        
        return true;
    },

    // --- UI Components Injection (Mesmo de antes) ---
    injectComponents: () => {
        const toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        document.body.appendChild(toastContainer);

        const modalHtml = `
            <div id="confirm-modal" class="modal-overlay hidden">
                <div class="modal-box">
                    <h3 id="modal-title" class="mb-2">Confirmation</h3>
                    <p id="modal-msg" class="mb-2">Are you sure?</p>
                    <div class="flex-end mt-2">
                        <button id="modal-cancel" class="btn btn-outline">Cancel</button>
                        <button id="modal-confirm" class="btn btn-danger">Confirm</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    },

    showToast: (message, type = 'success') => {
        const container = document.getElementById('toast-container');
        if(!container) return;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span>${type === 'success' ? '✅' : '⚠️'}</span> <span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    },

    confirm: (title, message, onConfirm) => {
        const modal = document.getElementById('confirm-modal');
        const titleEl = document.getElementById('modal-title');
        const msgEl = document.getElementById('modal-msg');
        const confirmBtn = document.getElementById('modal-confirm');
        const cancelBtn = document.getElementById('modal-cancel');

        titleEl.innerText = title;
        msgEl.innerText = message;
        modal.classList.remove('hidden');

        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

        newConfirmBtn.onclick = () => {
            modal.classList.add('hidden');
            onConfirm();
        };
        newCancelBtn.onclick = () => { modal.classList.add('hidden'); };
    }
};

document.addEventListener('DOMContentLoaded', App.injectComponents);