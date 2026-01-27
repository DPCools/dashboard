-- HomeDash Seed Data

-- Insert default settings
INSERT OR IGNORE INTO settings (key, value) VALUES
    ('admin_password_hash', '$argon2id$v=19$m=65536,t=4,p=1$aFhWd0xMdXZub3RYa1NwMg$jeep4MY7ynr3oqXPWEmcO0Os3q9jqDkm/WaZzngrQ/A'), -- Default: admin123
    ('site_title', 'HomeDash'),
    ('theme', 'system'),
    ('items_per_row', '4');

-- Insert main page
INSERT OR IGNORE INTO pages (id, name, slug, is_private, icon, display_order) VALUES
    (1, 'Main Dashboard', 'main', 0, 'layout-dashboard', 0);

-- Insert Homelab items (Category: Homelab)
INSERT OR IGNORE INTO items (page_id, title, url, icon, description, category, display_order) VALUES
    (1, 'Proxmox', 'https://proxmox.local:8006', 'server', 'Virtualization Management', 'Homelab', 1),
    (1, 'TrueNAS', 'https://truenas.local', 'hard-drive', 'Network Storage', 'Homelab', 2),
    (1, 'Pi-hole', 'http://pihole.local/admin', 'shield', 'Network-wide Ad Blocking', 'Homelab', 3),
    (1, 'Home Assistant', 'http://homeassistant.local:8123', 'home', 'Smart Home Automation', 'Homelab', 4),
    (1, 'Portainer', 'https://portainer.local:9443', 'container', 'Docker Management', 'Homelab', 5),
    (1, 'Grafana', 'http://grafana.local:3000', 'bar-chart-2', 'Monitoring & Analytics', 'Homelab', 6);

-- Insert Media items (Category: Media)
INSERT OR IGNORE INTO items (page_id, title, url, icon, description, category, display_order) VALUES
    (1, 'Plex', 'http://plex.local:32400/web', 'film', 'Media Server', 'Media', 7),
    (1, 'Radarr', 'http://radarr.local:7878', 'video', 'Movie Management', 'Media', 8),
    (1, 'Sonarr', 'http://sonarr.local:8989', 'tv', 'TV Show Management', 'Media', 9),
    (1, 'qBittorrent', 'http://qbittorrent.local:8080', 'download', 'Torrent Client', 'Media', 10),
    (1, 'Overseerr', 'http://overseerr.local:5055', 'search', 'Media Request Management', 'Media', 11),
    (1, 'Tautulli', 'http://tautulli.local:8181', 'activity', 'Plex Statistics', 'Media', 12);

-- Insert Network/Admin items (Category: Network)
INSERT OR IGNORE INTO items (page_id, title, url, icon, description, category, display_order) VALUES
    (1, 'pfSense', 'https://pfsense.local', 'network', 'Firewall & Router', 'Network', 13),
    (1, 'UniFi Controller', 'https://unifi.local:8443', 'wifi', 'Network Management', 'Network', 14),
    (1, 'Uptime Kuma', 'http://uptime.local:3001', 'activity', 'Service Monitoring', 'Network', 15),
    (1, 'Nginx Proxy Manager', 'http://nginx.local:81', 'globe', 'Reverse Proxy', 'Network', 16),
    (1, 'Netdata', 'http://netdata.local:19999', 'cpu', 'Real-time Monitoring', 'Network', 17);

-- Insert 3D Printing items (Category: 3D Printing)
INSERT OR IGNORE INTO items (page_id, title, url, icon, description, category, display_order) VALUES
    (1, 'OctoPrint', 'http://octoprint.local', 'printer', '3D Printer Control', '3D Printing', 18);
