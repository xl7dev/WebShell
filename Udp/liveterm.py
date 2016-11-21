import sys
from io import StringIO
from PyQt5.QtWidgets import QApplication, QMainWindow
from PyQt5.QtWidgets import QLabel, QLineEdit, QPushButton, QGridLayout, QWidget
from PyQt5.QtGui import QIcon, QPalette
from PyQt5.QtCore import QTimer, pyqtSignal, pyqtSlot, Qt
from console import Console
import socket
from PyQt5.QtCore import QCoreApplication
from time import sleep

class MainWindow(QMainWindow):
    
	def __init__(self, parent=None):
		super(MainWindow, self).__init__(parent)
		#icon = QIcon("logo.png")
		#self.setWindowIcon(icon)
		self.setWindowTitle("LiveHack by nAkoustix v0.1")
		
		#pal = self.palette()
		#pal.setColor(QPalette.Base, Qt.black)
		#pal.setColor(QPalette.Text, Qt.green)
		#self.setPalette(pal)
		#self.setAutoFillBackground(True)
		
		self.console = Console(self)
		self.lay = QGridLayout()
		self.lay.addWidget(self.console, 0,0)
        
		cw = QWidget(self)
		cw.setLayout(self.lay)
		self.setCentralWidget(cw)
		
		self.socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
		self.socket.setblocking(0)
		self.localAddr = ("localhost", 9001)
		self.socket.bind(self.localAddr)
		
		self.socksend = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
		self.remoteAddr = ("localhost", 9000)
		
		self.buffer = []
		self.indentation = 0
		
		self.readTimer = QTimer(self)
		self.readTimer.timeout.connect(self.readData)
		
		self.console.newLine.connect(self.interpretLine)
		self.console.indentationPlus.connect(self.indentPlus)
		self.console.indentationMinus.connect(self.indentMinus)
		
		self.readTimer.start(50)
		
		self.console.makePrompt()
		
	def indentPlus(self):
		print("indent +")
		self.indentation += 1
		
	def indentMinus(self):
		print("indent -")
		self.indentation -= 1
		
	def interpretLine(self, line):
		if line[:3] != ">>>" and line[:3] != "...":
			self.console.putData("\n")
			self.console.makePrompt()
			return
		line = line[4:]
		if line[:1] == "!":
			line = line[1:]
#-- Escape chars ----------------------
			print("Escape char!")
			if line == "knock":
				print("knock, knock, knocking on Liiives door...")
				self.console.putData("\n\\0/ - Client says:\t\"Knock knock.. Hi Live! may I come in?\"")
				self.socksend.sendto("!knock".encode("utf-8"), self.remoteAddr)
			elif line == "clear":
				self.socksend.sendto("!clear".encode("utf-8"), self.remoteAddr)
				self.clear()
				self.console.clear()
			elif line == "exit":
				print("Bye!!")
				self.exitTimer = QTimer(self)
				self.console.putData("\nBye!!")
				#self.exitTimer.timeout.connect(QCoreApplication.instance.quit)
				self.exitTimer.timeout.connect(self.close)
				self.exitTimer.start(800)
				#QCoreApplication.instance().quit()
			else:
				self.console.putData("\nUnknown escape sequence!")
				self.makePrompt()
			self.clear()
			return
		self.buffer += [line]
		#self.console.addToCmdHistory(line)
#-- Editor behaviour ------------------
		if line[-1:] == ":":
			self.console.putData("\n")
			self.indentation += 1
			self.console.makeExtendedPrompt(self.indentation)
			print(": found")
			print("line")
			#debug("1");
		else:
			self.console.putData("\n")
			if self.indentation == 0:
				self.execute()
				self.console.makePrompt()
				#debug("2");
			else:
				#self.indentation = 0
				stripped = line.replace(" ", "")
				if len(stripped) == 0:
					self.indentation = 0
					self.execute()
					self.console.makePrompt()
					#debug("3");
				else:
					#debug("4");
					self.console.makeExtendedPrompt(self.indentation)
	def clear(self):
		self.indentation = 0;
		self.buffer = []
				
	def execute(self):
		print("execute")
		print(self.indentation)
		s = ""
		for line in self.buffer:
			s += line + "\n"
		# check if there is anything
		if len(s.replace("\n", "")) == 0:
			print("execution aborted")
		else:
			print(s)
			self.socksend.sendto(bytes(s, "utf-8"), self.remoteAddr)
			self.socksend.sendto("!execute".encode("utf-8"), self.remoteAddr)
		self.clear()
		
	def readData(self):
		try:
			while 1:
				self.data, self.addr = self.socket.recvfrom(65536)
				decoded = self.data.decode()
				#print(decoded)
				if decoded != "" and decoded != "\n":
					self.console.putData("\n")
					self.console.putData(decoded)

		except Exception as e:
			pass
   
        
def main():
    
	app = QApplication(sys.argv)
	geom = app.desktop().geometry()
    
	w = MainWindow()
    #w.resize(1000, 900)
    
	w.setGeometry(500,300,700,400)
	w.show()
    
	sys.exit(app.exec_())


if __name__ == '__main__':
	main()