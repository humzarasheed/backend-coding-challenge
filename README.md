# Cloud-Based Collaborative Book Platform

## Overview

A cloud-based platform built with **Laravel 12** and **Filament Pannel** where users can create, save, and collaborate on books. Authors can manage books and sections, while collaborators contribute to sections.

### Key Technologies
- **Laravel 12** for backend
- **Filament** for admin dashboard
- **Filament Shield** for roles and permissions management
- **SQLite** for database (used for quicker setup)

## Features
- **User Roles**: Users can register as **Author** or **Collaborator**.
  - **Authors** can create books, manage sections, and assign roles/permissions.
  - **Collaborators** can edit sections and collaborate on books they have access to.
- **Book Management**: Authors can create books, and each book has multiple **Sections**.
- **Sections/Subsections**: Infinite nesting of sections is supported.
- **Database**: **SQLite** is used for the database to facilitate a quicker setup and development process.
  
Future features may include collaboration invitations, restricting access to invited collaborators only.

## Trade-offs
- **Filament**: Used for quick development of the admin panel, although not strictly necessary for the task. 
- **Role Management**: Filament Shield was chosen to handle roles/permissions efficiently, saving development time.
- **Collaboration**: All collaborators can currently collaborate on all books. Future feature: invitation system for restricted collaboration.
- **Database**: **SQLite** was chosen for its quick setup, making it easier to get started without dealing with complex database configurations. For production, it can be switched to a more robust database like MySQL or PostgreSQL.

## Installation Instructions
1. Clone the repository:
   ```bash
   git clone git@github.com:humzarasheed/backend-coding-challenge.git
2. Navigate to the project directory:
   ```bash
   cd backend-coding-challenge
3. Install dependencies:
    ```bash
    composer install
4. Copy the example environment file:
   ```bash
   cp .env.example .env
5. Generate the application key:
   ```bash
   php artisan key:generate
6. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
7. Start the development server:
   ```bash
   composer run dev



