# Su Elevation Support - Testing & Usage Guide

## Overview

The su elevation feature allows HomeDash to execute commands on remote hosts that require two-step authentication:
1. **SSH Connection**: Initial login with basic user credentials (e.g., `bsmith`)
2. **Su Elevation**: Switch to privileged user (e.g., `root` or `docker`) to execute commands

This is particularly useful for Docker hosts where:
- SSH access is granted to regular users
- Docker commands require root or docker group privileges
- Sudo is not configured, but su is available

## How It Works

### Command Wrapping
When su elevation is enabled, commands are automatically wrapped:

```bash
# Original command
docker ps

# Wrapped command (executed on remote host)
echo '<su_password>' | su - root -c 'docker ps'
```

**Note:** The `su` command is used WITH the `-` flag to create a login shell, which properly initializes the target user's environment, changes to their home directory, and avoids permission issues with the source user's files.

### Security Features
✅ **Encrypted Credentials**: Su passwords encrypted with AES-256-CBC at rest
✅ **Injection Prevention**: Proper shell escaping prevents command injection
✅ **Audit Logging**: Original commands logged (not wrapped version with password)
✅ **Short Exposure**: Password only visible in process list during execution (milliseconds)

## Configuration

### Step 1: Add Host with Su Elevation

1. Navigate to **Settings → Remote Commands → Hosts** tab
2. Click **"Add Host"**
3. Fill in SSH credentials:
   - **Name**: Docker01 (or any descriptive name)
   - **Host Type**: SSH
   - **Hostname**: 192.168.0.57
   - **Port**: 22
   - **Username**: bsmith (SSH login user)
   - **Authentication**: Password
   - **Password**: <basic_user_ssh_password>

4. Enable privilege elevation:
   - ✓ Check **"Use su elevation"**
   - **Su Username**: root (or docker, dockeradmin, etc.)
   - **Su Password**: <root_or_docker_user_password>
   - **Shell**: /bin/bash (default, change if needed)

5. Click **"Add Host"**

### Step 2: Test Connection

1. Click **"Test"** button next to your host
2. Expected results:
   - ✅ **Success**: "Connection successful! Su elevation verified (running as root)."
   - ❌ **Failure**: Clear error message indicating SSH or su issue

### Step 3: Execute Commands

**Option A: Using Templates Tab**
1. Navigate to **Templates** tab
2. Find Docker command template (e.g., "List Docker Containers")
3. Click **"Execute"**
4. Select **Docker01** host
5. Click **"Execute"**
6. View output showing container list

**Option B: Using Command Widget**
1. Create a Command Widget on your dashboard
2. Select template (e.g., "docker ps")
3. Select host (Docker01)
4. Configure auto-refresh if needed
5. Widget will display Docker containers with su elevation

## Troubleshooting

### "Su elevation enabled but credentials not configured"
**Problem**: Host has su elevation enabled but su_password is missing
**Solution**: Edit host and re-enter su password

### Connection successful but "su: Authentication failure"
**Problem**: Incorrect su password
**Solution**:
1. Verify su password is correct (test manually: `ssh user@host`, then `su - root`)
2. Edit host and update su password

### "Su elevation may not be working correctly"
**Problem**: Connection succeeds but `whoami` returns wrong user
**Solution**:
1. **Common mistake**: Verify su username is NOT the same as SSH username
   - SSH Username: `bsmith` (login user)
   - Su Username: `root` or `docker` (target privileged user)
2. Check su username is correct (should be different from SSH user)
3. Verify SSH user has permission to su to target user
4. Check shell path is valid on remote system

### Commands timeout with su elevation
**Problem**: Su command hangs waiting for input
**Solution**:
1. Verify su accepts password via stdin on your system
2. Try alternative shell (e.g., `/bin/sh` instead of `/bin/bash`)
3. Check if su requires TTY (may need `su -p` flag - contact support)

### Docker commands return "permission denied"
**Problem**: Su elevation working but Docker still denies access
**Solution**:
1. Verify elevated user has Docker permissions:
   ```bash
   # Test manually
   su - root -c 'docker ps'
   ```
2. Check if user is in docker group (if not elevating to root)
3. Verify Docker daemon is running

## Example Commands for Docker01

Once configured with su elevation, you can execute:

### Container Management
- `docker ps` - List running containers
- `docker ps -a` - List all containers
- `docker stats --no-stream` - Show container resource usage
- `docker images` - List Docker images

### System Information
- `docker info` - Display Docker system info
- `docker version` - Show Docker version
- `df -h` - Disk space (if elevating to root)
- `free -h` - Memory usage (if elevating to root)

### Network & Volumes
- `docker network ls` - List Docker networks
- `docker volume ls` - List Docker volumes

## Best Practices

### Security
1. **Minimize Privileges**: Only elevate to users with necessary permissions
   - If only Docker commands needed, use `docker` group user instead of `root`
2. **Credential Rotation**: Regularly update su passwords
3. **Audit Regularly**: Review command execution history in History tab
4. **Template Whitelisting**: Only create templates for approved commands

### Performance
1. **Connection Timeouts**: Default 10 seconds - increase for slow networks
2. **Command Timeouts**: Adjust per template based on expected execution time
3. **Rate Limiting**: Max 10 commands per minute per session

### Maintenance
1. **Test Connections**: Periodically test hosts to ensure credentials valid
2. **Monitor Failures**: Check History tab for failed executions
3. **Update Credentials**: If su password changes, update in host settings

## Comparison: Su vs Sudo

| Feature | Su Elevation (Current) | Sudo (Future) |
|---------|------------------------|---------------|
| Password via stdin | ✅ Yes (`echo pass \| su`) | ❌ Requires TTY or NOPASSWD |
| Configuration | ✅ Simple (just su password) | Requires sudoers configuration |
| Security | Moderate (password in process) | Better (leverages sudo logging) |
| Compatibility | Works on most systems | Requires sudo configured |
| Implementation | ✅ Completed | Future enhancement |

## Common Configuration Mistakes

**❌ WRONG - Su username same as SSH username:**
```
SSH Username: bsmith
Su Username: bsmith  ← This doesn't make sense!
```

**✅ CORRECT - Different users for SSH and su:**
```
SSH Username: bsmith (regular user for SSH login)
Su Username: root (privileged user for command execution)
```

**Why?** The point of su elevation is to log in as a regular user via SSH, then elevate to a privileged user to run commands. If both are the same, no elevation happens.

## Advanced: Multiple Hosts

You can configure different elevation settings per host:

**Host: Docker01**
- SSH User: bsmith
- Su User: docker
- Use Case: Docker management

**Host: Docker02**
- SSH User: admin
- Su User: root
- Use Case: Full system administration

**Host: pve1 (Proxmox)**
- SSH User: root
- Su Elevation: Disabled
- Use Case: Direct root SSH access

## Support & Feedback

If you encounter issues:
1. Check Apache error logs: `tail -f /var/log/apache2/error.log`
2. Check command execution history in dashboard
3. Test SSH and su manually before configuring in dashboard
4. Report issues at: https://github.com/DPCools/dashboard/issues

## Technical Details

### Database Schema
Su settings stored in `command_hosts.connection_settings` JSON field:
```json
{
  "use_su_elevation": true,
  "su_username": "root",
  "su_shell": "/bin/bash"
}
```

Su password stored in `command_credentials` table:
- `credential_type` = `'su_password'`
- `encrypted_value` = AES-256-CBC encrypted password
- `encryption_iv` = Initialization vector for decryption

### Escape Sequences
Password escaping (single quotes):
```php
// Original: My'Pass"word
// Escaped: My'\''Pass"word
str_replace("'", "'\\''", $password)
```

Command escaping (preserves all characters):
```php
// Ensures command executes as-is after su
str_replace("'", "'\\''", $command)
```

### Exit Code Handling
The system captures exit codes through command wrapping:
```bash
(printf '%s\n' 'password' | su - user -c 'docker ps') 2>&1; echo "__EXIT_CODE__:$?"
```

Exit code `0` = Success
Exit code `1` = Su authentication failure or command error
Exit code `124` = Command timeout

---

**Version**: 1.0.0
**Last Updated**: 2026-01-29
**Compatible with**: HomeDash v1.1.0+
