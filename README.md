# Person Management System

Sistema para gerenciamento de usuários com autenticação, dashboard e controle de permissões.

## Stacks

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7
- **Banco de Dados:** MySQL
- **Server:** XAMPP (Apache)
- **Bibliotecas:** js-brasil (validação e máscara de CPF)

## Requisitos

- XAMPP 7.4+ (Apache + PHP + MySQL)
- Git

## Requisitos

### 1. Instalar XAMPP
Baixe em: https://www.apachefriends.org/pt_BR/index.html

### 2. Copiar Projeto
```bash
# Copie a pasta 'sistema_gestao' para:
C:\xampp\htdocs\sistema_gestao
```

### 3. Iniciar Serviços
Abra `xampp-control.exe` e clique em "Start" para:
- Apache
- MySQL

### 4. Acessar o Projeto
```
http://localhost/sistema_gestao/src/pages/login.html
```

**Credenciais padrão:**
- Email: `admin@test.com`
- Senha: `123456`


## ⚙️ Inicialização do Banco de Dados

Se o banco não for criado automaticamente, acesse:
```
http://localhost/sistema_gestao/src/api/init_db.php
```

## 🔌 Endpoints da API Backend

Base URL: `http://localhost/sistema_gestao/src/api/index.php`

### Autenticação
```http
POST /index.php?action=login
{
    "email": "admin@test.com",
    "password": "123456"
}
```

### Usuários
```http
GET    /index.php/users           # Listar todos
GET    /index.php/users/:id       # Obter um
POST   /index.php/users           # Criar
PUT    /index.php/users/:id       # Atualizar
DELETE /index.php/users/:id       # Deletar
```

**Criar/Atualizar Usuário:**
```json
{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123",
    "cpf": "12345678900",
    "birth_date": "1990-01-15",
    "role": "user"
}
```

## Permissões

- **Admin:** Acesso total ao dashboard e gerenciamento de usuários
- **User:** Acesso restrito (visualizar apenas suas informações)

## Funcionalidades

- Login/Logout
- Criar, editar e deletar usuários
- Validação em tempo real (nome, email, CPF, senha)
- Dashboard com listagem de usuários
- =Notificações (Toast)
- Confirmação de ações


## Troubleshooting

| Problema | Solução |
|----------|---------|
| Erro de conexão com BD | Verifique se MySQL está rodando e credenciais em `config.php` |
| "Email already in use" | Use outro email ou delete o usuário anterior |
| Página em branco | Abra F12, verifique console para erros |
| CPF não valida | Recarregue a página (verifique se js-brasil carregou) |

## Notas

- O banco é criado automaticamente ao acessar `init_db.php`
- Senhas são armazenadas com hash bcrypt
- Sessão é mantida em localStorage
- CORS configurado para aceitar qualquer origem

