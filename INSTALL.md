# HomeDash Installation Guide

Complete guide for installing and updating HomeDash via Git.

## 🚀 Fresh Installation

### Prerequisites

- PHP 8.3 or higher
- Apache with mod_rewrite enabled
- PHP extensions: `pdo_sqlite`, `sqlite3`, `session`, `hash`
- Git installed on your server

### Step 1: Clone Repository

```bash
cd /var/www/html
git clone https://github.com/yourusername/homedash.git dashboard
cd dashboard
```

### Step 2: Set Permissions

```bash
# Set ownership to web server user
sudo chown -R www-data:www-data /var/www/html/dashboard

# Set directory permissions
find /var/www/html/dashboard -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/html/dashboard -type f -exec chmod 644 {} \;

# Make data directory writable
sudo chmod 775 /var/www/html/dashboard/data

# Make icons directory writable
sudo chmod 775 /var/www/html/dashboard/public/icons
```

### Step 3: First Run Setup

1. Open your browser and navigate to:
   ```
   http://your-server/dashboard
   ```

2. You'll be automatically redirected to the setup page

3. Click **"Begin Setup"** to:
   - Create the database with sample data
   - Generate a random admin password
   - Apply all database migrations

4. **IMPORTANT**: Copy the generated admin password shown on screen
   - This password is shown only once
   - Use the "Copy" button to save it securely
   - You can change it later in the dashboard settings

5. Click **"Go to Dashboard"** to start using HomeDash

## 🔄 Updating HomeDash

When you pull updates from Git, HomeDash automatically detects and applies new database migrations.

### Update Process

```bash
cd /var/www/html/dashboard

# Pull latest changes
git pull origin main

# Fix permissions if needed
sudo chown -R www-data:www-data .
sudo chmod 775 data public/icons
```

### What Happens on Update

1. **First Request After Pull**:
   - HomeDash checks the database version
   - Automatically applies pending migrations
   - Updates version tracking

2. **Zero Downtime**:
   - Migrations run seamlessly
   - No manual database changes needed
   - Existing data is preserved

3. **Version Tracking**:
   - Current version stored in database
   - Migration history tracked in `migrations` table
   - Each migration runs only once

## 🗃️ Database Management

### Backup Database

Your entire dashboard configuration is in a single SQLite file:

```bash
# Create backup
cp /var/www/html/dashboard/data/dashboard.db ~/backups/dashboard-$(date +%Y%m%d).db

# Automated daily backup (add to crontab)
0 2 * * * cp /var/www/html/dashboard/data/dashboard.db ~/backups/dashboard-$(date +\%Y\%m\%d).db
```

### Restore Database

```bash
# Stop web server (optional)
sudo systemctl stop apache2

# Restore backup
cp ~/backups/dashboard-20260126.db /var/www/html/dashboard/data/dashboard.db

# Fix permissions
sudo chown www-data:www-data /var/www/html/dashboard/data/dashboard.db
sudo chmod 664 /var/www/html/dashboard/data/dashboard.db

# Start web server
sudo systemctl start apache2
```

### Reset Database

If you want to start fresh:

```bash
# Delete existing database
rm /var/www/html/dashboard/data/dashboard.db

# Visit the dashboard in browser
# You'll be redirected to setup page automatically
```

Or use the setup page directly:
```
http://your-server/dashboard/setup.php
```

## 🔐 Security

### Change Admin Password

After first setup, change the generated password:

1. Log into dashboard with generated password
2. Go to **Settings**
3. Scroll to **Admin Password** section
4. Enter new password and confirm
5. Click **Save Changes**

### Generate New Admin Password

If you lost the admin password, generate a new one:

```bash
# Generate new password hash
php -r "echo password_hash('your-new-password', PASSWORD_ARGON2ID) . PHP_EOL;"

# Update database (replace YOUR_HASH with output from above)
sqlite3 /var/www/html/dashboard/data/dashboard.db "UPDATE settings SET value = 'YOUR_HASH' WHERE key = 'admin_password_hash';"
```

## 📁 File Structure

Files excluded from Git (via `.gitignore`):

- `data/dashboard.db` - Your database (not tracked)
- `public/icons/*.svg` - Custom uploaded icons (not tracked)
- `data/sessions/*` - PHP sessions (not tracked)

Files tracked in Git:

- All PHP source code
- Database schema (`database/schema.sql`)
- Database seeds (`database/seeds.sql`)
- All migrations (`database/migrations/*.sql`)
- Configuration templates

## 🛠️ Troubleshooting

### Setup Page Not Loading

**Issue**: Redirect loop or 404 error

**Solution**:
```bash
# Check .htaccess is present and readable
ls -la /var/www/html/dashboard/.htaccess

# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check Apache config allows .htaccess
# In /etc/apache2/sites-available/000-default.conf:
# <Directory /var/www/html>
#     AllowOverride All
# </Directory>
```

### Database Permission Errors

**Issue**: Cannot create database file

**Solution**:
```bash
# Ensure data directory is writable
sudo chown www-data:www-data /var/www/html/dashboard/data
sudo chmod 775 /var/www/html/dashboard/data

# Check SELinux (if applicable)
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/dashboard/data
```

### Migrations Not Running

**Issue**: New features missing after git pull

**Solution**:
```bash
# Check migration table
sqlite3 /var/www/html/dashboard/data/dashboard.db "SELECT * FROM migrations;"

# Check for errors in Apache logs
sudo tail -f /var/log/apache2/error.log

# Manually run a specific migration
sqlite3 /var/www/html/dashboard/data/dashboard.db < database/migrations/005_add_unique_icon_filename.sql
```

### Check Current Version

```bash
# Check version in database
sqlite3 /var/www/html/dashboard/data/dashboard.db "SELECT * FROM settings WHERE key IN ('app_version', 'db_version');"

# Check version in code
cat /var/www/html/dashboard/VERSION
```

## 🔄 Migration System

### How Migrations Work

1. **Migrations Table**: Tracks which migrations have been executed
2. **Automatic Execution**: Runs on every page load (checks for new migrations)
3. **Idempotent**: Each migration runs only once
4. **Ordered**: Migrations run in alphanumeric order (001, 002, 003, etc.)

### Migration File Format

```sql
-- Migration filename: 006_add_new_feature.sql

-- Add your SQL changes here
ALTER TABLE items ADD COLUMN new_field TEXT;

-- Create indexes
CREATE INDEX idx_items_new_field ON items(new_field);

-- Update existing data if needed
UPDATE items SET new_field = 'default_value' WHERE new_field IS NULL;
```

### Check Migration Status

```bash
# View all executed migrations
sqlite3 /var/www/html/dashboard/data/dashboard.db << EOF
SELECT
    migration,
    executed_at
FROM migrations
ORDER BY id;
EOF
```

## 📊 Version History

### v1.1.0 (Current)
- Proxmox cluster monitoring widget
- Widget drag-and-drop reordering
- Intelligent masonry grid layout
- Multiple icon upload with progress tracking
- Unique icon filename constraints
- External IP widget location accuracy improvements
- First-run setup with random password generation
- Automatic migration system

### v1.0.0
- Initial release
- Multi-page dashboard
- Widget system (6 widget types)
- Item password protection
- Custom icon upload
- Dark mode support

## 📝 Development

### Creating a New Migration

1. Create file in `database/migrations/`:
   ```bash
   nano database/migrations/006_add_my_feature.sql
   ```

2. Add your SQL changes:
   ```sql
   -- Description of changes
   ALTER TABLE items ADD COLUMN my_field TEXT;
   ```

3. Commit and push:
   ```bash
   git add database/migrations/006_add_my_feature.sql
   git commit -m "Add migration for my_feature"
   git push
   ```

4. On other installations, git pull will automatically apply the migration on next page load

### Testing Migrations

```bash
# Create test database
cp data/dashboard.db data/dashboard-test.db

# Test migration manually
sqlite3 data/dashboard-test.db < database/migrations/006_add_my_feature.sql

# Verify changes
sqlite3 data/dashboard-test.db ".schema items"

# If successful, remove test database
rm data/dashboard-test.db
```

## 🆘 Support

- **Documentation**: Check README.md for feature documentation
- **Issues**: Report bugs via GitHub Issues
- **Logs**: Check `/var/log/apache2/error.log` for PHP errors

---

**Version**: 1.1.0
**Last Updated**: 2026-01-26
