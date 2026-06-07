import paramiko

def view_config():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    remote_config = '/home/victor/sistemarh/config/mail.php'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        with sftp.open(remote_config, 'r') as f:
            lines = f.readlines()
            
        print("--- config/mail.php snippet ---")
        in_smtp = False
        for line in lines:
            if "'smtp' => [" in line:
                in_smtp = True
            if in_smtp:
                print(line.rstrip('\n'))
                if "]," in line:
                    break
        sftp.close()
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    view_config()
