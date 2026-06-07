import paramiko

def update_env():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    env_path = '/home/victor/sistemarh/.env'
    
    new_values = {
        'MAIL_MAILER': 'smtp',
        'MAIL_HOST': 'smtp.resend.com',
        'MAIL_PORT': '587',
        'MAIL_USERNAME': 'resend',
        'MAIL_PASSWORD': 're_Swgt1Zy4_QA9pDEbgTiyJCN6eb7eV9YLG',
        'MAIL_ENCRYPTION': 'tls',
        'MAIL_FROM_ADDRESS': '"onboarding@resend.dev"',
        'MAIL_FROM_NAME': '"${APP_NAME}"'
    }

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        # Read env
        with sftp.open(env_path, 'r') as f:
            lines = f.readlines()
            
        updated_lines = []
        found_keys = set()
        
        for line in lines:
            line_stripped = line.strip()
            if not line_stripped or line_stripped.startswith('#'):
                updated_lines.append(line)
                continue
                
            if '=' in line_stripped:
                key = line_stripped.split('=', 1)[0].strip()
                if key in new_values:
                    updated_lines.append(f"{key}={new_values[key]}\n")
                    found_keys.add(key)
                else:
                    updated_lines.append(line)
            else:
                updated_lines.append(line)
                
        # Append keys that were not found
        for key, value in new_values.items():
            if key not in found_keys:
                updated_lines.append(f"{key}={value}\n")
                
        # Write back
        with sftp.open(env_path, 'w') as f:
            f.writelines(updated_lines)
            
        print("Env updated successfully.")
        
        # Clear cache
        commands = [
            f"cd /home/victor/sistemarh && php artisan config:clear",
            "sudo systemctl restart php8.4-fpm"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            if out: print(out)
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    update_env()
