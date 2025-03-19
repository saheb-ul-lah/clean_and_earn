cleanandearn/
│
├── includes/                 
│   ├── dashboard_footer.php            
│   ├── dashboard_layout.php            
│   ├── db_connect.php            
│   ├── footer.php        
│   ├── functions.php         
│   └── sidebar.php           
│   └── header.php           
│
├── auth/                     
│   ├── login.php             
│   ├── register.php          
│   ├── logout.php            
│   └── forgot-password.php    
│
├── dashboard/      
|   ├── includes/             
|   |   ├── dashboard_footer.php         
|   |   ├── dashboard_layout.php         
|   |   ├── db_connect.php         
|   |   ├── footer.php      
|   |   ├── functions.php       
|   |   └── sidebar.php         
|   |   └── header.php         
|   |   
|   ├── auth/                
|   │   ├── login.php          
|   │   ├── register.php        
|   │   ├── logout.php         
|   │   └── forgot-password.php   
│   |    
│   ├── index.php             
│   ├── profile/              
│   │   └── index.php
│   ├── settings/             
│   │   └── index.php
│   ├── waste-listings/       
│   │   └── index.php
│   ├── rewards/             
│   │   └── index.php
│   ├── collections/          
│   │   └── index.php
│   ├── earnings/             
│   │   └── index.php
│   ├── storage/              
│   │   └── index.php
│   ├── inventory/            
│   │   └── index.php
│   ├── marketplace/          
│   │   └── index.php
│   ├── orders/               
│   │   └── index.php
│   ├── users/                
│   │   └── index.php
│   ├── waste-types/          
│   │   └── index.php
│   ├── reports/              
│   │   └── index.php
│   └── unauthorized.php      
├── .htaccess                 
├── config.php
├── db.sql                    
├── README.md                 


\\If you deploy to a production server, uncomment the HTTPS redirection rule in the .htaccess file  :


RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

Also, Ensure mod_rewrite is enabled in your Apache configuration.

