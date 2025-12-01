const user = App.requireAuth();

document.getElementById('nav-username').innerText = user.name;
document.getElementById('nav-role').innerText = user.role === 'admin' ? 'Administrador' : 'Usuário Comum';

if (user.role === 'admin') {
    document.getElementById('admin-view').classList.remove('hidden');
    loadAllUsers();
} else {
    document.getElementById('user-view').classList.remove('hidden');
    document.getElementById('welcome-name').innerText = user.name;
}

function goToMyProfile() {
    window.location.href = `user-details.html?id=${user.id}`;
}

async function loadAllUsers() {
    try {
        const users = await App.api('users'); 
        const grid = document.getElementById('user-grid');
        const template = document.getElementById('user-card-template');
        
        grid.innerHTML = '';

        if (users.length === 0) {
            grid.innerHTML = '<p>No user found</p>';
            return;
        }

        users.forEach(user => {
            const card = template.cloneNode(true);
            card.id = '';
            card.classList.remove('hidden'); 
            
            card.querySelector('.card-name').innerText = user.name;
            
            const roleSpan = card.querySelector('.card-role');
            roleSpan.innerText = user.role;
            roleSpan.classList.add(user.role === 'admin' ? 'card-role-admin' : 'card-role-user');
            
            card.querySelector('.card-email').innerText = user.email;
            
            card.querySelector('.card-details-btn').addEventListener('click', () => openDetailsModal(user));
            
            const editBtn = card.querySelector('.card-edit-btn');
            editBtn.href = `user-details.html?id=${user.id}`;
            
            card.querySelector('.card-delete-btn').addEventListener('click', () => deleteUser(user.id, user.name));
            
            grid.appendChild(card);
        });
    } catch (e) {
        console.error(e);
        const grid = document.getElementById('user-grid');
        const errorMsg = document.createElement('p');
        errorMsg.className = 'error-text';
        errorMsg.innerText = 'Error loading users ';
        grid.innerHTML = '';
        grid.appendChild(errorMsg);
    }
}


function deleteUser(id, name) {
    App.confirm('Delete user', `Are you sure to delete '${name}' of the system?`, async () => {
        try {
            await App.api(`users/${id}`, 'DELETE');
            App.showToast('User deleted successfully', 'success');
            loadAllUsers();
        } catch (e) {
            console.error(e);
            App.showToast('Error deleting user', 'error');
        }
    });
}


function openDetailsModal(user) {
    const modal = document.getElementById('details-modal');
    const content = document.getElementById('modal-content');
    const template = document.getElementById('modal-content-template');
    const editBtn = document.getElementById('modal-edit-btn');

    if (!template) {
        console.error('Modal template not found');
        return;
    }

    const birthDate = user.birth_date ? new Date(user.birth_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) : 'Not informed';
    const cpfFormatted = user.cpf ? user.cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4") : 'Not informed';

    const detailsContent = template.cloneNode(true);
    detailsContent.id = ''; 
    detailsContent.classList.remove('hidden'); 
    
    detailsContent.querySelector('.modal-detail-name').innerText = user.name;
    detailsContent.querySelector('.modal-detail-email').innerText = user.email;
    detailsContent.querySelector('.modal-detail-cpf').innerText = cpfFormatted;
    detailsContent.querySelector('.modal-detail-birth').innerText = birthDate;
    detailsContent.querySelector('.modal-detail-role').innerText = user.role;
    detailsContent.querySelector('.modal-detail-id').innerText = `#${user.id}`;

    content.innerHTML = '';
    content.appendChild(detailsContent);

    editBtn.onclick = () => window.location.href = `user-details.html?id=${user.id}`;

    modal.classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('details-modal').classList.add('hidden');
}

document.getElementById('details-modal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('details-modal')) {
        closeDetailsModal();
    }
});