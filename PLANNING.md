# Project Plan: HomeDash (Custom Dashboard)

### 📋 Overview
A lightweight, self-hostable personal dashboard inspired by Dashy, built with **PHP 8.2+** and **SQLite**. Designed for home use: low overhead, fast loading, and easy configuration.

---

### 🏗️ Technical Stack
* **Backend:** Standalone PHP 8.2+ (or Laravel Lite).
* **Database:** SQLite (Single file for easy backups).
* **Frontend:** Tailwind CSS + Shadcn/Radix UI patterns.
* **Icons:** Lucide-PHP or FontAwesome (CDN).
* **Authentication:** Simple session-based password protection.

---

### 🗄️ Database Schema (SQLite)
**Table: `pages`**
* `id` (INT, PK)
* `name` (TEXT) - e.g., "Main", "Network", "Home Lab".
* `slug` (TEXT, Unique) - e.g., "network-admin".
* `is_private` (BOOLEAN) - Default 0.
* `password_hash` (TEXT, Nullable) - Used if `is_private` is 1.

**Table: `items`**
* `id` (INT, PK)
* `page_id` (INT, FK)
* `title` (TEXT)
* `url` (TEXT)
* `icon` (TEXT)
* `description` (TEXT)
* `display_order` (INT)

---

### 🚀 Core Features

#### 1. The Grid Dashboard
* Responsive grid layout using Tailwind.
* Cards displaying Service Name, Icon, and a "Status Indicator" (simple ping to check if the URL is up).
* Section headers to group items (e.g., "Media", "Smart Home").

#### 2. Selective Privacy (Password Protection)
* Ability to mark a page as "Private".
* If a page is private:
    * Prompt user for a password before rendering items.
    * Store a simple session token to keep the user logged in for that session.
    * Option to set a global password or a per-page password.

#### 3. Simple UI Editor
* A "Settings" mode to add, edit, or delete items and pages.
* Drag-and-drop ordering (optional/future enhancement).

---

### 🛠️ Architecture Patterns
* **Routing:** Simple `index.php?page=slug` or clean URL rewriting via `.htaccess`.
* **Security:**
    * Use `password_hash()` and `password_verify()` for page access.
    * Use **PDO** for all database queries to prevent SQL injection.
    * Sanitize all output to prevent XSS.
* **State Management:** Minimal. Use a local SQLite file to store all configurations instead of a heavy YAML file.

---

### 🎨 Design Goals (Home Use)
* **Cleanliness:** No cluttered sidebars; focus on the links.
* **Speed:** No heavy JS frameworks. Standard PHP SSR (Server Side Rendering).
* **Themes:** Simple Light/Dark mode toggle based on system settings.

---

### 📅 Phase 1: MVP (Minimum Viable Product)
1.  Initialize SQLite database with `pages` and `items`.
2.  Create a basic PHP script to fetch and display items on a grid.
3.  Implement the `is_private` check logic: 
    * If `is_private == 1`, redirect to `login.php`.
4.  Apply Tailwind CSS styling for a modern, "Dashy-like" look.
