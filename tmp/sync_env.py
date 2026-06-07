import paramiko
import os

def sync_env():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    local_env_path = 'C:/Dev/Web/SistemaRH_Laravel/.env'
    remote_env_path = '/home/victor/sistemarh/.env'
    
    # 1. Read local env keys
    local_keys = {}
    with open(local_env_path, 'r', encoding='utf-8') as f:
        local_lines = f.readlines()
        
    for line in local_lines:
        line_stripped = line.strip()
        if line_stripped and not line_stripped.startswith('#') and '=' in line_stripped:
            key, val = line_stripped.split('=', 1)
            local_keys[key.strip()] = line_stripped
            
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        # 2. Read remote env keys
        with sftp.open(remote_env_path, 'r') as f:
            remote_lines = f.readlines()
            
        remote_keys = set()
        for line in remote_lines:
            line_stripped = line.strip()
            if line_stripped and not line_stripped.startswith('#') and '=' in line_stripped:
                key = line_stripped.split('=', 1)[0].strip()
                remote_keys.add(key)
                
        # 3. Find missing keys
        missing_lines = []
        for key, full_line in local_keys.items():
            if key not in remote_keys:
                missing_lines.append(full_line + '\\n')
                print(f"Adding missing key: {key}")
                
        if missing_lines:
            remote_lines.append('\\n# Variables agregadas en la sincronización\\n')
            remote_lines.extend(missing_lines)
            
            with sftp.open(remote_env_path, 'w') as f:
                f.writelines(remote_lines)
            print("Remote .env updated.")
            
            # 4. Clear config
            ssh.exec_command("cd /home/victor/sistemarh && php artisan config:clear && php artisan config:cache")
            ssh.exec_command("sudo systemctl restart php8.4-fpm")
            print("Cache cleared and PHP-FPM restarted.")
        else:
            print("No missing variables found. Remote .env is up to date.")
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    sync_env()
