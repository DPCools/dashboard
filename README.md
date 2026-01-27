# HomeDash - Personal Dashboard

A lightweight, self-hostable personal dashboard built with standalone PHP 8.3+, SQLite, and Tailwind CSS. Organize and access your homelab services through a clean, modern interface.

## Features

- **Clean Grid Layout**: Display services in an organized grid with categories
- **Multiple Pages**: Organize services across multiple dashboard pages
- **Dynamic Widgets**: 7 widget types for displaying real-time information (IP, Weather, System Stats, Clock, Notes, RSS, Proxmox)
- **Page-Level Privacy**: Password-protect individual pages or use global admin access
- **Item-Level Privacy**: Password-protect individual service items
- **Custom Icons**: Upload your own SVG icons or use 1000+ built-in Lucide icons
- **Easy Management**: Full CRUD interface for pages, items, and widgets
- **Auto-Refresh**: Widgets automatically update with configurable refresh intervals
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile
- **Dark Mode**: Automatic dark mode based on system preferences
- **Lightweight**: No heavy dependencies, fast load times
- **Self-Hosted**: Your data stays on your server with SQLite

## Requirements

- PHP 8.3 or higher
- Apache with mod_rewrite enabled
- PHP extensions: pdo_sqlite, sqlite3, session, hash
- File write permissions for the `data/` directory

## Installation

### Quick Start

1. **Clone Repository**
   ```bash
   cd /var/www/html
   git clone https://github.com/yourusername/homedash.git dashboard
   cd dashboard
   ```

2. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/dashboard
   sudo chmod 775 /var/www/html/dashboard/data
   sudo chmod 775 /var/www/html/dashboard/public/icons
   ```

3. **First Run Setup**
   - Visit `http://your-server/dashboard` in your browser
   - You'll be automatically redirected to the setup page
   - Click "Begin Setup" to create database and sample data
   - **Save the generated admin password** (shown only once!)
   - Click "Go to Dashboard" to start using HomeDash

For complete installation instructions, updating, backup/restore, and troubleshooting, see **[INSTALL.md](INSTALL.md)**.

### Updating HomeDash

To update to the latest version:

```bash
cd /var/www/html/dashboard
git pull origin main
```

Database migrations run automatically on first page load after update. No manual intervention needed!

## Usage

### Accessing the Dashboard

- Visit `http://your-server/dashboard` to view your main dashboard
- Click on page tabs to switch between different dashboard pages
- Click on any service card to open it in a new tab

### Admin Functions

1. **Login as Admin**
   - Click "Settings" or visit `/dashboard/settings`
   - Enter the admin password (default: `admin123`)

2. **Managing Pages**
   - Go to Settings to view all pages
   - Create new pages to organize different categories of services
   - Set pages as private to require password authentication
   - Reorder pages by changing their display order

3. **Managing Items**
   - Click "Add Item" on any page while logged in as admin
   - Edit or delete items using the buttons on each card
   - Organize items by category for automatic grouping
   - Choose from Lucide icons or upload custom SVG icons
   - Password-protect individual items for secure access

4. **Managing Widgets**
   - Click "Add Widget" on any page while logged in as admin
   - Choose from 7 widget types: External IP, Weather, System Stats, Clock, Notes, RSS Feed, Proxmox
   - Configure widget-specific settings (location for weather, timezone for clock, etc.)
   - Set custom refresh intervals (default 300 seconds / 5 minutes)
   - Widgets appear in a dedicated section at the top of each page with purple-themed styling
   - Drag and drop to reorder widgets within the page
   - Intelligent masonry grid layout automatically stacks smaller widgets alongside larger ones
   - Edit or delete widgets using the buttons on each widget card

5. **Custom Icons**
   - Go to Settings → "Manage Icons" to access the icon library
   - Upload multiple custom SVG icons at once with individual progress tracking
   - Automatic duplicate detection prevents uploading icons with the same filename
   - Manually copy SVG files to `/public/icons/` folder and click "Scan Folder"
   - Use the icon picker when creating/editing items or pages
   - Browse 1000+ built-in Lucide icons or use your custom uploads
   - Delete unused custom icons (system prevents deletion of icons in use)

### Page Privacy

- **Public Pages**: Accessible to anyone
- **Private Pages**: Require password authentication
- **Admin Override**: Admin password grants access to all pages

### Widgets

HomeDash supports 7 widget types that display dynamic information on your dashboard:

#### 1. External IP Widget
- Displays your public IP address
- Optional location information (city, region, country, postal code)
- Optional ISP information
- Uses free APIs: ipapi.co (primary) and ip-api.com (fallback)
- Cache: 5 minutes (IP rarely changes)

#### 2. Weather Widget
- Current weather conditions with temperature, description, humidity, wind
- Configurable location (city name or coordinates)
- Units: Metric (°C, km/h) or Imperial (°F, mph)
- Uses wttr.in API (free, no API key required)
- Cache: 30 minutes to respect rate limits

**Configuration:**
- Location: City name (e.g., "London", "Tokyo") or coordinates
- Units: Choose between metric and imperial
- Example: Set location to "San Francisco" and units to "imperial"

#### 3. System Stats Widget
- Real-time CPU load averages (1min, 5min, 15min)
- Memory usage with percentage and progress bar
- Disk usage with percentage and progress bar
- Configurable disk path (default: /)
- Linux/Unix only (gracefully degrades on other systems)
- No cache (real-time data)

**Note:** Requires Linux/Unix system. Will show error on Windows or if system files are not accessible.

#### 4. Clock Widget
- Live updating clock (updates every second on client-side)
- Configurable timezone (PHP timezone identifier)
- Time format: 12-hour (AM/PM) or 24-hour
- Optional seconds display
- No API calls or caching needed

**Configuration:**
- Timezone: Use PHP timezone identifiers (e.g., "America/New_York", "Europe/London", "Asia/Tokyo")
- Format: Choose 12h or 24h display
- Show seconds: Toggle seconds display

#### 5. Notes Widget
- Simple sticky note for reminders and quick references
- Plain text content (preserves line breaks)
- No API calls or caching
- Perfect for TODOs, server info, or quick notes

#### 6. RSS Feed Widget
- Displays latest items from RSS or Atom feeds
- Configurable number of items (1-20, default 5)
- Shows title, description, and relative publish date
- Clickable links open in new tab
- Cache: 15 minutes to respect feed rate limits

**Configuration:**
- Feed URL: Full URL to RSS/Atom feed (e.g., "https://hnrss.org/frontpage")
- Item count: Number of items to display (1-20)
- Compatible with RSS 2.0 and Atom formats

#### 7. Proxmox Cluster Widget
- Monitor your entire Proxmox cluster from your dashboard
- Displays cluster-wide statistics (nodes, VMs, LXCs, total CPU cores)
- Per-node details with CPU and RAM usage, free RAM, status indicators
- Shows VM and LXC counts per node
- Visual progress bars for resource utilization
- Handles self-signed SSL certificates automatically
- Cache: 60 seconds to minimize API calls

**Setup Instructions:**

1. **Create API Token in Proxmox:**
   - Log into Proxmox Web UI
   - Navigate to Datacenter → Permissions → API Tokens
   - Click "Add" to create a new token
   - User: `root@pam` (or your preferred user)
   - Token ID: `dashboard` (or any identifier)
   - **CRITICAL**: UNCHECK "Privilege Separation" checkbox
   - Click "Add" and copy the Token Secret (shown only once!)

2. **Assign Permissions:**
   - The user must have "PVEAuditor" role at minimum
   - For root@pam, this is already configured
   - For other users, assign role: Datacenter → Permissions → Add → User Permission

3. **Configure Widget:**
   - API URL: `https://192.168.1.100:8006` (your Proxmox server URL with port)
   - Username: `root@pam` (same as token user)
   - Token ID: `dashboard` (just the token name, not `root@pam!dashboard`)
   - Token Secret: Paste the secret from step 1
   - Enable cluster stats and node details as desired

**Troubleshooting:**
- **401 Authentication Error**: Verify "Privilege Separation" was unchecked when creating the token
- **Connection Failed**: Check API URL is correct (include https:// and port :8006)
- **SSL Errors**: Widget automatically disables SSL verification for self-signed certificates
- **Missing Data**: Ensure token user has PVEAuditor or Administrator role

### Widget Management

**Adding Widgets:**
1. Navigate to the page where you want to add a widget
2. Click "Add Widget" button (admin only)
3. Select widget type from dropdown
4. Fill in widget title and select icon
5. Configure widget-specific settings based on type
6. Set refresh interval (in seconds, 0 = no auto-refresh)
7. Click "Create Widget"

**Widget Placement:**
- Widgets appear in a dedicated section at the top of each page
- Displayed before service items with purple-themed styling
- Each page has its own set of widgets (page-specific)
- Intelligent masonry grid layout: smaller widgets automatically stack alongside larger ones
- Responsive columns: 1 (mobile), 2 (tablet), 3 (desktop)
- Grid automatically recalculates on widget refresh, resize, or reorder

**Widget Reordering:**
- Admin users can drag and drop widgets to reorder them
- Grab the grip icon and drag to desired position
- Order is saved automatically to the database
- Grid layout recalculates immediately after reordering

**Auto-Refresh:**
- Widgets automatically refresh in the background via AJAX
- Default refresh interval: 300 seconds (5 minutes)
- Configurable per widget (0 = disabled)
- Clock widget updates every second on client-side (no AJAX)
- Refresh happens without page reload

**Performance & Caching:**
- Widget data is cached to minimize API calls
- Cache durations optimized per widget type
- Expired cache entries cleaned automatically
- Background refresh doesn't block page rendering

## Sample Data

The initial installation includes 18 pre-configured services across 4 categories:

- **Homelab**: Proxmox, TrueNAS, Pi-hole, Home Assistant, Portainer, Grafana
- **Media**: Plex, Radarr, Sonarr, qBittorrent, Overseerr, Tautulli
- **Network**: pfSense, UniFi Controller, Uptime Kuma, Nginx Proxy Manager, Netdata
- **3D Printing**: OctoPrint

You can edit or delete these to match your actual services.

## Configuration

### Changing Admin Password

To change the admin password, update the database directly:

```bash
# Access SQLite database
sqlite3 /var/www/html/dashboard/data/dashboard.db

# Update admin password (replace 'your-new-password' with your actual password)
# You'll need to generate the hash first using PHP:
php -r "echo password_hash('your-new-password', PASSWORD_ARGON2ID);"

# Then update in database:
UPDATE settings SET value = 'YOUR_GENERATED_HASH' WHERE key = 'admin_password_hash';
```

### Customizing Settings

Settings are stored in the `settings` table:

- `site_title`: Dashboard title (default: "HomeDash")
- `theme`: Theme preference (default: "system")
- `items_per_row`: Number of items per row in grid (default: 4)

### Database Backup

Your entire dashboard configuration is stored in a single SQLite file:
```
/var/www/html/dashboard/data/dashboard.db
```

To backup, simply copy this file to a safe location.

## Security

### Built-in Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **XSS Prevention**: All output is escaped
- **SQL Injection Prevention**: Prepared statements used throughout
- **Password Security**: Argon2ID hashing algorithm
- **Session Management**: Secure session handling with periodic regeneration
- **Database Protection**: `.htaccess` prevents direct database access

### Best Practices

1. Change the default admin password immediately
2. Use strong, unique passwords for private pages
3. Keep PHP updated to the latest version
4. Regularly backup your database
5. Use HTTPS in production (configure at Apache/Nginx level)
6. Restrict access using firewall rules if needed

## File Structure

```
/var/www/html/dashboard/
├── index.php                    # Front controller
├── login.php                    # Authentication handler
├── logout.php                   # Session cleanup
├── .htaccess                    # URL rewriting & security
├── config/
│   ├── constants.php            # Application constants
│   └── database.php             # Database connection & migrations
├── app/
│   ├── models/                  # Data models (Page, Item, Icon, Widget)
│   ├── controllers/             # Business logic
│   ├── helpers/                 # Utility functions (Security, Auth, View, IconHelper)
│   ├── widgets/                 # Widget system
│   │   ├── Widget.php           # Abstract base class
│   │   ├── WidgetFactory.php    # Factory pattern
│   │   ├── ExternalIpWidget.php
│   │   ├── WeatherWidget.php
│   │   ├── SystemStatsWidget.php
│   │   ├── ClockWidget.php
│   │   ├── NotesWidget.php
│   │   ├── RssWidget.php
│   │   └── ProxmoxWidget.php
│   └── views/                   # HTML templates
│       ├── dashboard/           # Dashboard views
│       ├── widgets/             # Widget views & forms
│       ├── items/               # Item views & forms
│       └── ...
├── public/
│   ├── icons/                   # Custom SVG icon uploads
│   └── js/
│       └── icon-picker.js       # Icon picker component
├── data/
│   └── dashboard.db            # SQLite database
└── database/
    ├── schema.sql              # Database schema
    ├── seeds.sql               # Sample data
    └── migrations/             # Database migrations
        ├── 001_add_icon_system.sql
        ├── 002_*.sql
        ├── 003_add_item_password_protection.sql
        ├── 004_add_widget_system.sql
        └── 005_add_unique_icon_filename.sql
```

## Troubleshooting

### Database Not Created

If the database doesn't auto-create:
```bash
# Check directory permissions
ls -la /var/www/html/dashboard/data

# Should be writable by www-data
chmod 775 /var/www/html/dashboard/data
chown www-data:www-data /var/www/html/dashboard/data
```

### Clean URLs Not Working

Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Check that `.htaccess` is being read:
```bash
# In Apache config, ensure AllowOverride is set to All
# for the /var/www/html directory
```

### Session Issues

Check PHP session directory permissions:
```bash
# Check session.save_path in php.ini
php -i | grep session.save_path
```

## Development

Built with:
- **PHP 8.3+**: Modern PHP with strict types
- **SQLite**: Zero-configuration database
- **Tailwind CSS**: Utility-first CSS framework (via CDN)
- **Lucide Icons**: Beautiful, consistent icons (via CDN)

Coding standards:
- PSR-12 compliant
- Strict type declarations
- Prepared statements for all queries
- Comprehensive XSS/CSRF protection

## License

Free to use for personal and commercial projects.

## Documentation

- **[INSTALL.md](INSTALL.md)** - Complete installation and update guide
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and changes
- **[TASK.md](TASK.md)** - Development progress and completed tasks
- **[PLANNING.md](PLANNING.md)** - Architecture and design decisions

## Support

For issues, feature requests, or contributions:
- Check the documentation files above
- Review existing GitHub Issues
- Check Apache error logs: `/var/log/apache2/error.log`

---

**Version**: 1.1.0
**Last Updated**: 2026-01-26
