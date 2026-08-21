# Gestão de Actividades — Sistema de Gestão de Eventos e Presenças

Sistema web desenvolvido em **Laravel** e **Livewire** para gestão completa de eventos, inscrições de participantes, pagamentos e controlo de presenças via QR Code.

<p align="center">
  <img alt="Version" src="https://img.shields.io/badge/version-1.0.0-blue.svg?cacheSeconds=2592000" />
  <img alt="License" src="https://img.shields.io/badge/license-MIT-green.svg" />
</p>

🏠 **Homepage:** [gestao-eventos-ipukb.onrender.com](https://gestao-eventos-ipukb.onrender.com/)
✨ **Demo:** [gestao-eventos-ipukb.onrender.com](https://gestao-eventos-ipukb.onrender.com/)

## Tecnologias

- **PHP 8.2+** + **Laravel** (framework backend)
- **Livewire** + **Flux UI** (componentes dinâmicos e reativos em tempo real)
- **Laravel Fortify** (autenticação, verificação de email e autenticação de dois fatores)
- **Tailwind CSS** + **Alpine.js** (interface e interatividade frontend)
- **Spatie Laravel-Permission** (gestão de papéis e permissões de acesso)
- **SQLite** (ambiente local/desenvolvimento) / **MySQL / MariaDB** (produção)

## Estrutura do Projecto

```
gestao_de_atividades/
├── app/
│   ├── Actions/
│   ├── Concerns/
│   ├── Http/Controllers/
│   ├── Livewire/
│   │   ├── Actions/             # Ex: Logout
│   │   └── Settings/            # Perfil, Segurança, Two-Factor, Aparência
│   ├── Models/                  # Evento, Inscricao, Presenca, Post, Role, Permission, User
│   ├── Notifications/           # Boas-vindas, Inscrição (confirmada/cancelada/rejeitada),
│   │                             # Presença confirmada, Verificação de email
│   └── Providers/                # AppServiceProvider, FortifyServiceProvider
├── config/                      # Configurações do projeto, auth, fortify, livewire, permissões
├── database/
│   ├── factories/
│   ├── migrations/              # Eventos, Inscrições, Presenças, Permissões, Posts,
│   │                             # QR Code, Preço, Pagamento, Comprovativo, Soft Deletes
│   ├── seeders/                 # DatabaseSeeder, RolesAndPermissionsSeeder
│   └── database.sqlite
├── public/                      # Assets estáticos (CSS, JS, imagens)
├── resources/
│   |
│   └── views/
│       ├── components/
│       |    |── admin/           # Comprovativos
│       |    ├── clientes/
│       |    ├── eventos/
│       |    ├── inscricoes/
│       |    ├── novo-usuario/
│       |    ├── permissions/
│       |    ├── posts/
│       |    ├── presencas/
│       |    ├── roles/
│       |    ├── settings/
│       |    ├── users/
│       ├── flux/                # Componentes Flux UI
│       └── livewire/
├── routes/
│   └── web.php                  # Rotas da aplicação web
├── casos_de_uso.md              # Documentação de casos de uso
└── deployment/                  # Configurações de deployment
```

## Como Instalar e Executar

1. Certifique-se que tem o **PHP** (>= 8.2) e o **Composer** instalados:
   ```bash
   php -v
   composer -v
   ```

2. Clone o repositório e entre na pasta do projecto:
   ```bash
   git clone https://github.com/Manuel-Pedro1996/gestao_eventos_ipukb.git
   cd gestao_de_atividades
   ```

3. Instale as dependências do PHP e Node:
   ```bash
   composer install
   npm install
   ```

4. Configure o ficheiro de ambiente:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. (Opcional) Por padrão o projeto usa **SQLite** localmente. Se preferir MySQL/MariaDB, atualize as variáveis `DB_*` no `.env`. Para SQLite, garanta que o ficheiro existe:
   ```bash
   touch database/database.sqlite
   ```

6. Execute as migrações com dados iniciais (papéis, permissões e utilizadores de teste):
   ```bash
   php artisan migrate --seed
   ```

7. Inicie o servidor de desenvolvimento e a compilação dos assets:
   ```bash
   composer run dev
   ```

8. Abra o navegador em:
   ```
   http://127.0.0.1:8000
   ```

## Funcionalidades Principais

| Recurso                        | Descrição                                                                 |
| -------------------------------- | -------------------------------------------------------------------------- |
| **Gestão de Eventos**            | Criação, edição, definição de preço, agendamento e detalhes de atividades  |
| **Inscrições**                    | Formulários dinâmicos para registo de participantes                        |
| **Pagamentos e Comprovativos**   | Upload e validação de comprovativo de pagamento por parte do participante  |
| **Check-in via QR Code**         | Controlo e registo prático de presenças através de código QR único         |
| **Autenticação Segura**          | Login, verificação de email e autenticação de dois fatores (2FA)           |
| **Gestão de Utilizadores**       | Atribuição de perfis e permissões ( aos utilizadores do sisttema)       |
| **Notificações**                  | Emails automáticos de boas-vindas, confirmação/rejeição de inscrição e presença 

## Papéis e Permissões

O sistema utiliza **Spatie Laravel-Permission** para controlo de acesso baseado em papéis (roles), garantindo que:

- **Administradores** têm acesso total à gestão de eventos, utilizadores, cargos e permissões.
- **Organizadores** gerem os seus próprios eventos e respetivas inscrições/presenças.
- **Participantes/Clientes** acedem apenas às suas inscrições e comprovativos.

## Notas

- A aplicação conta com integração em produção ativa acessível em [gestao-eventos-ipukb.onrender.com](https://gestao-eventos-ipukb.onrender.com/).
- O ambiente de desenvolvimento local e em produção utiliza MySQL/MariaDB.
- Recomenda-se manter a base de dados atualizada para suportar as operações do Eloquent ORM sem inconformidades de schema.

## Autor

👤 **Manuel Graciano Sahando Pedro**

- GitHub: [@Manuel-Pedro1996](https://github.com/Manuel-Pedro1996)
- LinkedIn: [@pedro-manuel-pedro-a0630b318](https://linkedin.com/in/pedro-manuel-pedro-a0630b318)

## Licença

Este projeto está licenciado sob a licença MIT.

## Mostre o seu apoio

Dê uma ⭐️ se este projeto o ajudou!

---